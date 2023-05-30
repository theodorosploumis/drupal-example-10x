<?php

namespace Drupal\cl_server\Controller;

use Drupal\sdc\ComponentPluginManager;
use Drupal\sdc\Exception\ComponentNotFoundException;
use Drupal\sdc\Exception\TemplateNotFoundException;
use Drupal\sdc\Plugin\Component;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Provides an endpoint for Storybook to query.
 *
 * @see https://github.com/storybookjs/storybook/tree/next/app/server
 */
class ServerEndpointController extends ControllerBase {

  /**
   * Kill-switch to avoid caching the page.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  private KillSwitch $cacheKillSwitch;

  /**
   * The discovery service.
   *
   * @var \Drupal\sdc\ComponentPluginManager
   */
  private ComponentPluginManager $pluginManager;

  /**
   * Creates an object.
   *
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $cache_kill_switch
   *   The cache kill switch.
   * @param \Drupal\sdc\ComponentPluginManager $plugin_manager
   *   The plugin manager.
   */
  public function __construct(KillSwitch $cache_kill_switch, ComponentPluginManager $plugin_manager) {
    $this->cacheKillSwitch = $cache_kill_switch;
    $this->pluginManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    $cache_kill_switch = $container->get('page_cache_kill_switch');
    assert($cache_kill_switch instanceof KillSwitch);
    $plugin_manager = $container->get('plugin.manager.sdc');
    assert($plugin_manager instanceof ComponentPluginManager);
    return new static($cache_kill_switch, $plugin_manager);
  }

  /**
   * Render a Twig template from a Storybook component directory.
   */
  public function render(Request $request): array {
    try {
      $build = $this->generateRenderArray(
        $this->getComponent($request),
        $this->getArguments($request)
      );
    }
    catch (ComponentNotFoundException $e) {
      $build = [
        '#markup' => '<div class="messages messages--error"><h3>' . $this->t('Unable to find component') . '</h3>' . $this->t('Check that the module or theme containing the component is enabled and matches the stories file name.') . '</div>',
      ];
    }
    $this->cacheKillSwitch->trigger();
    return [
      '#attached' => ['library' => ['cl_server/attach_behaviors']],
      '#type' => 'container',
      '#cache' => ['max-age' => 0],
      // Magic wrapper ID to pull the HTML from.
      '#attributes' => ['id' => '___cl-wrapper'],
      'component' => $build,
    ];
  }

  /**
   * Gets the arguments.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The inbound request.
   *
   * @return array
   *   The array of arguments.
   */
  private function getArguments(Request $request): array {
    $params = $request->query->get('_params');
    $json = base64_decode($params, TRUE);
    if ($json === FALSE) {
      throw new BadRequestHttpException('Invalid component parameters');
    }
    return Json::decode($json);
  }

  /**
   * Get the component based on the request object.
   *
   * @throws \Drupal\sdc\Exception\ComponentNotFoundException
   *   If the component cannot be found.
   */
  public function getComponent(Request $request): Component {
    $story_filename = $request->query->get('_storyFileName');
    if (!$story_filename) {
      throw new ComponentNotFoundException('Impossible to find a story with an empty story file name.');
    }
    $basename = basename($story_filename);
    [$machine_name] = explode('.', $basename);
    $provider = $this->findExtensionName($this->findStoryFile($story_filename));
    return $this->pluginManager->createInstance("$provider:$machine_name");
  }

  /**
   * Generates a render array to showcase the component with the expected
   * blocks.
   *
   * @param \Drupal\sdc\Plugin\Component $component
   *   The component.
   * @param array $context
   *   The template context.
   *
   * @return array
   *   The generated render array.
   */
  private function generateRenderArray(Component $component, array $context): array {
    $metadata = $component->metadata;
    $block_names = array_keys($metadata->slots);
    $slots = array_map(
      static fn (string $slot_str) => [
        '#type' => 'inline_template',
        '#template' => $slot_str,
        '#context' => $context,
      ],
      array_intersect_key($context, array_flip($block_names))
    );
    return [
      '#type' => 'component',
      '#component' => $component->getPluginId(),
      '#slots' => $slots,
      '#props' => array_diff_key($context, array_flip($block_names)),
    ];
  }

  /**
   * Finds the plugin ID from the story file name.
   *
   * The story file should be in the component directory, but storybook will
   * not process is from the Drupal docroot. This means we don't know what the
   * path is relative to.
   *
   * @param string $filename
   *   The filename.
   *
   * @return string
   *   The plugin ID.
   */
  private function findStoryFile(string $filename): ?string {
    if (empty($filename)) {
      return NULL;
    }
    if (file_exists($filename)) {
      return $filename;
    }
    $parts = explode(DIRECTORY_SEPARATOR, $filename);
    array_shift($parts);
    $filename = implode(DIRECTORY_SEPARATOR, $parts);
    return $this->findStoryFile($filename);
  }

  /**
   *
   */
  private function findExtensionName(string $path): ?string {
    if (empty($path)) {
      return NULL;
    }
    $path = dirname($path);
    $dir = basename($path);
    $info_file = $path . DIRECTORY_SEPARATOR . "$dir.info.yml";
    if (file_exists($info_file)) {
      return $dir;
    }
    return $this->findExtensionName($path);
  }

}
