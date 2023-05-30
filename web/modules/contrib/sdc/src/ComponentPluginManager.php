<?php

namespace Drupal\sdc;

use Drupal\Component\Discovery\YamlDirectoryDiscovery;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Factory\ContainerFactory;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\sdc\Component\ComponentValidator;
use Drupal\sdc\Component\SchemaCompatibilityChecker;
use Drupal\sdc\Exception\ComponentNotFoundException;
use Drupal\sdc\Exception\IncompatibleComponentSchema;
use Drupal\sdc\Plugin\Component;
use Drupal\sdc\Plugin\Discovery\DirectoryWithMetadataPluginDiscovery;

/**
 * Defines a plugin manager to deal with sdc.
 *
 * Modules and themes can create components by adding a folder under
 * MODULENAME/components/my-component/my-component.sdc.yml.
 *
 * @see plugin_api
 */
class ComponentPluginManager extends DefaultPluginManager {

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected ThemeHandlerInterface $themeHandler;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected ThemeManagerInterface $themeManager;

  /**
   * The component negotiator.
   *
   * @var \Drupal\sdc\ComponentNegotiator
   */
  protected ComponentNegotiator $componentNegotiator;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected FileSystemInterface $fileSystem;

  /**
   * The compatibility checker.
   *
   * @var \Drupal\sdc\Component\SchemaCompatibilityChecker
   */
  protected SchemaCompatibilityChecker $compatibilityChecker;

  /**
   * {@inheritdoc}
   */
  protected $defaults = [
    'class' => Component::class,
  ];

  /**
   * The app root.
   *
   * @var string
   */
  private string $appRoot;

  /**
   * The component validator.
   *
   * @var \Drupal\sdc\Component\ComponentValidator
   */
  private $componentValidator;

  /**
   * Constructs SdcPluginManager object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager.
   * @param \Drupal\sdc\ComponentNegotiator $component_negotiator
   *   The component negotiator.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\sdc\Component\SchemaCompatibilityChecker $compatibility_checker
   *   The compatibility checker.
   * @param \Drupal\sdc\Component\ComponentValidator $component_validator
   *   The component validator.
   * @param string $app_root
   *   The path to the Drupal root.
   */
  public function __construct(
    ModuleHandlerInterface $module_handler,
    ThemeHandlerInterface $theme_handler,
    CacheBackendInterface $cache_backend,
    ConfigFactoryInterface $config_factory,
    ThemeManagerInterface $theme_manager,
    ComponentNegotiator $component_negotiator,
    FileSystemInterface $file_system,
    SchemaCompatibilityChecker $compatibility_checker,
    ComponentValidator $component_validator,
    string $app_root,
  ) {
    $this->factory = new ContainerFactory($this);
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->configFactory = $config_factory;
    $this->themeManager = $theme_manager;
    $this->componentNegotiator = $component_negotiator;
    $this->fileSystem = $file_system;
    $this->compatibilityChecker = $compatibility_checker;
    $this->setCacheBackend($cache_backend, 'sdc_plugins');
    $this->componentValidator = $component_validator;
    $this->appRoot = $app_root;
  }

  /**
   * Creates an instance.
   *
   * @throws \Drupal\sdc\Exception\ComponentNotFoundException
   *
   * @internal
   */
  public function createInstance($plugin_id, array $configuration = []): Component {
    $configuration['app_root'] = $this->appRoot;
    $configuration['enforce_schemas'] = $this->shouldEnforceSchemas(
      $this->definitions[$plugin_id] ?? []
    );
    try {
      $instance = parent::createInstance($plugin_id, $configuration);
      if (!$instance instanceof Component) {
        throw new ComponentNotFoundException(sprintf(
          'Unable to find component "%s" in the component repository.',
          $plugin_id,
        ));
      }
      return $instance;
    }
    catch (PluginException $e) {
      // Cast the PluginNotFound to a more specific exception.
      $message = sprintf(
        'Unable to find component "%s" in the component repository. [%s]',
        $plugin_id,
        $e->getMessage()
      );
      throw new ComponentNotFoundException($message, $e->getCode(), $e);
    }
  }

  /**
   * Creates instance catching exceptions.
   */
  public function createInstanceAndCatch(string $plugin_id): ?Component {
    try {
      return $this->createInstance($plugin_id);
    }
    catch (ComponentNotFoundException $e) {
      return NULL;
    }
  }

  /**
   * Gets a component for rendering.
   *
   * @param string $machine_name
   *   The machine name.
   *
   * @return \Drupal\sdc\Plugin\Component
   *   The component.
   *
   * @throws \Drupal\sdc\Exception\ComponentNotFoundException
   *
   * @internal
   */
  public function find(string $machine_name): Component {
    $definitions = $this->getDefinitions();
    if (empty($definitions)) {
      throw new ComponentNotFoundException('Unable to find any component definition.');
    }
    return $this->createInstance(
      $this->componentNegotiator->negotiate($machine_name, $definitions)
    );
  }

