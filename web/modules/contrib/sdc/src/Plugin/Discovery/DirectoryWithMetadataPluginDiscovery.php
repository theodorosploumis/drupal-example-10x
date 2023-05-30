<?php

namespace Drupal\sdc\Plugin\Discovery;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\sdc\Utilities;

/**
 * Discover directories that contain a specific metadata file.
 */
class DirectoryWithMetadataPluginDiscovery extends YamlDiscovery {

  /**
   * Constructs a YamlDirectoryDiscovery object.
   *
   * @param array $directories
   *   An array of directories to scan, keyed by the provider. The value can
   *   either be a string or an array of strings. The string values should be
   *   the path of a directory to scan.
   * @param string $file_cache_key_suffix
   *   The file cache key suffix. This should be unique for each type of
   *   discovery.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(array $directories, $file_cache_key_suffix, FileSystemInterface $file_system) {
    // Intentionally does not call parent constructor as this class uses a
    // different YAML discovery.
    $this->discovery = new DirectoryWithMetadataDiscovery($directories, $file_cache_key_suffix, $file_system);
  }

  /**
   * Finds assets related to the provided metadata file.
   *
   * @param string $component_directory
   *   The component directory for the plugin.
   * @param string $machine_name
   *   The component's machine name.
   * @param string $file_extension
   *   The file extension to detect.
   * @param bool $make_relative
   *   TRUE to make the filename relative to the SDC module location.
   *
   * @return string|null
   *   Filenames, maybe relative to the sdc module.
   */
  public function findAsset(string $component_directory, string $machine_name, string $file_extension, bool $make_relative = FALSE): ?string {
    $sdc_module_path = dirname(__FILE__, 4);
    $absolute_path = sprintf('%s%s%s.%s', $component_directory, DIRECTORY_SEPARATOR, $machine_name, $file_extension);
    if (!file_exists($absolute_path)) {
      return NULL;
    }
    return $make_relative
      ? Utilities::makePathRelative($absolute_path, $sdc_module_path)
      : $absolute_path;
  }

}
