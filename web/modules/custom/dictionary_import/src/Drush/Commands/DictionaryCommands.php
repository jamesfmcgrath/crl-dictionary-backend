<?php

namespace Drupal\dictionary_import\Drush\Commands;

use Drupal\dictionary_import\Service\DictionaryImporter;
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
