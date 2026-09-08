<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\Schema;

use Netzbewegung\NbHeadlessContentBlocks\Schema\SchemaEndpointConfiguration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class SchemaEndpointConfigurationTest extends UnitTestCase
{
    #[Test]
    public function endpointIsDisabledByDefault(): void
    {
        self::assertFalse($this->subject([])->isEnabled());
    }

    #[Test]
    public function endpointIsDisabledWhenTheExtensionIsNotConfigured(): void
    {
        $extensionConfiguration = $this->createMock(ExtensionConfiguration::class);
        $extensionConfiguration->method('get')
            ->willThrowException(new ExtensionConfigurationExtensionNotConfiguredException());

        $subject = new SchemaEndpointConfiguration($extensionConfiguration);

        self::assertFalse($subject->isEnabled());
        self::assertSame('/api/schema', $subject->getPath());
    }

    /**
     * @return \Generator<string, array{mixed, bool}>
     */
    public static function enableValueProvider(): \Generator
    {
        yield 'string one' => ['1', true];
        yield 'integer one' => [1, true];
        yield 'boolean true' => [true, true];
        yield 'string zero' => ['0', false];
        yield 'empty string' => ['', false];
        yield 'boolean false' => [false, false];
    }

    #[DataProvider('enableValueProvider')]
    #[Test]
    public function enableFlagIsInterpreted(mixed $value, bool $expected): void
    {
        $subject = $this->subject(['schemaEndpoint' => ['enable' => $value]]);

        self::assertSame($expected, $subject->isEnabled());
    }

    /**
     * @return \Generator<string, array{mixed, string}>
     */
    public static function pathProvider(): \Generator
    {
        yield 'default when unset' => [null, '/api/schema'];
        yield 'default when empty' => ['', '/api/schema'];
        yield 'default when slash only' => ['/', '/api/schema'];
        yield 'leading slash added' => ['api/schema', '/api/schema'];
        yield 'trailing slash removed' => ['/api/schema/', '/api/schema'];
        yield 'custom path' => ['/schema', '/schema'];
        yield 'whitespace trimmed' => ['  /json/schema  ', '/json/schema'];
    }

    #[DataProvider('pathProvider')]
    #[Test]
    public function pathIsNormalized(?string $value, string $expected): void
    {
        $subject = $this->subject(['schemaEndpoint' => ['path' => $value]]);

        self::assertSame($expected, $subject->getPath());
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function subject(array $configuration): SchemaEndpointConfiguration
    {
        $extensionConfiguration = $this->createMock(ExtensionConfiguration::class);
        $extensionConfiguration->method('get')->willReturn($configuration);

        return new SchemaEndpointConfiguration($extensionConfiguration);
    }
}
