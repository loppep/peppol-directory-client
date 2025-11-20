<?php

declare(strict_types=1);

namespace Loppep\PeppolDirectoryClient;

use Http\Discovery\Psr18Client;
use Loppep\PeppolDirectoryClient\Data\IdType;
use Loppep\PeppolDirectoryClient\Data\MatchType;
use Loppep\PeppolDirectoryClient\Data\SmpInformation;
use Loppep\PeppolDirectoryClient\Enum\Environment;
use Loppep\PeppolDirectoryClient\Util\Xml;
use ParagonIE\ConstantTime\Encoding;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\http\Client\ClientInterface;
use Psr\http\Message\ResponseInterface;
use RuntimeException;
use SimpleXMLElement;
use Throwable;

use function array_filter;
use function array_map;
use function basename;
use function explode;
use function urldecode;

class PeppolDirectoryClient
{
    /**
     * @var ClientInterface|Psr18Client
     */
    private ClientInterface $http;

    /**
     * @var Environment
     */
    private Environment $environment;

    /**
     * @param ClientInterface|null $http
     * @param Environment|null $environment
     */
    public function __construct(
        ?ClientInterface $http = null,
        ?Environment $environment = null
    ) {
        $this->http = $http ?? new Psr18Client();
        $this->environment = $environment ?? Environment::production();
    }

    /**
     * @return array<int, MatchType>|MatchType[]
     * @throws Throwable
     */
    public function search(string $query): array
    {
        $xml = Xml::fromResponse(
            $this->request("{$this->environment->peppolDirectoryUrl()}/search/1.0/xml?q={$query}")
        );

        $matches = array_map(
            static fn(SimpleXMLElement $matchType): MatchType => MatchType::fromXml($matchType),
            array_filter(
                is_array($xml->match)
                    ? $xml->match
                    : [$xml->match]
            )
        );

        foreach ($matches as $index => $match) {
            $smpInformation = $this->getSmpInformationForParticipantId($match->participantId);

            if ($smpInformation === null) {
                $matches[$index] = MatchType::unregistered($match->participantId);

                continue;
            }

            $matches[$index] = $match
                ->withDocTypeId(
                    $this->getSupportedDocTypeIdsForParticipantId(
                        $smpInformation,
                        $match->participantId
                    )
                )
                ->withSmpUrl($smpInformation->smpUrl);
        }

        return $matches;
    }

    /**
     * @param IdType $participantId
     * @return MatchType
     * @throws Throwable
     */
    public function get(IdType $participantId): MatchType
    {
        $smpInformation = $this->getSmpInformationForParticipantId($participantId);

        if ($smpInformation === null) {
            return MatchType::unregistered($participantId);
        }

        return new MatchType(
            $participantId,
            $this->getSupportedDocTypeIdsForParticipantId(
                $smpInformation,
                $participantId
            ),
            [],
            true,
            $smpInformation->smpUrl,
        );
    }

    /**
     * @param IdType $participantId
     * @return bool
     */
    public function isRegistered(IdType $participantId): bool
    {
        $smpInformation = $this->getSmpInformationForParticipantId($participantId);

        return $smpInformation !== null;
    }

    /**
     * @param IdType $participantId
     * @return SmpInformation|null
     */
    private function getSmpInformationForParticipantId(IdType $participantId): ?SmpInformation
    {
        $smpUrl = $this->getSmpUrlForParticipantId($participantId);

        if ($smpUrl === null) {
            return null;
        }

        try {
            return new SmpInformation(
                $smpUrl,
                Xml::fromResponse(
                    $this->request($smpUrl . '/' . $participantId->scheme . '::' . $participantId->value)
                )
            );
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * @param IdType $participantId
     * @return string|null
     */
    private function getSmpUrlForParticipantId(IdType $participantId): ?string
    {
        $hostname = sprintf(
            '%s.%s.%s',
            rtrim(
                Encoding::base32Encode(
                    hash(
                        'sha256',
                        mb_strtolower(
                            $participantId->value
                        ),
                        true,
                    )
                ),
                '=',
            ),
            $participantId->scheme,
            $this->environment->peppolSmlDnsZoneName(),
        );

        $dnsRecords = dns_get_record(
            $hostname,
            DNS_NAPTR,
        );
        $dnsRecords = array_filter(
            is_array($dnsRecords) ? $dnsRecords : [],
            static function (array $dnsRecord) {
                return ($dnsRecord['flags'] ?? '') === 'U'
                    && ($dnsRecord['services'] ?? '') === 'Meta:SMP';
            }
        );

        if (count($dnsRecords) === 0) {
            return null;
        }

        $regex = $dnsRecords[0]['regex'] ?? null;

        if ($regex === null) {
            return null;
        }

        $firstCharacter = $regex[0];
        $lastCharacter = $regex[mb_strlen($regex) - 1];

        if ($firstCharacter !== $lastCharacter) {
            return null;
        }

        $delimiter = $firstCharacter;
        [$pattern, $replacement] = explode(
            $delimiter,
            trim($regex, $delimiter)
        );

        return rtrim(
            preg_replace(
                '/^(' . $pattern . ')$/',
                $replacement,
                $hostname,
            ),
            '/',
        );
    }

    /**
     * @param SmpInformation $smpInformation
     * @param IdType $participantId
     * @return array
     */
    private function getSupportedDocTypeIdsForParticipantId(SmpInformation $smpInformation, IdType $participantId): array
    {
        $referenceUrls = array_map(
            static fn(SimpleXMLElement $element): ?string => isset($element->attributes()['href'])
                ? (string)$element->attributes()['href']
                : null,
            array_filter($smpInformation->configuration->xpath('//ServiceMetadataReference'))
        );

        $docTypeIds = [];
        foreach ($referenceUrls as $referenceUrl) {
            [$scheme, $value] = explode(
                '::',
                basename(urldecode($referenceUrl)),
                2
            );
            $docTypeIds[] = new IdType($scheme, $value);
        }

        return $docTypeIds;
    }

    /**
     * @param string $url
     * @param string $method
     * @param int $maxAttempts
     * @param int $backOffMs
     * @return ResponseInterface
     * @throws ClientExceptionInterface
     * @throws Throwable
     */
    private function request(
        string $url,
        string $method = 'GET',
        int $maxAttempts = 5,
        int $backOffMs = 500
    ): ResponseInterface {
        try {
            $response = $this->http->sendRequest(
                $this->http->createRequest(
                    'GET',
                    $url,
                )
            );

            if ($response->getStatusCode() !== 200) {
                throw new RuntimeException(
                    "Failed to fetch URL: {$url}, status code: {$response->getStatusCode()}"
                );
            }
        } catch (Throwable $e) {
            if ($maxAttempts > 0) {
                usleep($backOffMs * 1000);

                return $this->request($url, $method, $maxAttempts - 1, $backOffMs);
            }

            throw $e;
        }

        return $response;
    }
}