<?php

namespace Drupal\dictionary_import\Drush\Commands;

use Drupal\dictionary_import\Service\DictionaryImporter;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush commands for dictionary import.
 */
class DictionaryCommands extends DrushCommands {

  /**
   * The dictionary importer service.
   *
   * @var \Drupal\dictionary_import\Service\DictionaryImporter
   */
  protected $importer;

  /**
   * Constructs a DictionaryCommands object.
   *
   * @param \Drupal\dictionary_import\Service\DictionaryImporter $importer
   *   The dictionary importer service.
   */
  public function __construct(DictionaryImporter $importer) {
    parent::__construct();
    $this->importer = $importer;
  }

  /**
   * Factory to create command instance with dependencies from container.
   *
   * Required for Drush 12+ PSR4 command discovery.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   * @param \Psr\Container\ContainerInterface|null $drushContainer
   *   The Drush container (unused).
   *
   * @return static
   */
  public static function create(ContainerInterface $container, $drushContainer = null): static {
    $importer = $container->get('dictionary_import.importer');
    return new static($importer);
  }

  /**
   * Set up the Dictionary Entry content type and fields.
   *
   * @command dictionary:setup
   * @aliases dict-setup
   * @usage dictionary:setup
   *   Create the Dictionary Entry content type and required fields.
   */
  public function setup() {
    $this->output()->writeln('Setting up Dictionary Entry content type and fields...');

    try {
      // Create the Dictionary Entry content type.
      if (!NodeType::load('dictionary_entry')) {
        $type = NodeType::create([
          'type' => 'dictionary_entry',
          'name' => 'Dictionary Entry',
        ]);
        $type->save();
        \Drupal::entityTypeManager()->clearCachedDefinitions();
        $this->output()->writeln('<info>Created Dictionary Entry content type</info>');
      }
      else {
        $this->output()->writeln('Dictionary Entry content type already exists');
      }

      // Create field_word storage (plain text, required on the bundle).
      if (!FieldStorageConfig::loadByName('node', 'field_word')) {
        $storage = FieldStorageConfig::create([
          'field_name' => 'field_word',
          'entity_type' => 'node',
          'type' => 'string',
          'settings' => [
            'max_length' => 255,
          ],
          'cardinality' => 1,
        ]);
        $storage->save();
        $this->output()->writeln('<info>Created field_word storage</info>');
      }

      if (!FieldConfig::loadByName('node', 'dictionary_entry', 'field_word')) {
        $field = FieldConfig::create([
          'field_name' => 'field_word',
          'entity_type' => 'node',
          'bundle' => 'dictionary_entry',
          'label' => 'Word',
          'required' => TRUE,
        ]);
        $field->save();
        $this->output()->writeln('<info>Created field_word field</info>');
      }
      else {
        $this->output()->writeln('field_word already exists');
      }

      // Create field_definitions storage (long text field).
      if (!FieldStorageConfig::loadByName('node', 'field_definitions')) {
        $storage = FieldStorageConfig::create([
          'field_name' => 'field_definitions',
          'entity_type' => 'node',
          'type' => 'text_long',
          'cardinality' => -1,
        ]);
        $storage->save();
        $this->output()->writeln('<info>Created field_definitions storage</info>');
      }

      if (!FieldConfig::loadByName('node', 'dictionary_entry', 'field_definitions')) {
        $field = FieldConfig::create([
          'field_name' => 'field_definitions',
          'entity_type' => 'node',
          'bundle' => 'dictionary_entry',
          'label' => 'Definitions',
        ]);
        $field->save();
        $this->output()->writeln('<info>Created field_definitions field</info>');
      }
      else {
        $this->output()->writeln('field_definitions already exists');
      }

      $this->output()->writeln('<info>Setup complete!</info>');
    }
    catch (\Exception $e) {
      $this->output()->writeln(sprintf('<error>Error during setup: %s</error>', $e->getMessage()));
      throw $e;
    }
  }

  /**
   * Import a word from the external dictionary API.
   *
   * @param string $word
   *   The word to import.
   *
   * @command dictionary:import
   * @aliases dict-import
   * @usage dictionary:import hello
   *   Import the word "hello" from the external API.
   */
  public function import(string $word) {
    $this->output()->writeln(sprintf('Importing word: %s', $word));

    try {
      $success = $this->importer->importWord($word);

      if ($success) {
        $this->output()->writeln(sprintf('<info>Successfully imported: %s</info>', $word));
      }
      else {
        $this->output()->writeln(sprintf('<error>Failed to import: %s (word not found in external API)</error>', $word));
      }
    }
    catch (\Exception $e) {
      $this->output()->writeln(sprintf('<error>Error importing word: %s</error>', $e->getMessage()));
      throw $e;
    }
  }

}
