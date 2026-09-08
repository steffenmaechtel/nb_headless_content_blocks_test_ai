# Publish JSON Schema

This guide shows how to make the generated JSON Schema reachable for
consumers (frontend repository, editors, CI) — either via the live HTTP
endpoint or as static files with stable URLs.

## Option 1: live HTTP endpoint

The extension ships a frontend middleware that serves the combined schema
(`content-blocks.schema.json`) at a stable URL:

```
GET https://cms.example.org/api/schema/content-blocks.json
```

It is enabled by default for every site that includes the
`nb-headless-content-blocks/headless-content-blocks` Site Set. The response
contains the same document the CLI writes to
`content-blocks.schema.json` — a `oneOf` over all registered Content
Blocks, discriminated by the `type` wrapper field.

### Access control

| Context | Token configured | Result |
|---|---|---|
| non-production | no | public (200) |
| non-production | yes | token required (200 / 404) |
| production | no | disabled (404) |
| production | yes | token required (200 / 404) |

A configured token is therefore always required — it also protects
non-production environments that set one. Send it as `X-API-Token` header
or `?token=` query parameter. A missing or wrong token returns `404`, so
the endpoint is indistinguishable from an unregistered route.

### Per-site configuration

All options are [site settings](https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/13.4/Feature-101476-SiteSettingsAPI.html),
provided as defaults by the Site Set and overridable per site in
`config/sites/<identifier>/config.yaml`:

```yaml
settings:
  nbHeadlessContentBlocks:
    schemaApi:
      enabled: true
      path: /api/schema/content-blocks.json
      idBase: ''        # default: the site's base URL
      token: ''         # empty = public outside production, disabled in production
```

- `enabled: false` disables the endpoint for that site.
- `path` changes the URL the middleware answers on.
- `idBase` overrides the `$id` base URL (default: the site's base URL, so
  `$id` becomes e.g. `https://cms.example.org/content-blocks.schema.json`).
- `token` requires authentication (see above).

### Caching

- Public responses are served with `Cache-Control: public, max-age=86400`.
- Token-protected responses are served with `Cache-Control: private, no-store`.

Only `GET` and `HEAD` are allowed; other methods get `405`.

## Option 2: static files

The [generate-schema command](generate-json-schema.md) writes the schema
files to a directory. Publish them like any other static asset:

- **Commit them to the frontend repository** and reference the committed
  files in fixtures and mocks.
- **Copy them in CI** (after each Content Block change) to a static file
  server or CDN, e.g. under `https://cdn.example.org/api/schema/`. Static
  hosting gives you long-lived `Cache-Control` headers and no origin
  restrictions (no CORS needed).
- Use the `--id-base` option so the `$id` URLs match the published
  location:

  ```bash
  bin/typo3 nbheadlesscontentblocks:generate-schema \
      --target public/api/schema \
      --id-base https://cdn.example.org/api/schema
  ```

## URL strategy and versioning

The `$id` URLs become public API once consumers reference them. Keep them
stable:

- Do not change the default path or `$id` base without a migration plan.
- Treat schema-incompatible contract changes as a versioned release (the
  `$id` can carry a version segment, e.g. `.../v1/content-blocks.schema.json`).
- The endpoint always reflects the current installation; static files let
  you pin a version explicitly.

## Browser-based tooling and CORS

Editors and IDE extensions fetch schemas without a browser origin, so CORS
is usually not an issue. If browser-based tooling must fetch the endpoint
directly, add `Access-Control-Allow-Origin` headers on a reverse proxy in
front of the CMS (the middleware intentionally does not add them).

Background, benefits and the phased plan:
[JSON Schema generation](../design/json_schema_generation.md) (design
record).