  /**
   * Gets all components.
   *
   * @return \Drupal\sdc\Plugin\Component[]
   *   An array of Component objects.
   *
   * @internal
   */
  public function getAllComponents(): array {
    $plugin_ids = array_keys($this->getDefinitions());
    return array_values(array_filter(array_map(
      [$this, 'createInstanceAndCatch'],
      $plugin_ids
    )));
  }

  /**
   * Creates the library declaration array from a component definition.
   *
   * @param array $definition
   *   The component definition.
   *
   * @return array
   *   The library for the Library API.
   */
  protected function libraryFromDefinition(array $definition): array {
    $metadata_path = $definition[YamlDirectoryDiscovery::FILE_KEY];
    $component_directory = $this->fileSystem->dirname($metadata_path);
    // Add the JS and CSS files.
    $library = [];
    $css_file = $this->getDiscovery()
      ->findAsset($component_directory, $definition['machineName'], 'css', TRUE);
    if ($css_file) {
      $library['css'] = ['component' => [$css_file => []]];
    }
    $js_file = $this->getDiscovery()
      ->findAsset($component_directory, $definition['machineName'], 'js', TRUE);
    if ($js_file) {
      $library['js'] = [$js_file => []];
    }
    $library['dependencies'] = array_merge(
    // Ensure that 'core/drupal' is always present.
      ['core/drupal'],
      $definition['libraryDependencies'] ?? []
    );
    // We allow component authors to use library overrides to use files relative
    // to the component directory. So we need to fix the paths here.
    $overrides = $this->fixLibraryPaths(
      $definition['libraryOverrides'] ?? [],
      $component_directory
    );
    // Apply library overrides.
    $library = array_merge(
      $library,
      $overrides
    );
    return $library;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $directories = $this->getScanDirectories();
      $this->discovery = new DirectoryWithMetadataPluginDiscovery($directories, 'sdc', $this->fileSystem);
    }
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  protected function providerExists($provider) {
    return $this->moduleHandler->moduleExists($provider) || $this->themeHandler->themeExists($provider);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\sdc\Exception\InvalidComponentException
   * @throws \Drupal\sdc\Exception\IncompatibleComponentSchema
   */
  protected function alterDefinitions(&$definitions) {
    // Save in the definition weather this is a module or a theme. This is
    // important because when creating the plugin instance (the Component
    // object) we'll need to negotiate based on the active theme.
    $definitions = array_map([$this, 'alterDefinition'], $definitions);
    // Validate the definition after alterations.
    $definitions = array_filter(
      $definitions,
      function (array $definition) {
        // Wrap in an assertion to ensure we do this only during development.
        assert($this->isValidDefinition($definition));
        return TRUE;
      }
    );
    // The metadata file is *.component.yml, the first bit should match the
    // machine name.
    $invalid_definition_ids = array_keys(array_filter(
      $definitions,
      static fn(array $definition) => str_ends_with($definition[YamlDirectoryDiscovery::FILE_KEY] ?? '', '.component.yml')
        && !str_ends_with($definition[YamlDirectoryDiscovery::FILE_KEY] ?? '', DIRECTORY_SEPARATOR . $definition['machineName'] . '.component.yml')
    ));
    $definitions = array_diff_key($definitions, array_flip($invalid_definition_ids));
    parent::alterDefinitions($definitions);

    // Finally, validate replacements.
    $replacing_definitions = array_filter(
      $definitions,
      static fn(array $definition) => ($definition['replaces'] ?? NULL) && ($definitions[$definition['replaces']] ?? NULL)
    );
    $validation_errors = array_reduce($replacing_definitions, function (array $errors, array $new_definition) use ($definitions) {
      $original_definition = $definitions[$new_definition['replaces']];
      $original_schemas = $original_definition['schemas'] ?? NULL;
      $new_schemas = $new_definition['schemas'] ?? NULL;
      if (!$original_schemas || !$new_schemas) {
        return [
          sprintf(
            "Component \"%s\" is attempting to replace \"%s\", however component replacement requires both components to have schema definitions.",
            $new_definition['id'],
            $original_definition['id'],
          ),
        ];
      }
      try {
        // $schema_types will likely be ['props', 'slots'].
        $schema_types = array_unique([
          ...array_keys($original_schemas),
          ...array_keys($new_schemas),
        ]);
        array_walk(
          $schema_types,
          fn(string $schema_type) => $this->compatibilityChecker
            ->isCompatible(
              $original_definition['schemas'][$schema_type] ?? [],
              $new_definition['schemas'][$schema_type] ?? []
            )
        );
      }
      catch (IncompatibleComponentSchema $e) {
        $errors[] = sprintf(
          "\"%s\" is incompatible with the component is wants to replace \"%s\". Errors:\n%s",
          $new_definition['id'],
          $original_definition['id'],
          $e->getMessage()
        );
      }
      return $errors;
    }, []);
    if (!empty($validation_errors)) {
      throw new IncompatibleComponentSchema(implode("\n", $validation_errors));
    }
  }

  /**
   * Alters the plugin definition with computed properties.
   *
   * @param array $definition
   *   The definition.
   *
   * @return array
   *   The altered definition.
   */
  protected function alterDefinition(array $definition): array {
    $definition['extension_type'] = $this->moduleHandler->moduleExists($definition['provider'] ?? '')
      ? ExtensionType::Module
      : ExtensionType::Theme;
    $metadata_path = $definition[YamlDirectoryDiscovery::FILE_KEY];
    $component_directory = $this->fileSystem->dirname($metadata_path);
    $definition['path'] = $component_directory;
    [, $machine_name] = explode(':', $definition['id'] ?? '');
    $definition['machineName'] = $machine_name;
    $definition['library'] = $this->libraryFromDefinition($definition);
    // Discover the template.
    $template = $this->getDiscovery()
      ->findAsset($component_directory, $definition['machineName'], 'twig', FALSE);
    $definition['template'] = basename($template);
    $definition['documentation'] = 'No documentation found. Add a README.md in your component directory and install the package: https://commonmark.thephpleague.com/';
    $documentation_path = sprintf('%s/README.md', $this->fileSystem->dirname($metadata_path));
    if (class_exists('\League\CommonMark\CommonMarkConverter') && file_exists($documentation_path)) {
      $documentation_md = file_get_contents($documentation_path);
      // phpcs:ignore Drupal.Classes.FullyQualifiedNamespace.UseStatementMissing
      $converter = new \League\CommonMark\CommonMarkConverter();
      $definition['documentation'] = $converter->convert($documentation_md)
        ->getContent();
    }
    return $definition;
  }

  /**
   * Validates the metadata info.
   *
   * @param array $definition
   *   The component definition.
   *
   * @return bool
   *   TRUE if it's valid.
   *
   * @throws \Drupal\sdc\Exception\InvalidComponentException
   */
  private function isValidDefinition(array $definition): bool {
    return $this->componentValidator->validateDefinition(
      $definition,
      $this->shouldEnforceSchemas($definition)
    );
  }

  /**
   * Get the list of directories to scan.
   *
   * @return string[]
   *   The directories.
   */
  private function getScanDirectories(): array {
    $extension_directories = [
      ...$this->moduleHandler->getModuleDirectories(),
      ...$this->themeHandler->getThemeDirectories(),
    ];
    return array_map(
      static fn(string $path) => rtrim(sprintf(
        '%s%s%s',
        rtrim($path, DIRECTORY_SEPARATOR),
        DIRECTORY_SEPARATOR,
        'components'
      ), DIRECTORY_SEPARATOR),
      $extension_directories
    );
  }

  /**
   * Changes the library paths, so they can be used by the library system.
   *
   * We need this so we can let users apply overrides to JS and CSS files with
   * paths relative to the component.
   *
   * @param array $overrides
   *   The library overrides as provided by the component author.
   * @param string $component_directory
   *   The directory for the component.
   *
   * @return array
   *   The overrides with the fixed paths.
   */
  private function fixLibraryPaths(array $overrides, string $component_directory): array {
    $sdc_module_path = dirname(__FILE__, 2);
    // We only alter the keys of the CSS and JS entries.
    $fixed_overrides = $overrides;
    unset($fixed_overrides['css'], $fixed_overrides['js']);
    $css = $overrides['css'] ?? [];
    $js = $overrides['js'] ?? [];
    foreach ($css as $dir => $css_info) {
      foreach ($css_info as $filename => $options) {
        $absolute_filename = sprintf('%s%s%s', $component_directory, DIRECTORY_SEPARATOR, $filename);
        $fixed_filename = Utilities::makePathRelative($absolute_filename, $sdc_module_path);
        $fixed_overrides['css'][$dir][$fixed_filename] = $options;
      }
    }
    foreach ($js as $filename => $options) {
      $absolute_filename = sprintf('%s%s%s', $component_directory, DIRECTORY_SEPARATOR, $filename);
      $fixed_filename = Utilities::makePathRelative($absolute_filename, $sdc_module_path);
      $fixed_overrides['js'][$fixed_filename] = $options;
    }
    return $fixed_overrides;
  }

  /**
   * Assess weather schemas are mandatory for props and slots.
   *
   * Schemas are always mandatory for component provided by modules. It depends
   * on a theme setting for theme components.
   *
   * @param array $definition
   *   The plugin definition.
   *
   * @return bool
   *   TRUE if schemas are mandatory.
   */
  private function shouldEnforceSchemas(array $definition): bool {
    $provider_type = $definition['extension_type'] ?? NULL;
    if ($provider_type !== ExtensionType::Theme) {
      return TRUE;
    }
    return $this->themeHandler
      ->getTheme($definition['provider'])
      ?->info['enforce_sdc_schemas'] ?? FALSE;
  }

}
