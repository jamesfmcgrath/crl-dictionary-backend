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

## Development

```bash
# Clear cache after code changes
ddev drush cr

# View logs
ddev drush watchdog:show --count=20
```

## Troubleshooting

If the `dictionary:import` command fails with `'field_word' not found`, the content type and fields may not have been created during module installation. You can manually create them by running:

```bash
ddev drush php:eval "\$type = \Drupal\node\Entity\NodeType::load('dictionary_entry'); if (!\$type) { \$type = \Drupal\node\Entity\NodeType::create(['type' => 'dictionary_entry', 'name' => 'Dictionary Entry']); \$type->save(); } if (!\Drupal\field\Entity\FieldStorageConfig::loadByName('node', 'field_word')) { \$s = \Drupal\field\Entity\FieldStorageConfig::create(['field_name' => 'field_word', 'entity_type' => 'node', 'type' => 'string', 'settings' => ['max_length' => 255], 'cardinality' => 1]); \$s->save(); } if (!\Drupal\field\Entity\FieldConfig::loadByName('node', 'dictionary_entry', 'field_word')) { \$f = \Drupal\field\Entity\FieldConfig::create(['field_name' => 'field_word', 'entity_type' => 'node', 'bundle' => 'dictionary_entry', 'label' => 'Word', 'required' => TRUE]); \$f->save(); } if (!\Drupal\field\Entity\FieldStorageConfig::loadByName('node', 'field_definitions')) { \$s = \Drupal\field\Entity\FieldStorageConfig::create(['field_name' => 'field_definitions', 'entity_type' => 'node', 'type' => 'text_long', 'cardinality' => -1]); \$s->save(); } if (!\Drupal\field\Entity\FieldConfig::loadByName('node', 'dictionary_entry', 'field_definitions')) { \$f = \Drupal\field\Entity\FieldConfig::create(['field_name' => 'field_definitions', 'entity_type' => 'node', 'bundle' => 'dictionary_entry', 'label' => 'Definitions']); \$f->save(); } echo 'Setup complete\n';"
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
