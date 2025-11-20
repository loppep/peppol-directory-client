<?php

namespace Loppep\PeppolDirectoryClient\Data;

use SimpleXMLElement;

class SmpInformation
{
    /**
     * @var string
     */
    public string $smpUrl;

    /**
     * @var SimpleXMLElement
     */
    public SimpleXMLElement $configuration;

    /**
     * @param string $smpUrl
     * @param SimpleXMLElement $configuration
     */
    public function __construct(string $smpUrl, SimpleXMLElement $configuration)
    {
        $this->smpUrl = $smpUrl;
        $this->configuration = $configuration;
    }
}