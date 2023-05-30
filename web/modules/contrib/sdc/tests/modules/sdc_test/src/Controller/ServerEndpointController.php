<?php

namespace Drupal\sdc_test\Controller;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * An endpoint to serve a component during tests.
 */
class ServerEndpointController {

  /**
   * Render an arbitrary render array.
   */
  public function renderArray(string $encoded): array {
    \Drupal::service('page_cache_kill_switch')->trigger();
    $decoded = base64_decode($encoded, TRUE);
    $render_array = unserialize(
      $decoded,
      ['allowed_classes' => [\stdClass::class, TranslatableMarkup::class]]
    );
    return [
      '#type' => 'container',
      '#cache' => ['max-age' => 0],
      // Magic wrapper ID to pull the HTML from.
      '#attributes' => ['id' => '___sdc-wrapper'],
      'component' => $render_array,
    ];
  }

}
