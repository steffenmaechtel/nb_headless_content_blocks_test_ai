# Publish JSON Schema

This guide shows how to deliver the generated JSON Schemas to their
consumers — the frontend build, IDEs and contract tests — either as
static files or through the extension's opt-in HTTP endpoint.

How to generate the files in the first place:
[Generate JSON Schema](generate-json-schema.md).

## Option 1: static files (recommended for production)

Run the generator in CI (or locally) and copy the files wherever they
are served from:

```bash
bin/typo3 nbheadlesscontentblocks:generate-schema \
    --target public/api/schema \
    --id-base https://cms.example.org/api/schema
```

- **Commit them** into the site package when the schemas should be
  reviewed like code — a diff in the merge request shows the contract
  change explicitly.
- **Copy them in CI** when the frontend repository or a static file
  server should receive them automatically on deploy.

Keep the `$id` URLs (`--id-base`) **stable** — they become the public
identifiers tools reference. A pragmatic URL strategy:

- `https://<cms-host>/api/schema/<ctype>.schema.json` for one block
- `https://<cms-host>/api/schema/content-blocks.schema.json` for the
  combined schema
- When the contract changes in a breaking way, generate into a versioned
  path (`/api/schema/v2/`) and keep the old files available.

This extension's own test suite freezes the generated combined schema
byte-exactly
(`Tests/Functional/Schema/CommittedSchemaArtifactTest.php`) — the same
pattern works for your site package: commit the artifact and let CI
regenerate and diff it.

## Option 2: HTTP endpoint

The extension ships a PSR-15 middleware that serves the schemas over
HTTP, so no file copy step is needed. It is **disabled by default** and
must be enabled in the extension configuration
(Settings → Extensions → nb_headless_content_blocks, or
`EXTENSIONS` in `LocalConfiguration.php`):

```php
'EXTENSIONS' => [
    'nb_headless_content_blocks' => [
        'schemaEndpoint' => [
            'enable' => '1',
            'path' => '/api/schema',
        ],
    ],
],
```

| Setting | Default | Meaning |
|---|---|---|
| `schemaEndpoint.enable` | `0` | serve the schemas over HTTP |
| `schemaEndpoint.path` | `/api/schema` | absolute path prefix |

The endpoint runs before site resolution, so the path is independent of
any site base and works for every site/language of the installation.

It serves:

| URL | Content |
|---|---|
| `<path>/` | JSON index of all available schema URLs |
| `<path>/content-blocks.schema.json` | combined schema (all blocks) |
| `<path>/<ctype>.schema.json` | schema of one Content Block |

Behavior details:

- `Content-Type: application/schema+json` for schemas,
  `application/json` for the index
- the `$id` of every served schema is derived from the request URL, so
  the URLs are stable and self-describing
- `ETag` + `304 Not Modified` on `If-None-Match`, `Cache-Control:
  public, max-age=3600`
- `GET`/`HEAD` only — anything else gets `405`
- unknown schema names get a `404` JSON error

**Security note:** the endpoint publishes your whole content model
(all Content Blocks, field identifiers, field types). That is usually
fine for staging/internal environments — the reason it is off by
default. On production, prefer option 1 (static files) or put a
gate (basic auth, network restriction) in front of the path.

Background and the phased delivery plan:
[JSON Schema generation](../design/json_schema_generation.md) (design
record).
