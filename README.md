# Dictionary API - Drupal Backend

Drupal 10 backend that imports dictionary definitions from an external API and exposes them via JSON:API for the Next.js frontend.

## Prerequisites

- [DDEV](https://ddev.readthedocs.io/) installed
- PHP 8.3+
- Composer

The examples below assume the DDEV project name is `crl-dictionary-backend`, which gives a base URL of `http://crl-dictionary-backend.ddev.site`. Adjust URLs if your project name differs.

## Setup

```bash
# Start DDEV
ddev start

# Install dependencies (if cloning)
ddev composer install

# Install Drupal (if fresh setup)
ddev drush site:install standard --site-name="Dictionary API" --account-pass=admin -y

# Enable custom module
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

## Architecture

- **Custom Module:** `dictionary_import` (in `web/modules/custom/dictionary_import`) handles importing from the external dictionary API and managing nodes.
- **Content Type:** Dictionary Entry with fields for the word and its definitions.
- **API:** Entries are exposed via Drupal's JSON:API module and consumed by the Next.js 15 frontend in `../crl-dictionary-frontend`.

For more implementation details (services, Drush command, JSON:API usage), see the module-level README in `web/modules/custom/dictionary_import/README.md`.

## Technical Details

Built for the Charles River Laboratories technical test. Demonstrates:

- Custom Drupal module development
- Drush command integration
- JSON:API exposure and filtering
- External API integration consumed by a Next.js 15 frontend
