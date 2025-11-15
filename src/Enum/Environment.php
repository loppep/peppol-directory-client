<?php

declare(strict_types=1);

namespace Loppep\PeppolDirectoryClient\Enum;

use RuntimeException;

class Environment
{
    public const Production = 'production';

    public const Testing = 'testing';

    /**
     * @var string
     */
    private string $value;

    /**
     * @param string $value
     */
    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return Environment
     */
    public static function production(): Environment
    {
        return new Environment(self::Production);
    }

    /**
     * @return Environment
     */
    public static function testing(): Environment
    {
        return new Environment(self::Testing);
    }

    /**
     * @return string
     */
    public function peppolDirectoryUrl(): string
    {
        switch ($this->value) {
            case self::Production:
                return 'https://directory.peppol.eu';
            case self::Testing:
                return 'https://test-directory.peppol.eu';
            default:
                throw new RuntimeException("Unknown environment: $this->value");
        }
    }

    /**
     * @return string
     */
    public function peppolSmlDnsZoneName(): string
    {
        switch ($this->value) {
            case self::Production:
                return 'edelivery.tech.ec.europa.eu.';
            case self::Testing:
                return 'acc.edelivery.tech.ec.europa.eu.';
            default:
                throw new RuntimeException("Unknown environment: $this->value");
        }
    }
}