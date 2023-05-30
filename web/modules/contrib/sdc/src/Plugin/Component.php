<?php

namespace Drupal\sdc\Plugin;

use Drupal\Core\Plugin\PluginBase;
use Drupal\sdc\Component\ComponentMetadata;
use Drupal\sdc\Exception\InvalidComponentException;

/**
 * Simple value object that contains information about the component.
 */
class Component extends PluginBase {

  /**
   * The component's metadata.
   *
   * @var \Drupal\sdc\Component\ComponentMetadata
   */
  private ComponentMetadata $metadata;

  /**
   * The component machine name.
   *
   * @var string
   */
  private string $machineName;

  /**
   * The Twig template for the component.
   *
   * @var string|null
   */
  private ?string $template;

  /**
   * The library definition to be attached with the component.
   *
   * @var array
   */
  private array $library;

  /**
   * Component constructor.
   *
   * @throws \Drupal\sdc\Exception\InvalidComponentException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->template = $plugin_definition['template'] ?? NULL;
    $this->machineName = $plugin_definition['machineName'];
    $this->library = $plugin_definition['library'] ?? [];
    $this->metadata = new ComponentMetadata(
      $plugin_definition,
      $configuration['app_root'],
      (bool) ($configuration['enforce_schemas'] ?? FALSE)
    );
    $this->validate();
  }

  /**
   * Validates the data for the component object.
   *
   * @throws \Drupal\sdc\Exception\InvalidComponentException
   *   If the component is invalid.
   */
  private function validate() {
    $machine_name = $this->getMachineName();
    $id = $this->getPluginId();
    if (!$this->template) {
      $message = sprintf('Unable to find main template %s.twig.', $machine_name);
      throw new InvalidComponentException($message);
    }
    if (strpos($id, '/') !== FALSE) {
      $message = sprintf('Component ID cannot contain slashes: %s', $id);
      throw new InvalidComponentException($message);
    }
  }

  /**
   * The template names.
   *
   * @return string|null
   *   The template.
   */
  public function getTemplate(): ?string {
    return $this->template;
  }

  /**
   * The machine name.
   *
   * @return string
   *   The name.
   */
  public function getMachineName(): string {
    return $this->machineName;
  }

  /**
   * Gets the library definition for the component.
   *
   * @return array
   *   The library definition.
   */
  public function getLibrary(): array {
    return $this->library;
  }

  /**
   * The auto-computed library name.
   *
   * @return string
   *   The library name.
   */
  public function getLibraryName(): string {
    $library_id = $this->getPluginId();
    $library_id = str_replace(':', '--', $library_id);
    return sprintf('sdc/%s', $library_id);
  }

  /**
   * Gets the component metadata.
   *
   * @return \Drupal\sdc\Component\ComponentMetadata
   *   The component metadata.
   */
  public function getMetadata(): ComponentMetadata {
    return $this->metadata;
  }

}
