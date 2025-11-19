<?php

declare(strict_types=1);

namespace Loppep\PeppolDirectoryClient\Data;

use JsonSerializable;
use SimpleXMLElement;

use function array_filter;
use function array_map;
use function is_array;

class MatchType implements JsonSerializable
{
    /**
     * @var IdType
     */
    public IdType $participantId;

    /**
     * @var array<int, IdType>|IdType[]
     */
    public array $docTypeId;

    /**
     * @var array<int, EntityType>|EntityType[]
     */
    public array $entity;

    /**
     * @var bool
     */
    public bool $registered = true;

    /**
     * @param IdType $participantId
     * @param array<int, IdType>|IdType[] $docTypeId
     * @param array<int, EntityType>|EntityType[] $entity
     * @param bool $registered
     */
    public function __construct(
        IdType $participantId,
        array $docTypeId,
        array $entity = [],
        bool $registered = true
    ) {
        $this->participantId = $participantId;
        $this->docTypeId = $docTypeId;
        $this->entity = $entity;
        $this->registered = $registered;
    }

    /**
     * @param IdType $participantId
     * @return MatchType
     */
    public static function unregistered(IdType $participantId): MatchType
    {
        return new MatchType(
            $participantId,
            [],
            [],
            false,
        );
    }

    /**
     * @param SimpleXMLElement $element
     * @return MatchType
     */
    public static function fromXml(SimpleXMLElement $element): MatchType
    {
        return new MatchType(
            IdType::fromXml($element->participantID),
            array_map(
                static fn(SimpleXMLElement $docTypeId): IdType => IdType::fromXml($docTypeId),
                array_filter(
                    is_array($element->docTypeID)
                        ? $element->docTypeID
                        : [$element->docTypeID]
                ),
            ),
            array_map(
                static fn(SimpleXMLElement $entity): EntityType => EntityType::fromXml($entity),
                array_filter(
                    is_array($element->entity)
                        ? $element->entity
                        : [$element->entity]
                )
            ),
        );
    }

    /**
     * @param array $docTypeId
     * @return MatchType
     */
    public function withDocTypeId(array $docTypeId): MatchType
    {
        return new MatchType(
            $this->participantId,
            $docTypeId,
            $this->entity,
            $this->registered,
        );
    }

    /**
     * @param array $array
     * @return MatchType
     */
    public static function fromArray(array $array): MatchType
    {
        return new MatchType(
            IdType::fromArray($array['participantId']),
            array_map(
                static fn(array $docTypeId): IdType => IdType::fromArray($docTypeId),
                $array['docTypeId'],
            ),
            array_map(
                static fn(array $entity): EntityType => EntityType::fromArray($entity),
                $array['entity'],
            ),
            $array['registered'],
        );
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'participantId' => $this->participantId,
            'docTypeId' => $this->docTypeId,
            'entity' => $this->entity,
            'registered' => $this->registered,
        ];
    }
}