<?php

namespace Drupal\sdc\Twig;

use Drupal\sdc\ComponentPluginManager;
use Drupal\sdc\Exception\ComponentNotFoundException;
use Drupal\sdc\Exception\TemplateNotFoundException;
use Drupal\sdc\Plugin\Component;
use Drupal\Component\Discovery\YamlDirectoryDiscovery;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;

/**
 * Lets you load templates using the component ID.
 */
class TwigComponentLoader implements LoaderInterface {

  /**
   * The plugin manager.
   *
   * @var \Drupal\sdc\ComponentPluginManager
   */
  protected ComponentPluginManager $pluginManager;

  /**
   * Constructs a new ComponentLoader object.
   *
   * @param \Drupal\sdc\ComponentPluginManager $plugin_manager
   *   The plugin manager.
   */
  public function __construct(ComponentPluginManager $plugin_manager) {
    $this->pluginManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Twig\Error\LoaderError
   *   Thrown if a template matching $name cannot be found.
   */
  protected function findTemplate($name, $throw = TRUE) {
    $path = $name;
    try {
      $component = $this->parseIdAndLoadComponent($name);
      $template = $component->getTemplate();
      $path = sprintf(
        '%s%s%s',
        $component->getMetadata()->getPath(),
        DIRECTORY_SEPARATOR,
        $template
      );
    }
    catch (ComponentNotFoundException | TemplateNotFoundException  $e) {
      if ($throw) {
        throw new LoaderError($e->getMessage(), $e->getCode(), $e);
      }
    }
    if ($path || !$throw) {
      return $path;
    }

    throw new LoaderError(sprintf('Unable to find template "%s" in the components registry.', $name));
  }

  /**
   * {@inheritdoc}
   */
  public function exists($name): bool {
    if (!preg_match('/^[a-zA-Z][a-zA-Z0-9:_-]*[a-zA-Z0-9]?$/', $name)) {
      return FALSE;
    }
    try {
      $this->parseIdAndLoadComponent($name);
      return TRUE;
    }
    catch (ComponentNotFoundException $e) {
      watchdog_exception('sdc', $e);
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceContext($name): Source {
    try {
      $component = $this->parseIdAndLoadComponent($name);
      $path = $component->getMetadata()->getPath()
        . DIRECTORY_SEPARATOR
        . $component->getTemplate();
    }
    catch (ComponentNotFoundException | TemplateNotFoundException $e) {
      return new Source('', $name, '');
    }
    $original_code = file_get_contents($path);
    return new Source($original_code, $name, $path);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheKey($name): string {
    try {
      $component = $this->parseIdAndLoadComponent($name);
    }
    catch (ComponentNotFoundException | TemplateNotFoundException $e) {
      throw new LoaderError('Unable to find component');
    }
    return implode('--', array_filter([
      'sdc',
      $name,
      $component->getPluginDefinition()['provider'] ?? '',
    ]));
  }

  /**
   * {@inheritdoc}
   */
  public function isFresh(string $name, int $time): bool {
    $file_is_fresh = static fn(string $path) => filemtime($path) < $time;
    try {
      $component = $this->parseIdAndLoadComponent($name);
    }
    catch (ComponentNotFoundException | TemplateNotFoundException $e) {
      throw new LoaderError('Unable to find component');
    }
    // If any of the templates, or the component definition, are fresh. Then the
    // component is fresh.
    $metadata_path = $component->getPluginDefinition()[YamlDirectoryDiscovery::FILE_KEY];
    if ($file_is_fresh($metadata_path)) {
      return TRUE;
    }
    return array_reduce(
      array_map(
        static fn(string $name) => $component->getMetadata()
          ->getPath() . DIRECTORY_SEPARATOR . $name,
        $component->getTemplates()
      ),
      static fn(bool $fresh, string $path) => $fresh || $file_is_fresh($path),
      FALSE
    );
  }

  /**
   * Parse ID from the template key.
   *
   * @param string $name
   *   The template name as provided in the include/embed.
   *
   * @return \Drupal\sdc\Plugin\Component
   *   The component.
   *
   * @throws \Drupal\sdc\Exception\ComponentNotFoundException
   */
  private function parseIdAndLoadComponent(string $name): Component {
    // First check if we can parse the prefix from the name.
    return $this->pluginManager->find($name);
  }

}
