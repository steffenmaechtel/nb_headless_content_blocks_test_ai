<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Middleware;

use Netzbewegung\NbHeadlessContentBlocks\Schema\JsonSchemaGenerator;
use Netzbewegung\NbHeadlessContentBlocks\Schema\SchemaApiAccess;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;

/**
 * Serves the combined Content Block JSON Schema at a stable URL (issue #22,
 * phase 2). The endpoint is public outside the production context and
 * requires a configured token in production — or whenever a token is set.
 *
 * Behavior is configured per site via site settings:
 *
 * - nbHeadlessContentBlocks.schemaApi.enabled (default true)
 * - nbHeadlessContentBlocks.schemaApi.path (default /api/schema/content-blocks.json)
 * - nbHeadlessContentBlocks.schemaApi.idBase (default: the site URL)
 * - nbHeadlessContentBlocks.schemaApi.token (default: none)
 */
final readonly class SchemaEndpointMiddleware implements MiddlewareInterface
{
    private const SETTING_PREFIX = 'nbHeadlessContentBlocks.schemaApi.';

    private const DEFAULT_PATH = '/api/schema/content-blocks.json';

    public function __construct(
        private readonly JsonSchemaGenerator $jsonSchemaGenerator,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $settings = $this->getSettings($request);
        if (!$this->setting($settings, 'enabled', true)) {
            return $handler->handle($request);
        }

        $path = (string)$this->setting($settings, 'path', self::DEFAULT_PATH);
        if ($path === '' || rtrim($request->getUri()->getPath(), '/') !== rtrim($path, '/')) {
            return $handler->handle($request);
        }

        if (!in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return (new Response())->withStatus(405)->withHeader('Allow', 'GET, HEAD');
        }

        $configuredToken = (string)$this->setting($settings, 'token', '');
        if (!SchemaApiAccess::isAllowed(
            Environment::getContext()->isProduction(),
            $configuredToken,
            $this->providedToken($request)
        )) {
            return (new Response())->withStatus(404);
        }

        $idBase = (string)$this->setting($settings, 'idBase', '');
        if ($idBase === '') {
            $normalizedParams = $request->getAttribute('normalizedParams');
            if ($normalizedParams instanceof NormalizedParams) {
                $idBase = rtrim($normalizedParams->getSiteUrl(), '/');
            }
        }

        $cacheControl = $configuredToken === '' ? 'public, max-age=86400' : 'private, no-store';

        return (new JsonResponse($this->jsonSchemaGenerator->generateCombined($idBase)))
            ->withHeader('Cache-Control', $cacheControl);
    }

    private function getSettings(ServerRequestInterface $request): ?SiteSettings
    {
        $site = $request->getAttribute('site');
        if (!$site instanceof Site) {
            return null;
        }
        return $site->getSettings();
    }

    private function setting(?SiteSettings $settings, string $key, mixed $default): mixed
    {
        if ($settings === null) {
            return $default;
        }
        return $settings->get(self::SETTING_PREFIX . $key, $default);
    }

    private function providedToken(ServerRequestInterface $request): string
    {
        $header = $request->getHeaderLine('X-API-Token');
        if ($header !== '') {
            return $header;
        }
        $query = $request->getQueryParams()['token'] ?? '';
        return is_string($query) ? $query : '';
    }
}
