<?php

namespace Drupal\Tests\sdc\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\sdc\ComponentPluginManager;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\WebAssert;

/**
 * Tests the correct rendering of components.
 *
 * @group sdc
 */
class ComponentRenderTest extends BrowserTestBase {

  use StringTranslationTrait;

  /**
   * Modules to enable.
   *
   * The test runner will merge the $modules lists from this class, the class
   * it extends, and so on up the class hierarchy. It is not necessary to
   * include modules in your list that a parent class has already declared.
   *
   * @var string[]
   *
   * @see \Drupal\Tests\BrowserTestBase::installDrupal()
   */
  protected static $modules = ['sdc', 'sdc_test'];

  /**
   * The theme to install as the default for testing.
   *
   * Defaults to the install profile's default theme, if it specifies any.
   *
   * @var string
   */
  protected $defaultTheme = 'sdc_theme_test';

  /**
   * Test that components render correctly.
   *
   * @throws \Behat\Mink\Exception\ElementTextException
   * @throws \Behat\Mink\Exception\ElementHtmlException
   */
  public function testRender(): void {
    $assert_session = $this->assertSession();
    $this->checkIncludeDefaultContent($assert_session);
    $this->checkIncludeDataMapping($assert_session);
    $this->checkEmbedWithNested($assert_session);
    $this->checkPropValidation($assert_session);
    $this->checkNonExistingComponent($assert_session);
  }

  /**
   * Check using a component with an include and default context.
   *
   * @param \Drupal\Tests\WebAssert $assert_session
   *   The session.
   *
   * @throws \Behat\Mink\Exception\ElementTextException
   */
  protected function checkIncludeDefaultContent(WebAssert $assert_session): void {
    $build = [
      '#type' => 'inline_template',
      '#template' => "{% embed('sdc_theme_test_base:my-card-no-schema') %}{% block card_body %}Foo bar{% endblock %}{% endembed %}",
    ];
    $encoded = base64_encode(serialize($build));
    $this->drupalGet('_sdc_array/' . $encoded);
    $assert_session->elementTextContains(
      'css',
      '#___sdc-wrapper .sdc .sdc--my-card-no-schema__body',
      'Foo bar'
    );
  }

  /**
   * Check using a component with an include and no default context.
   *
   * This covers passing a render array to a 'string' prop, and mapping the
   * prop to a context variable.
   *
   * @param \Drupal\Tests\WebAssert $assert_session
   *   The session.
   *
   * @throws \Behat\Mink\Exception\ElementTextException
   */
  protected function checkIncludeDataMapping(WebAssert $assert_session): void {
    $content = [
      'label' => [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => 'Another button ç',
      ],
    ];
    $build = [
      '#type' => 'inline_template',
      '#context' => ['content' => $content],
      '#template' => "{{ include('sdc_test:my-button', { text: content.label, iconType: 'external' }, with_context = false) }}",
    ];
    $encoded = base64_encode(serialize($build));
    $this->drupalGet('_sdc_array/' . $encoded);
    $assert_session->elementTextContains(
      'css',
      '#___sdc-wrapper button',
      'Another button ç'
    );
  }

