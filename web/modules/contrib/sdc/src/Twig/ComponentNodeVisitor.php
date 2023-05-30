<?php

namespace Drupal\sdc\Twig;

use Drupal\sdc\ComponentPluginManager;
use Drupal\sdc\Exception\ComponentNotFoundException;
use Drupal\sdc\Exception\InvalidComponentException;
use Drupal\sdc\Plugin\Component;
use Drupal\sdc\Utilities;
use Twig\Environment;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\ModuleNode;
use Twig\Node\Node;
use Twig\Node\PrintNode;
use Twig\NodeVisitor\NodeVisitorInterface;

/**
 * Provides a ComponentNodeVisitor to change the generated parse-tree.
 */
class ComponentNodeVisitor implements NodeVisitorInterface {

  /**
   * The list of collected nodes on entry.
   *
   * @var \Twig\Node\ModuleNode[]
   */
  protected array $collectedNodes = [];

  /**
   * {@inheritdoc}
   */
  public function enterNode(Node $node, Environment $env): Node {
    return $node;
  }

  /**
   * {@inheritdoc}
   */
  public function leaveNode(Node $node, Environment $env): ?Node {
    if (!$node instanceof ModuleNode) {
      return $node;
    }
    $component = $this->getComponent($node);
    if (!$component) {
      return $node;
    }
    $line = $node->getTemplateLine();
    $print_nodes = [];
    $component_id = $component->getPluginId();
    $emoji = Utilities::emojiForString($component_id);
    if ($env->isDebug()) {
      $print_nodes[] = new PrintNode(new ConstantExpression(sprintf('<!-- %s Component start: %s -->', $emoji, $component_id), $line), $line);
    }
    $print_nodes[] = new PrintNode(new FunctionExpression(
      'attach_library',
      new Node([new ConstantExpression($component->getLibraryName(), $line)]),
      $line
    ), $line);
    $print_nodes[] = new PrintNode(new FunctionExpression(
      'sdc_additional_context',
      new Node([new ConstantExpression($component_id, $line)]),
      $line
    ), $line);
    $print_nodes[] = new PrintNode(new FunctionExpression(
      'sdc_validate_props',
      new Node([new ConstantExpression($component_id, $line)]),
      $line
    ), $line);
    foreach ($print_nodes as $index => $print_node) {
      $node->getNode('display_start')->setNode($index, $print_node);
    }
    if ($env->isDebug()) {
      $node->getNode('display_end')
        ->setNode(
          0,
          new PrintNode(new ConstantExpression(sprintf('<!-- %s Component end: %s -->', $emoji, $component_id), $line), $line)
        );
    }
    // Slots can be validated at compile time, we don't need to add nodes to
    // execute functions during display with the actual data.
    $this->validateSlots($component, $node->getNode('blocks'));
    return $node;
  }

  /**
   * Finds the SDC for the current module node.
   *
   * @param \Twig\Node\Node $node
   *   The node.
   *
   * @return \Drupal\sdc\Plugin\Component|null
   *   The component, if any.
   */
  protected function getComponent(Node $node): ?Component {
    $template_name = $node->getTemplateName();
    if (!preg_match('/^[a-z]([a-zA-Z0-9_-]*[a-zA-Z0-9])*:[a-z]([a-zA-Z0-9_-]*[a-zA-Z0-9])*$/', $template_name)) {
      return NULL;
    }
    $manager = \Drupal::service('plugin.manager.sdc');
    assert($manager instanceof ComponentPluginManager);
    try {
      return $manager->find($template_name);
    }
    catch (ComponentNotFoundException $e) {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPriority(): int {
    return 250;
  }

  /**
   * Performs a cheap validation of the slots in the template.
   *
   * It validates them against the JSON Schema provided in the component
   * definition file and massaged in the ComponentMetadata class. We don't use
   * the JSON Schema validator because we just want to validate required and
   * undeclared slots. This cheap validation lets us validate during runtime
   * even in production.
   *
   * @throws \Drupal\sdc\Exception\InvalidComponentException
   *   When the slots don't pass validation.
   */
  protected function validateSlots(Component $component, Node $node): void {
    $metadata = $component->getMetadata();
    $schema = $metadata->getSchemas()['slots'] ?? NULL;
    if (!$schema) {
      if ($metadata->shouldEnforceSchemas()) {
        throw new InvalidComponentException(sprintf('The component "%s" does not provide schema information. Schema definitions are mandatory for components declared in modules. For components declared in themes, schema definitions are only mandatory if the "enforce_sdc_schemas" key is set to "true" in the theme info file.', $component->getPluginId()));
      }
      return;
    }
    $ids_required = $schema['required'] ?? [];
    $ids_available = array_keys($schema['properties'] ?? []);
    $undocumented_ids = [];
    try {
      $it = $node->getIterator();
    }
    catch (\Exception $e) {
      return;
    }
    if ($it instanceof \SeekableIterator) {
      while ($it->valid()) {
        $provided_id = $it->key();
        if (!in_array($provided_id, $ids_available, TRUE)) {
          $undocumented_ids[] = $provided_id;
        }
        $it->next();
      }
    }
    $missing_required_ids = array_filter(
      $ids_required,
      static fn(string $required_id) => !$node->hasNode($required_id)
    );
    // Now build the error message.
    $error_messages = [];
    if (!empty($missing_required_ids)) {
      $error_messages[] = sprintf(
        'Some required slots are missing: %s.',
        implode(', ', $missing_required_ids)
      );
    }
    if (!empty($undocumented_ids)) {
      $error_messages[] = sprintf(
        'We found an unexpected slot that is not declared: [%s]. Please declare them in "%s.component.yml".',
        implode(', ', $undocumented_ids),
        $component->getMachineName()
      );
    }
    if (!empty($error_messages)) {
      $message = implode("\n", $error_messages);
      throw new InvalidComponentException($message);
    }
  }

}
