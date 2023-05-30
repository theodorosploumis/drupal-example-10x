<?php

namespace Drupal\sdc\Component;

use Drupal\sdc\Exception\InvalidComponentException;

/**
 * Component metadata.
 */
final class ComponentMetadata {

  public const COMPONENT_STATUS_READY = 'READY';

  public const COMPONENT_STATUS_DEPRECATED = 'DEPRECATED';

  public const COMPONENT_STATUS_BETA = 'BETA';

  public const COMPONENT_STATUS_WIP = 'WIP';

  public const DEFAULT_DESCRIPTION = '- Description not available -';

  /**
   * The absolute path to the component directory.
   *
   * @var string
   */
  private string $path;

  /**
   * The component documentation.
   *
   * @var string
   */
  private string $documentation;

  /**
   * The status of the component.
   *
   * @var string
   */
  private string $status;

  /**
   * The machine name for the component.
   *
   * @var string
   */
  private string $machineName;

  /**
   * The component's name.
   *
   * @var string
   */
  private string $name;

  /**
   * The PNG path for the component thumbnail.
   *
   * @var string
   */
  private string $thumbnailPath;

  /**
   * The component group.
   *
   * @var string
   */
  private string $group;

  /**
   * The library dependencies.
   *
   * @var string[]
   */
  private array $libraryDependencies;

  /**
   * Schemas for the component.
   *
   * @var array[]|null
   *   The schemas.
   */
  private ?array $schemas;

  /**
   * The component description.
   *
   * @var string
   */
  private string $description;

  /**
   * TRUE if the schemas for props and slots are mandatory.
   *
   * @var bool
   */
  private bool $mandatorySchemas;

