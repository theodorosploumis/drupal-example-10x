<?php

namespace Drupal\Tests\sdc\Functional;

use Drupal\sdc\ComponentNegotiator;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the component negotiator.
 *
 * @coversDefaultClass \Drupal\sdc\ComponentNegotiator
 * @group sdc
 */
class ComponentNegotiatorTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['sdc', 'sdc_test', 'sdc_test_replacements'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'sdc_theme_test_enforce_schema';

  /**
   * The plugin definitions.
   *
   * @var array
   */
  private array $definitions;

  /**
   * The component negotiator.
   *
   * @return \Drupal\sdc\ComponentNegotiator
   */
  private ComponentNegotiator $negotiator;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->negotiator = \Drupal::service(ComponentNegotiator::class);
    $this->definitions = sdc_manager()->getDefinitions();
  }

  /**
   * @covers ::negotiate
   */
  public function testNegotiate(): void {
    $data = [
      ['sdc_test:my-banner', 'sdc_test:my-banner'],
      ['sdc_theme_test:my-card', 'sdc_theme_test_enforce_schema:my-card'],
      [
        'sdc_test:my-button',
        'sdc_test_replacements:my-button',
      ],
      ['invalid:component', 'invalid:component'],
      ['invalid^component', 'invalid^component'],
      ['', ''],
    ];
    array_walk($data, function ($test_input) {
      [$requested_id, $expected_id] = $test_input;
      $negotiated_id = $this->negotiator->negotiate(
        $requested_id,
        $this->definitions
      );
      $this->assertSame($expected_id, $negotiated_id);
    });
  }

  /**
   * Tests rendering components with component replacement.
   *
   * @throws \Behat\Mink\Exception\ElementHtmlException
   */
  public function testRenderWithReplacements(): void {
    $assert_session = $this->assertSession();
    $build = [
      '#type' => 'inline_template',
      '#template' => "{{ include('sdc_test:my-button') }}",
      '#context' => ['text' => 'Like!', 'iconType' => 'like'],
    ];
    $encoded = base64_encode(serialize($build));
    $this->drupalGet('_sdc_array/' . $encoded);
    $assert_session->elementAttributeContains(
      'css',
      '#___sdc-wrapper button',
      'class',
      'sdc--my-button'
    );
    $assert_session->elementAttributeContains(
      'css',
      '#___sdc-wrapper button',
      'data-sdc-id',
      'sdc_test_replacements:my-button'
    );
    $assert_session->elementTextEquals(
      'css',
      '#___sdc-wrapper button .sdc-id',
      'sdc_test_replacements:my-button'
    );
    // Now test component replacement on themes.
    $build = [
      '#type' => 'inline_template',
      '#template' => "{{ include('sdc_theme_test:my-card') }}",
      '#context' => ['header' => 'Foo bar'],
    ];
    $encoded = base64_encode(serialize($build));
    $this->drupalGet('_sdc_array/' . $encoded);
    $assert_session->elementAttributeContains(
      'css',
      '#___sdc-wrapper .sdc',
      'class',
      'sdc--my-card'
    );
    $assert_session->elementAttributeContains(
      'css',
      '#___sdc-wrapper .sdc',
      'data-sdc-id',
      'sdc_theme_test_enforce_schema:my-card'
    );
  }

}
