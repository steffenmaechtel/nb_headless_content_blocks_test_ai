<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Middleware;

use Netzbewegung\NbHeadlessContentBlocks\Schema\JsonSchemaGenerator;
use Netzbewegung\NbHeadlessContentBlocks\Schema\SchemaEndpointConfiguration;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;

/**
 * Serves the generated JSON Schemas over HTTP, so consumers (frontend
 * builds, IDEs, contract tests) can fetch them from stable URLs instead
 * of having the files copied around.
 *
 * Opt-in: disabled unless schemaEndpoint.enable is set in the extension
 * configuration. The endpoint runs before site resolution, therefore the
 * configured path is absolute and independent of any site base.
 *
 * - <path>/                              index of the available schemas
 * - <path>/content-blocks.schema.json    combined schema (all blocks)
 * - <path>/<ctype>.schema.json           schema of a single Content Block
 */
final readonly class JsonSchemaEndpoint implements MiddlewareInterface
{
    private const COMBINED_FILE_NAME = 'content-blocks.schema.json';

    private const FILE_SUFFIX = '.schema.json';

    public function __construct(
        private SchemaEndpointConfiguration $configuration,
        private JsonSchemaGenerator $jsonSchemaGenerator,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->configuration->isEnabled()) {
            return $handler->handle($request);
        }

        $path = $this->configuration->getPath();
        $requestPath = rtrim($request->getUri()->getPath(), '/');
        if ($requestPath !== $path && !str_starts_with($requestPath, $path . '/')) {
            return $handler->handle($request);
        }

        if (!in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return $this->response(
                ['error' => 'Method not allowed'],
                405,
                'application/json; charset=utf-8'
            )->withHeader('Allow', 'GET, HEAD');
        }

        $fileName = ltrim(substr($requestPath, strlen($path)), '/');
        $idBase = $this->buildIdBase($request->getUri(), $path);

        if ($fileName === '') {
            return $this->conditionalResponse(
                $request,
                $this->buildIndex($idBase),
                'application/json; charset=utf-8'
            );
        }

        $schema = $this->buildSchema($fileName, $idBase);
        if ($schema === null) {
            return $this->response(
                ['error' => 'No JSON Schema found for "' . $fileName . '"'],
                404,
                'application/json; charset=utf-8'
            );
        }

        return $this->conditionalResponse($request, $schema, 'application/schema+json; charset=utf-8');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildSchema(string $fileName, string $idBase): ?array
    {
        if (!str_ends_with($fileName, self::FILE_SUFFIX)) {
            return null;
        }

        if ($fileName === self::COMBINED_FILE_NAME) {
            return $this->jsonSchemaGenerator->generateCombined($idBase);
        }

        $typeName = substr($fileName, 0, -strlen(self::FILE_SUFFIX));

        return $this->jsonSchemaGenerator->generateForTypeName($typeName, $idBase);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildIndex(string $idBase): array
    {
        $schemas = ['content-blocks' => $idBase . '/' . self::COMBINED_FILE_NAME];
        foreach ($this->jsonSchemaGenerator->getContentElementTypeNames() as $typeName) {
            $schemas[$typeName] = $idBase . '/' . $typeName . self::FILE_SUFFIX;
        }

        return ['schemas' => $schemas];
    }

    private function buildIdBase(UriInterface $uri, string $path): string
    {
        return rtrim((string)$uri->withPath($path)->withQuery('')->withFragment(''), '/');
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function conditionalResponse(
        ServerRequestInterface $request,
        array $payload,
        string $contentType
    ): ResponseInterface {
        $body = $this->encode($payload);
        $eTag = '"' . md5($body) . '"';

        if (trim($request->getHeaderLine('If-None-Match')) === $eTag) {
            return (new Response('php://temp', 304))->withHeader('ETag', $eTag);
        }

        return $this->body($body, 200, $contentType)->withHeader('ETag', $eTag);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function response(array $payload, int $status, string $contentType): ResponseInterface
    {
        return $this->body($this->encode($payload), $status, $contentType);
    }

    private function body(string $body, int $status, string $contentType): ResponseInterface
    {
        $response = new Response('php://temp', $status, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'public, max-age=3600',
        ]);
        $response->getBody()->write($body);
        $response->getBody()->rewind();

        return $response;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function encode(array $payload): string
    {
        return json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        ) . LF;
    }
}
