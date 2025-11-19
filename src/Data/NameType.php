<?php

declare(strict_types=1);

namespace Loppep\PeppolDirectoryClient\Data;

use JsonSerializable;
use SimpleXMLElement;

class NameType implements JsonSerializable
{
    /**
     * @var string
     */
    public string $name;

    /**
     * @var string|null
     */
    public ?string $language;

    /**
     * @param string $name
     * @param string|null $language
     */
    public function __construct(
        string $name,
        ?string $language
    ) {
        $this->name = $name;
        $this->language = $language;
    }

    /**
     * @param SimpleXMLElement $element
     * @return NameType
     */
    public static function fromXml(SimpleXMLElement $element): NameType
    {
        return new NameType(
            (string)$element,
            isset($element->attributes()['lang']) ? (string)$element->attributes()['lang'] : null,
        );
    }

    /**
     * @param array $array
     * @return NameType
     */
    public static function fromArray(array $array): NameType
    {
        return new NameType(
            $array['name'],
            $array['language'] ?? null,
        );
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'language' => $this->language,
        ];
    }
}