<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Functional\Frontend;

use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * The opt-in JSON Schema HTTP endpoint (phase 2 of the schema delivery,
 * see docs/design/json_schema_generation.md).
 */
final class JsonSchemaEndpointTest extends FunctionalTestCase
{
    private const SITE_IDENTIFIER = 'schema-endpoint-test';

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/nb_headless_content_blocks/Tests/Fixtures/Extensions/test_nb_headless_content_blocks',
        'typo3conf/ext/container',
        'typo3conf/ext/content_blocks',
        'typo3conf/ext/nb_headless_content_blocks',
    ];

    protected array $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'nb_headless_content_blocks' => [
                'schemaEndpoint' => [
                    'enable' => '1',
                    'path' => '/api/schema',
                ],
            ],
        ],
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DataSet/e2e_page.csv');
        $this->writeSiteConfiguration();
    }

    #[Test]
    public function combinedSchemaIsServed(): void
    {
        $response = $this->executeFrontendSubRequest(
            $this->request('/api/schema/content-blocks.schema.json')
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/schema+json; charset=utf-8', $response->getHeaderLine('Content-Type'));
        self::assertNotSame('', $response->getHeaderLine('ETag'));

        $schema = $this->decode($response);

        self::assertSame('http://json-schema.org/draft-07/schema#', $schema['$schema']);
        self::assertSame(
            'https://example.com/api/schema/content-blocks.schema.json',
            $schema['$id']
        );

        $typeConstants = array_map(
            static fn(array $branch): string => $branch['properties']['type']['const'],
            $schema['oneOf']
        );
        self::assertContains('test_simple', $typeConstants);
    }

    #[Test]
    public function singleContentBlockSchemaIsServed(): void
    {
        $response = $this->executeFrontendSubRequest(
            $this->request('/api/schema/test_simple.schema.json')
        );

        self::assertSame(200, $response->getStatusCode());

        $schema = $this->decode($response);

        self::assertSame('https://example.com/api/schema/test_simple.schema.json', $schema['$id']);
        self::assertArrayHasKey('my_text', $schema['properties']);
    }

    #[Test]
    public function indexListsTheAvailableSchemas(): void
    {
        $response = $this->executeFrontendSubRequest($this->request('/api/schema/'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json; charset=utf-8', $response->getHeaderLine('Content-Type'));

        $index = $this->decode($response);

        self::assertSame(
            'https://example.com/api/schema/content-blocks.schema.json',
            $index['schemas']['content-blocks']
        );
        self::assertSame(
            'https://example.com/api/schema/test_simple.schema.json',
            $index['schemas']['test_simple']
        );
    }

    #[Test]
    public function unknownSchemaNameIsNotFound(): void
    {
        $response = $this->executeFrontendSubRequest(
            $this->request('/api/schema/does_not_exist.schema.json')
        );

        self::assertSame(404, $response->getStatusCode());
        self::assertArrayHasKey('error', $this->decode($response));
    }

    #[Test]
    public function matchingEtagIsAnsweredWithNotModified(): void
    {
        $path = '/api/schema/content-blocks.schema.json';
        $eTag = $this->executeFrontendSubRequest($this->request($path))->getHeaderLine('ETag');

        $response = $this->executeFrontendSubRequest(
            $this->request($path)->withHeader('If-None-Match', $eTag)
        );

        self::assertSame(304, $response->getStatusCode());
        self::assertSame('', (string)$response->getBody());
    }

    #[Test]
    public function otherRequestMethodsAreRejected(): void
    {
        $response = $this->executeFrontendSubRequest(
            $this->request('/api/schema/content-blocks.schema.json')->withMethod('POST')
        );

        self::assertSame(405, $response->getStatusCode());
        self::assertSame('GET, HEAD', $response->getHeaderLine('Allow'));
    }

    #[Test]
    public function requestsOutsideTheEndpointPathArePassedThrough(): void
    {
        $response = $this->executeFrontendSubRequest(
            $this->request('/api/other/content-blocks.schema.json')
        );

        self::assertSame(404, $response->getStatusCode());
        self::assertStringNotContainsString('json-schema.org', (string)$response->getBody());
    }

    private function request(string $path): InternalRequest
    {
        return (new InternalRequest('https://example.com' . $path))
            ->withServerParams([
                'SCRIPT_NAME' => '/index.php',
                'HTTP_HOST' => 'example.com',
                'SERVER_NAME' => 'example.com',
                'HTTPS' => 'on',
                'REMOTE_ADDR' => '127.0.0.1',
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(ResponseInterface $response): array
    {
        $payload = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($payload);

        return $payload;
    }

    private function writeSiteConfiguration(): void
    {
        $sitePath = $this->instancePath . '/typo3conf/sites/' . self::SITE_IDENTIFIER;
        GeneralUtility::mkdir_deep($sitePath);
        GeneralUtility::writeFile($sitePath . '/config.yaml', <<<'YAML'
rootPageId: 1
base: 'https://example.com/'
languages:
  -
    title: English
    enabled: true
    languageId: 0
    base: /
    locale: en_US.UTF-8
    navigationTitle: English
    flag: us
YAML);
    }
}
