<?php

declare(strict_types=1);

namespace Loppep\PeppolDirectoryClient\Data;

use JsonSerializable;
use SimpleXMLElement;

class IdType implements JsonSerializable
{
    /**
     * @var string
     */
    public string $scheme;

    /**
     * @var string
     */
    public string $value;

    /**
     * @param string $scheme
     * @param string $value
     */
    public function __construct(
        string $scheme,
        string $value
    ) {
        $this->scheme = $scheme;
        $this->value = $value;
    }

    /**
     * @param SimpleXMLElement $element
     * @return IdType
     */
    public static function fromXml(SimpleXMLElement $element): IdType
    {
        return new IdType(
            (string)$element->attributes()['scheme'],
            (string)$element,
        );
    }

    /**
     * @param array $array
     * @return IdType
     */
    public static function fromArray(array $array): IdType
    {
        return new IdType(
            $array['scheme'],
            $array['value'],
        );
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'scheme' => $this->scheme,
            'value' => $this->value,
        ];
    }
}