  /**
   * Render a card with slots that include a CTA component.
   *
   * @param \Drupal\Tests\WebAssert $assert_session
   *   The session.
   *
   * @throws \Behat\Mink\Exception\ElementHtmlException
   * @throws \Behat\Mink\Exception\ElementTextException
   */
  protected function checkEmbedWithNested(WebAssert $assert_session): void {
    $content = [
      'heading' => [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => 'Just a link',
      ],
    ];
    $build = [
      '#type' => 'sdc',
      '#component' => 'sdc_theme_test:my-card',
      '#context' => ['header' => 'Card header', 'content' => $content],
      '#slots' => [
        'card_body' => "This is a card with a CTA {{ include('sdc_test:my-cta', { text: content.heading, href: 'https://www.example.org', target: '_blank' }, with_context = false) }}",
      ],
      '#trusted_slots' => TRUE,
    ];
    $encoded = base64_encode(serialize($build));
    $this->drupalGet('_sdc_array/' . $encoded);
    $assert_session->elementTextContains(
      'css',
      '#___sdc-wrapper .sdc--my-card h2.sdc--my-card__header',
      'Card header'
    );
    $assert_session->elementTextContains(
      'css',
      '#___sdc-wrapper .sdc--my-card .sdc--my-card__body',
      'This is a card with a CTA'
    );
    $assert_session->elementTextContains(
      'css',
      '#___sdc-wrapper .sdc--my-card .sdc--my-card__body a.sdc--my-cta',
      'Just a link'
    );
    $assert_session->elementAttributeContains(
      'css',
      '#___sdc-wrapper .sdc--my-card .sdc--my-card__body a.sdc--my-cta',
      'href',
      'https://www.example.org'
    );
    $assert_session->elementAttributeContains(
      'css',
      '#___sdc-wrapper .sdc--my-card .sdc--my-card__body a.sdc--my-cta',
      'target',
      '_blank'
    );
    // Now render a component and assert it contains the debug comments.
    $build = [
      '#type' => 'sdc',
      '#component' => 'sdc_test:my-banner',
      '#context' => [
        'heading' => $this->t('I am a banner'),
        'ctaText' => $this->t('Click me, please'),
        'ctaHref' => 'https://www.example.org',
        'ctaTarget' => '',
      ],
      '#slots' => [
        'banner_body' => $this->t('This is the contents of the banner body.'),
      ],
    ];
    $encoded = base64_encode(serialize($build));
    $this->drupalGet('_sdc_array/' . $encoded);
    $html_contents = $this->getSession()->getPage()->getContent();
    $this->assertStringContainsString('/sdc_test/components/my-banner/my-banner.css', $html_contents);
    $this->assertStringContainsString('/sdc_test/components/my-cta/my-cta.css', $html_contents);
    $this->assertStringContainsString('/sdc_test/components/my-cta/my-cta.js', $html_contents);
  }

  /**
   * Ensures the schema violations are reported properly.
   *
   * @param \Drupal\Tests\WebAssert $assert_session
   *   The session.
   */
  protected function checkPropValidation(WebAssert $assert_session): void {
    // 1. Violates the minLength for the text property.
    $content = ['label' => '1'];
    $build = [
      '#type' => 'inline_template',
      '#context' => ['content' => $content],
      '#template' => "{{ include('sdc_test:my-button', { text: content.label, iconType: 'external' }, with_context = false) }}",
    ];
    $encoded = base64_encode(serialize($build));
    $this->drupalGet('_sdc_array/' . $encoded);
    $this->assertSame(500, $this->getSession()->getStatusCode());
    // 2. Violates the required header property.
    $build = [
      '#type' => 'inline_template',
      '#context' => [],
      '#template' => "{{ include('sdc_theme_test:my-card', with_context = false) }}",
    ];
    $encoded = base64_encode(serialize($build));
    $this->drupalGet('_sdc_array/' . $encoded);
    $this->assertSame(500, $this->getSession()->getStatusCode());
  }

  /**
   * Ensures that including an invalid component creates an error.
   *
   * @param \Drupal\Tests\WebAssert $assert_session
   *   The session.
   */
  protected function checkNonExistingComponent(WebAssert $assert_session): void {
    $build = [
      '#type' => 'inline_template',
      '#context' => [],
      '#template' => "{{ include('sdc_test:INVALID', with_context = false) }}",
    ];
    $encoded = base64_encode(serialize($build));
    $this->drupalGet('_sdc_array/' . $encoded);
    $this->assertSame(500, $this->getSession()->getStatusCode());
  }

  /**
   * Ensures some key aspects of the plugin definition are correctly computed.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function testPluginDefinition(): void {
    $plugin_manager = \Drupal::service('plugin.manager.sdc');
    assert($plugin_manager instanceof ComponentPluginManager);
    $definition = $plugin_manager->getDefinition('sdc_test:my-banner');
    $this->assertSame('my-banner', $definition['machineName']);
    $this->assertStringEndsWith('sdc/tests/modules/sdc_test/components/my-banner', $definition['path']);
    $this->assertEquals(['core/drupal'], $definition['library']['dependencies']);
    $this->assertNotEmpty($definition['library']['css']['component']);
    $this->assertSame('my-banner.twig', $definition['template']);
    $this->assertNotEmpty($definition['documentation']);
  }

}
