<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Schema;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

/**
 * Extension configuration of the JSON Schema HTTP endpoint
 * (see ext_conf_template.txt). The endpoint is opt-in.
 */
final readonly class SchemaEndpointConfiguration
{
    public const EXTENSION_KEY = 'nb_headless_content_blocks';

    private const DEFAULT_PATH = '/api/schema';

    public function __construct(
        private ExtensionConfiguration $extensionConfiguration,
    ) {}

    public function isEnabled(): bool
    {
        $enable = $this->getConfiguration()['schemaEndpoint']['enable'] ?? false;

        return $enable === true || $enable === 1 || $enable === '1';
    }

    /**
     * Absolute path prefix without a trailing slash, e.g. "/api/schema".
     */
    public function getPath(): string
    {
        $path = '/' . trim(trim((string)($this->getConfiguration()['schemaEndpoint']['path'] ?? '')), '/');

        return $path === '/' ? self::DEFAULT_PATH : $path;
    }

    /**
     * @return array<string, mixed>
     */
    private function getConfiguration(): array
    {
        try {
            $configuration = $this->extensionConfiguration->get(self::EXTENSION_KEY);
        } catch (ExtensionConfigurationExtensionNotConfiguredException|ExtensionConfigurationPathDoesNotExistException) {
            return [];
        }

        return is_array($configuration) ? $configuration : [];
    }
}
