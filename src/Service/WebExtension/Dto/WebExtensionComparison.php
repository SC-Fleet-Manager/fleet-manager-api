<?php

namespace App\Service\WebExtension\Dto;

class WebExtensionComparison
{
    public string $lastVersion;
    public ?string $requestExtensionVersion;
    public bool $needUpgradeVersion;

    public function __construct(string $lastVersion, ?string $requestExtensionVersion, bool $needUpgradeVersion)
    {
        $this->lastVersion = $lastVersion;
        $this->requestExtensionVersion = $requestExtensionVersion;
        $this->needUpgradeVersion = $needUpgradeVersion;
    }
}
