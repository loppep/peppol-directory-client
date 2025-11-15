<?php

declare(strict_types=1);

namespace Loppep\PeppolDirectoryClient\Data;

use SimpleXMLElement;

class ContactType
{
    /** @var string|null */
    public ?string $type;

    /**
     * @var string|null
     */
    public ?string $name;

    /**
     * @var string|null
     */
    public ?string $phone;

    /**
     * @var string|null
     */
    public ?string $email;

    /**
     * @param string|null $type
     * @param string|null $name
     * @param string|null $phone
     * @param string|null $email
     */
    public function __construct(
        ?string $type,
        ?string $name,
        ?string $phone,
        ?string $email
    ) {
        $this->type = $type;
        $this->name = $name;
        $this->phone = $phone;
        $this->email = $email;
    }

    /**
     * @param SimpleXMLElement $element
     * @return ContactType
     */
    public static function fromXml(SimpleXMLElement $element): ContactType
    {
        return new ContactType(
            $element->attributes()['type'] ?? null,
            $element->attributes()['name'] ?? null,
            $element->attributes()['phone'] ?? null,
            $element->attributes()['email'] ?? null,
        );
    }
}