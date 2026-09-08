<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Functional\Schema;

use Netzbewegung\NbHeadlessContentBlocks\Schema\JsonSchemaGenerator;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * End-to-end tests for the JSON Schema HTTP endpoint (issue #22, phase 2):
 * a frontend middleware serves the combined schema at a stable URL, gated by
 * application context and an optional per-site token.
 */
final class SchemaEndpointTest extends FunctionalTestCase
{
    private const SCHEMA_PATH = '/api/schema/content-blocks.json';

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/nb_headless_content_blocks/Tests/Fixtures/Extensions/test_nb_headless_content_blocks',
        'typo3conf/ext/container',
        'typo3conf/ext/content_blocks',
        'typo3conf/ext/nb_headless_content_blocks',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->writeSiteConfiguration('schema-public', 'public.example.com', []);
        $this->writeSiteConfiguration('schema-auth', 'auth.example.com', ['token' => 'secret-schema-token']);
        $this->writeSiteConfiguration('schema-disabled', 'disabled.example.com', ['enabled' => false]);
    }

    #[Test]
    public function schemaIsServedPubliclyOutsideProduction(): void
    {
        $response = $this->executeFrontendSubRequest(
            new InternalRequest('https://public.example.com' . self::SCHEMA_PATH)
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json; charset=utf-8', $response->getHeaderLine('Content-Type'));
        self::assertSame('public, max-age=86400', $response->getHeaderLine('Cache-Control'));

        $json = $this->decodeJsonResponse($response);
        self::assertSame('http://json-schema.org/draft-07/schema#', $json['$schema']);
        self::assertSame('Content Block elements', $json['title']);
        self::assertSame(
            'https://public.example.com/content-blocks.schema.json',
            $json['$id']
        );
        self::assertArrayHasKey('linkObject', $json['definitions']);
        self::assertArrayHasKey('fileObject', $json['definitions']);

        $typeNames = $this->get(JsonSchemaGenerator::class)->getContentElementTypeNames();
        self::assertCount(count($typeNames), $json['oneOf']);
        $consts = array_column(array_column(array_column($json['oneOf'], 'properties'), 'type'), 'const');
        self::assertContains('test_simple', $consts);
    }

    #[Test]
    public function configuredTokenIsRequired(): void
    {
        $url = 'https://auth.example.com' . self::SCHEMA_PATH;

        $withoutToken = $this->executeFrontendSubRequest(new InternalRequest($url));
        self::assertSame(404, $withoutToken->getStatusCode());

        $withQueryToken = $this->executeFrontendSubRequest(
            (new InternalRequest($url))->withQueryParameter('token', 'secret-schema-token')
        );
        self::assertSame(200, $withQueryToken->getStatusCode());
        self::assertSame('private, no-store', $withQueryToken->getHeaderLine('Cache-Control'));

        $withHeaderToken = $this->executeFrontendSubRequest(
            (new InternalRequest($url))->withHeader('X-API-Token', 'secret-schema-token')
        );
        self::assertSame(200, $withHeaderToken->getStatusCode());
    }

    #[Test]
    public function disabledEndpointPassesThrough(): void
    {
        $response = $this->executeFrontendSubRequest(
            new InternalRequest('https://disabled.example.com' . self::SCHEMA_PATH)
        );

        self::assertSame(404, $response->getStatusCode());
    }

    #[Test]
    public function nonGetMethodIsRejected(): void
    {
        $response = $this->executeFrontendSubRequest(
            (new InternalRequest('https://public.example.com' . self::SCHEMA_PATH))->withMethod('POST')
        );

        self::assertSame(405, $response->getStatusCode());
        self::assertSame('GET, HEAD', $response->getHeaderLine('Allow'));
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonResponse(ResponseInterface $response): array
    {
        $json = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($json);

        return $json;
    }

    /**
     * @param array<string, mixed> $schemaApiSettings
     */
    private function writeSiteConfiguration(string $identifier, string $host, array $schemaApiSettings): void
    {
        $sitePath = $this->instancePath . '/typo3conf/sites/' . $identifier;
        GeneralUtility::mkdir_deep($sitePath);

        $settingsYaml = '';
        if ($schemaApiSettings !== []) {
            $dumped = Yaml::dump(['nbHeadlessContentBlocks' => ['schemaApi' => $schemaApiSettings]], 4);
            $settingsYaml = 'settings:' . LF . preg_replace('/^/m', '  ', $dumped);
        }

        GeneralUtility::writeFile($sitePath . '/config.yaml', sprintf(
            "rootPageId: 1\n"
            . "base: 'https://%s/'\n"
            . "dependencies:\n"
            . "  - nb-headless-content-blocks/headless-content-blocks\n"
            . '%s'
            . "languages:\n"
            . "  -\n"
            . "    title: English\n"
            . "    enabled: true\n"
            . "    languageId: 0\n"
            . "    base: /\n"
            . "    locale: en_US.UTF-8\n"
            . "    navigationTitle: English\n"
            . "    flag: us\n",
            $host,
            $settingsYaml
        ));
    }
}
