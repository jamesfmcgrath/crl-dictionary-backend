# Dictionary API - Drupal Backend

Drupal 10 backend that imports dictionary definitions from an external API and exposes them via JSON:API.

## Prerequisites

- [DDEV](https://ddev.readthedocs.io/) installed
- PHP 8.3+
- Composer

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

### Import a word
```bash
ddev drush dictionary:import hello
ddev drush dictionary:import computer
```

### JSON:API Endpoints

**Get all dictionary entries:**
```bash
curl http://crl-dictionary-backend.ddev.site/jsonapi/node/dictionary_entry
```

**Filter by specific word:**
```bash
curl "http://crl-dictionary-backend.ddev.site/jsonapi/node/dictionary_entry?filter[field_word]=hello"
```

## Development
```bash
# Clear cache after code changes
ddev drush cr

# View logs
ddev drush watchdog:show --count=20
```

## Architecture

- **Custom Module:** `dictionary_import` handles importing from external API
- **Content Type:** Dictionary Entry with fields for word and definitions
- **API:** Exposed via Drupal's JSON:API module

## Technical Details

Built for Charles River Laboratories technical test. Demonstrates:
- Custom Drupal module development
- Drush command integration
- JSON:API exposure
- External API integration
