<?php

declare(strict_types=1);

namespace Loppep\PeppolDirectoryClient\Util;

use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;

use function simplexml_load_string;

class Xml
{
    /**
     * @param ResponseInterface $response
     * @return SimpleXMLElement
     */
    public static function fromResponse(ResponseInterface $response): SimpleXMLElement
    {
        return simplexml_load_string(
            self::removeNamespaces(
                $response->getBody()->getContents()
            )
        );
    }

    /**
     * @param string $xml
     * @return string
     */
    private static function removeNamespaces(string $xml): string
    {
        return preg_replace(
            [
                // namespace declarations
                '/xmlns[^=]*="[^"]*"/i',
                // namespace references
                '/(<\/?)\w+:/',
            ],
            [
                '',
                '$1'
            ],
            $xml
        );
    }
}