<?php

declare(strict_types=1);

namespace Loppep\PeppolDirectoryClient\Data;

use SimpleXMLElement;

use function array_filter;
use function array_map;
use function is_array;

class EntityType
{
    /**
     * @var array<int, NameType>|NameType
     */
    public array $name;

    /**
     * @var string
     */
    public string $countryCode;

    /**
     * @var string|null
     */
    public ?string $geoInfo;

    /**
     * @var array<int, IdType>|IdType
     */
    public array $identifier;

    /**
     * @var string|null
     */
    public ?string $website;

    /**
     * @var array<int, ContactType>|ContactType
     */
    public array $contact;

    /**
     * @var string
     */
    public string $regDate;

    /**
     * @var string|null
     */
    public ?string $additionalInfo;

    /**
     * @var bool
     */
    public bool $deleted = false;

    /**
     * @param array<int, NameType>|NameType $name
     * @param string $countryCode
     * @param string|null $geoInfo
     * @param array<int, IdType>|IdType $identifier
     * @param string|null $website
     * @param array<int, ContactType>|ContactType $contact
     * @param string|null $additionalInfo
     * @param string $regDate
     * @param bool $deleted
     */
    public function __construct(
        array $name,
        string $countryCode,
        ?string $geoInfo,
        array $identifier,
        ?string $website,
        array $contact,
        ?string $additionalInfo,
        string $regDate,
        bool $deleted = false
    ) {
        $this->name = $name;
        $this->countryCode = $countryCode;
        $this->geoInfo = $geoInfo;
        $this->identifier = $identifier;
        $this->website = $website;
        $this->contact = $contact;
        $this->additionalInfo = $additionalInfo;
        $this->regDate = $regDate;
        $this->deleted = $deleted;
    }

    /**
     * @param SimpleXMLElement $element
     * @return EntityType
     */
    public static function fromXml(SimpleXMLElement $element): EntityType
    {
        return new EntityType(
            array_map(
                static fn(SimpleXMLElement $name): NameType => NameType::fromXml($name),
                array_filter(
                    is_array($element->name)
                        ? $element->name
                        : [$element->name]
                ),
            ),
            (string)$element->countryCode,
            $element->geoInfo ?? null,
            array_map(
                static fn(SimpleXMLElement $identifier): IdType => IdType::fromXml($identifier),
                array_filter(
                    is_array($element->identifier)
                        ? $element->identifier
                        : [$element->identifier]
                ),
            ),
            $element->website ?? null,
            array_map(
                static fn(SimpleXMLElement $contact): ContactType => ContactType::fromXml($contact),
                array_filter(
                    is_array($element->contact)
                        ? $element->contact
                        : [$element->contact]
                ),
            ),
            $element->additionalInfo ?? null,
            (string)$element->regDate,
            ((string)($element->attributes()['deleted'] ?? 'false')) === 'true',
        );
    }
}