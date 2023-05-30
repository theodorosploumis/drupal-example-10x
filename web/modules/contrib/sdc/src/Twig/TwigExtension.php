<?php

namespace Drupal\sdc\Twig;

use Drupal\Component\Utility\Html;
use Drupal\Core\Template\Attribute;
use Drupal\sdc\Component\ComponentValidator;
use Drupal\sdc\ComponentPluginManager;
use Drupal\sdc\Exception\ComponentNotFoundException;
use Drupal\sdc\Exception\InvalidComponentException;
use Drupal\sdc\Plugin\Component;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * The twig extension so Drupal can recognize the new code.
 */
class TwigExtension extends AbstractExtension {

  /**
   * The plugin manager.
   *
   * @var \Drupal\sdc\ComponentPluginManager
   */
  private ComponentPluginManager $pluginManager;

  /**
   * The component validator.
   *
   * @var \Drupal\sdc\Component\ComponentValidator
   */
  private ComponentValidator $componentValidator;

  /**
   * Creates TwigExtension.
   *
   * @param \Drupal\sdc\ComponentPluginManager $plugin_manager
   *   The component plugin manager.
   * @param \Drupal\sdc\Component\ComponentValidator $component_validator
   *   The component validator.
   */
  public function __construct(ComponentPluginManager $plugin_manager, ComponentValidator $component_validator) {
    $this->pluginManager = $plugin_manager;
    $this->componentValidator = $component_validator;
  }

  /**
   * {@inheritdoc}
   */
  public function getNodeVisitors(): array {
    return [new ComponentNodeVisitor()];
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions(): array {
    return [
      new TwigFunction(
        'sdc_additional_context',
        [$this, 'addAdditionalContext'],
        ['needs_context' => TRUE]
      ),
      new TwigFunction(
        'sdc_validate_props',
        [$this, 'validateProps'],
        ['needs_context' => TRUE]
      ),
    ];
  }

  /**
   * Appends additional context to the template based on the template id.
   *
   * @param array &$context
   *   The context.
   * @param string $component_id
   *   The component ID.
   *
   * @throws \Drupal\sdc\Exception\ComponentNotFoundException
   */
  public function addAdditionalContext(array &$context, string $component_id): void {
    $component = $this->pluginManager->find($component_id);
    $context = array_merge(
      $context,
      $this->computeAdditionalRenderContext($component)
    );
  }

  /**
   * Calculates additional context for this template.
   *
   * @param \Drupal\sdc\Plugin\Component $component
   *   The component.
   *
   * @return array
   *   The additional context to inject to component templates.
   */
  protected function computeAdditionalRenderContext(Component $component): array {
    $metadata = $component->getMetadata();
    $status = $metadata->getStatus();
    $machine_name = $component->getMachineName();
    $classes = array_map([Html::class, 'cleanCssIdentifier'], [
      'sdc',
      'sdc--' . $machine_name,
      'sdc--' . $status,
    ]);
    $classes = array_map('strtolower', $classes);
    $attributes = [
      'class' => $classes,
      'data-sdc-id' => $component->getPluginId(),
    ];
    return [
      'sdcAttributes' => new Attribute($attributes),
      'sdcMeta' => $metadata->normalize(),
    ];
  }

  /**
   * Validates the props in development environments.
   *
   * @param array $context
   *   The context provided to the component.
   * @param string $component_id
   *   The component ID.
   *
   * @throws \Drupal\sdc\Exception\InvalidComponentException
   */
  public function validateProps(array &$context, string $component_id): void {
    assert($this->doValidateProps($context, $component_id));
  }

  /**
   * Performs the actual validation of the schema for the props.
   *
   * @param array $context
   *   The context provided to the component.
   * @param string $component_id
   *   The component ID.
   *
   * @return bool
   *   TRUE if it's valid.
   *
   * @throws \Drupal\sdc\Exception\InvalidComponentException
   */
  public function doValidateProps(array $context, string $component_id): bool {
    try {
      return $this->componentValidator->validateProps(
        $context,
        $this->pluginManager->find($component_id)
      );
    }
    catch (ComponentNotFoundException $e) {
      throw new InvalidComponentException($e->getMessage(), $e->getCode(), $e);
    }
  }

}
