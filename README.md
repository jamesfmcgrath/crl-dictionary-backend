# Dictionary API - Drupal Backend

Drupal 10 backend that imports dictionary definitions from an external API and exposes them via JSON:API for the Next.js frontend.

## Prerequisites

- [DDEV](https://ddev.readthedocs.io/) installed

**Important:** This project is configured with DDEV project name `crl-dictionary-backend` (see `.ddev/config.yaml`). All example URLs use `http://crl-dictionary-backend.ddev.site`. If you change the project name, update URLs accordingly.

## Setup

From the project root directory:

```bash
# Start DDEV
ddev start

# Install dependencies
ddev composer install

# Install Drupal (standard profile)
ddev drush site:install standard -y

# Enable custom dictionary module (creates content type + fields)
ddev drush en dictionary_import -y

# Clear cache
ddev drush cr
```

## Usage

### Import a word via Drush

```bash
ddev drush dictionary:import hello
ddev drush dictionary:import computer
# or using the alias:
ddev drush dict-import hello
```

This creates or updates `Dictionary Entry` nodes which are then exposed over JSON:API.

### JSON:API Endpoints

**Get all dictionary entries:**

```bash
curl http://crl-dictionary-backend.ddev.site/jsonapi/node/dictionary_entry
```

**Filter by specific word (used by the frontend):**

```bash
curl "http://crl-dictionary-backend.ddev.site/jsonapi/node/dictionary_entry?filter[field_word]=hello"
```

An empty `data` array indicates "word not found", which the frontend surfaces as an inline error without navigation.

**Note on HTTP vs HTTPS:** Examples use HTTP because DDEV uses self-signed SSL certificates. The JSON:API endpoints are available at both `http://` and `https://`, but the Next.js frontend should use HTTP when connecting to DDEV locally (self-signed certificates are rejected by Node.js by default). See the frontend README for more details on environment-specific configuration.

## Development

```bash
# Clear cache after code changes
ddev drush cr

# View logs
ddev drush watchdog:show --count=20
```

## Testing

Kernel PHPUnit tests are configured via the root `phpunit.xml` and live under the custom module:

- `web/modules/custom/dictionary_import/tests/src/Kernel/DictionaryImporterTest.php`

These tests cover:

- Creating a new `Dictionary Entry` node when importing a word that does not yet exist.
- Updating an existing `Dictionary Entry` node when importing a word that already exists.
- Handling the "word not found" case from the external API without creating any nodes.

From the backend project root, run:

```bash
# Run all configured kernel tests
ddev exec ./vendor/bin/phpunit

# Or restrict to the dictionary_import kernel tests
ddev exec ./vendor/bin/phpunit web/modules/custom/dictionary_import/tests/src/Kernel/DictionaryImporterTest.php
```

See `web/modules/custom/dictionary_import/README.md` for more details.

## Troubleshooting

If the `dictionary:import` command fails with `'field_word' not found`, the content type and fields may not have been created during module installation. Run the setup command:

```bash
ddev drush dictionary:setup
# or: ddev drush dict-setup
```

## Architecture

- **Custom Module:** `dictionary_import` (in `web/modules/custom/dictionary_import`) handles importing from the external dictionary API and managing nodes.
- **Content Type:** Dictionary Entry (created programmatically on module install) with fields for the word and its definitions.
- **API:** Entries are exposed via Drupal's JSON:API module and consumed by the Next.js 15 frontend in `../crl-dictionary-frontend`.

For more implementation details (services, Drush command, JSON:API usage), see the module-level README in `web/modules/custom/dictionary_import/README.md`.

## Technical Details

Built for the Charles River Laboratories technical test. Demonstrates:

- Custom Drupal module development
- Drush command integration
- JSON:API exposure and filtering
- External API integration consumed by a Next.js 15 frontend
