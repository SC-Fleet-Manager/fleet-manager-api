<?php

namespace App\Service\WebExtension;

use App\Service\WebExtension\Dto\WebExtensionComparison;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class WebExtensionVersionComparator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const VERSION_REGEX = '~^v?(\d+)\.(\d+)\.(\d+)~';

    private string $webExtensionVersionUrl;

    public function __construct(string $webExtensionVersionUrl)
    {
        $this->webExtensionVersionUrl = $webExtensionVersionUrl;
    }

    public function compareVersions(?string $requestExtensionVersion): ?WebExtensionComparison
    {
        if ($requestExtensionVersion !== null && !preg_match(self::VERSION_REGEX, $requestExtensionVersion)) {
            return null;
        }

        $webExtensionVersions = file_get_contents($this->webExtensionVersionUrl);
        if ($webExtensionVersions === false) {
            $this->logger->error('[WebExt Export] Cannot get web-extensions versions.', ['url' => $this->webExtensionVersionUrl]);

            return null;
        }

        $webExtensionVersions = json_decode($webExtensionVersions, true);
        if ($webExtensionVersions === false) {
            $this->logger->error('[WebExt Export] Cannot json decode web-extensions versions.', ['json_error' => json_last_error(), 'json_msg' => json_last_error_msg()]);

            return null;
        }

        $lastVersion = end($webExtensionVersions['addons']['info@fleet.fallkrom.space']['updates'])['version'] ?? null;
        if ($lastVersion === null) {
            return null;
        }

        $needUpgradeVersion = true;
        if ($requestExtensionVersion !== null) {
            preg_match(self::VERSION_REGEX, $lastVersion, $matches);
            $lastVersionNumeric = count($matches) >= 4 ? $this->transformVersionToNumeric($matches[1], $matches[2], $matches[3]) : 0;
            preg_match(self::VERSION_REGEX, $requestExtensionVersion, $matches);
            $extensionVersionNumeric = count($matches) >= 4 ? $this->transformVersionToNumeric($matches[1], $matches[2], $matches[3]) : 0;

            $needUpgradeVersion = $extensionVersionNumeric < $lastVersionNumeric;
        }

        return new WebExtensionComparison(
            $lastVersion,
            $requestExtensionVersion,
            $needUpgradeVersion,
        );
    }

    private function transformVersionToNumeric(int $major, int $minor, int $patch): int
    {
        return $major * 10000 + $minor * 100 + $patch;
    }
}