  /**
   * ComponentMetadata constructor.
   *
   * @param array $metadata_info
   *   The metadata info.
   * @param string $app_root
   *   The application root.
   * @param bool $enforce_schemas
   *   Enforces the definition of schemas for props and slots.
   *
   * @throws \Drupal\sdc\Exception\InvalidComponentException
   */
  public function __construct(array $metadata_info, string $app_root, bool $enforce_schemas) {
    $path = $metadata_info['path'];
    // Make the absolute path, relative to the Drupal root.
    $app_root = rtrim($app_root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (str_starts_with($path, $app_root)) {
      $path = substr($path, strlen($app_root));
    }
    $this->mandatorySchemas = $enforce_schemas;
    $this->path = $path;

    $path_parts = explode('/', $path);
    $folder_name = end($path_parts);
    $this->machineName = $metadata_info['machineName'] ?? $folder_name;
    $this->name = $metadata_info['name'] ?? ucwords($this->machineName);
    $this->description = $metadata_info['description'] ?? static::DEFAULT_DESCRIPTION;
    $this->status = $metadata_info['status'] ?? static::COMPONENT_STATUS_READY;
    $this->libraryDependencies = $metadata_info['libraryDependencies'] ?? [];
    $this->documentation = $metadata_info['documentation'] ?? '';

    $this->group = $metadata_info['group'] ?? 'All Components';

    // Save the schemas.
    $this->parseSchemaInfo($metadata_info);
  }

  /**
   * Parse the schema information.
   *
   * @param array $metadata_info
   *   The metadata information as decoded from the component definition file.
   *
   * @throws \Drupal\sdc\Exception\InvalidComponentException
   */
  private function parseSchemaInfo(array $metadata_info): void {
    if (empty($metadata_info['schemas'])) {
      if ($this->mandatorySchemas) {
        throw new InvalidComponentException(sprintf('The component "%s" does not provide schema information. Schema definitions are mandatory for components declared in modules. For components declared in themes, schema definitions are only mandatory if the "enforce_sdc_schemas" key is set to "true" in the theme info file.', $metadata_info['id']));
      }
      $this->schemas = NULL;
      return;
    }
    $default_schema = [
      'type' => 'object',
      'additionalProperties' => FALSE,
      'required' => [],
      'properties' => [],
    ];
    $this->schemas['props'] = $metadata_info['schemas']['props'] ?? $default_schema;
    $this->schemas['slots'] = $metadata_info['schemas']['slots'] ?? $default_schema;
    foreach (['props', 'slots'] as $key) {
      if (($this->schemas[$key]['type'] ?? 'object') !== 'object') {
        throw new InvalidComponentException('The schema for the props in the component metadata is invalid. The schema should be of type "object".');
      }
      if ($this->schemas[$key]['additionalProperties'] ?? FALSE) {
        throw new InvalidComponentException('The schema for the props in the component metadata is invalid. Arbitrary additional properties are not allowed.');
      }
      $this->schemas[$key]['additionalProperties'] = FALSE;
    }
    foreach ($this->schemas['slots']['properties'] as $slot_name => $slot_schema) {
      if (($slot_schema['type']) !== 'string') {
        $message = sprintf(
          'Slots can only be declared with the type "string". Slot [%s] in component "%s" is declared with type "%s".',
          $slot_name,
          $this->machineName,
          $slot_schema['type']
        );
        throw new InvalidComponentException($message);
      }
    }
    // Save the props.
    $schema_props = $metadata_info['schemas']['props'] ?? $default_schema;
    foreach ($schema_props['properties'] ?? [] as $name => $schema) {
      // All props should also support "object" this allows deferring rendering
      // in Twig to the render pipeline.
      $type = $schema['type'] ?? '';
      if (!is_array($type)) {
        $type = [$type];
      }
      $schema['type'] = array_unique([...$type, 'object']);
      $this->schemas['props']['properties'][$name]['type'] = $type;
    }
  }

  /**
   * Gets the documentation.
   *
   * @return string
   *   The HTML documentation.
   */
  public function getDocumentation(): string {
    return $this->documentation;
  }

  /**
   * Gets the thumbnail path.
   *
   * @return string
   *   The path.
   */
  public function getThumbnailPath(): string {
    if (!isset($this->thumbnailPath)) {
      $thumbnail_path = sprintf('%s/thumbnail.png', $this->path);
      $this->thumbnailPath = file_exists($thumbnail_path) ? $thumbnail_path : '';
    }
    return $this->thumbnailPath;
  }

  /**
   * Normalizes the value object.
   *
   * @return array
   *   The normalized value object.
   */
  public function normalize(): array {
    return [
      'path' => $this->getPath(),
      'machineName' => $this->getMachineName(),
      'status' => $this->getStatus(),
      'name' => $this->getName(),
      'group' => $this->getGroup(),
      'libraryDependencies' => $this->getLibraryDependencies(),
    ];
  }

  /**
   * Gets the path.
   *
   * @return string
   *   The path.
   */
  public function getPath(): string {
    return $this->path;
  }

  /**
   * Gets the machine name.
   *
   * @return string
   *   The machine name.
   */
  public function getMachineName(): string {
    return $this->machineName;
  }

  /**
   * Gets the status.
   *
   * @return string
   *   The status.
   */
  public function getStatus(): string {
    return $this->status;
  }

  /**
   * Gets the name.
   *
   * @return string
   *   The name.
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * Gets the group.
   *
   * @return string
   *   The group.
   */
  public function getGroup(): string {
    return $this->group;
  }

  /**
   * Gets the library dependencies.
   *
   * @return string[]
   *   The dependencies.
   */
  public function getLibraryDependencies(): array {
    return $this->libraryDependencies;
  }

  /**
   * Gets the schemas.
   *
   * @return array|null
   *   The schemas.
   */
  public function getSchemas(): ?array {
    return $this->schemas;
  }

  /**
   * Get the description.
   *
   * @return string
   *   The description.
   */
  public function getDescription(): string {
    return $this->description;
  }

  /**
   * Check of schemas for this component are mandatory.
   *
   * @return bool
   *   TRUE if the component validation should fail when schemas aren't present.
   */
  public function shouldEnforceSchemas(): bool {
    return $this->mandatorySchemas;
  }

}
