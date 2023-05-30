<?php

namespace Drupal\Tests\sdc\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\sdc\Utilities;

/**
 * @coversDefaultClass \Drupal\sdc\Utilities
 * @group sdc
 */
class UtilitiesTest extends KernelTestBase {

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
  protected static $modules = ['sdc'];

  /**
   * @covers ::makePathRelative
   */
  public function testMakePathRelative(): void {
    // We cannot use the data provider because the container is not ready in the
    // data provider.
    $app_root = \Drupal::root();
    $test_data = [
      ['', '', './'],
      [
        "$app_root/foo/bar/baz",
        "$app_root/foo/lorem/ipsum/dolor",
        '../../../../foo/bar/baz',
      ],
      [
        "$app_root/foo/bar/baz",
        "$app_root/foo/bar/baz",
        '../../../foo/bar/baz',
      ],
      [
        "$app_root/foo/bar/baz",
        "foo/lorem/ipsum/dolor",
        '../../../../foo/bar/baz',
      ],
      [
        "foo/bar/baz",
        "$app_root/foo/lorem/ipsum/dolor",
        '../../../../foo/bar/baz',
      ],
      [
        "foo/bar/baz",
        "foo/lorem/ipsum/dolor",
        '../../../../foo/bar/baz',
      ],
    ];
    foreach ($test_data as $test_datum) {
      [$full_path, $base, $expected] = $test_datum;
      $this->assertSame(
        $expected,
        Utilities::makePathRelative($full_path, $base)
      );
    }
  }

}
