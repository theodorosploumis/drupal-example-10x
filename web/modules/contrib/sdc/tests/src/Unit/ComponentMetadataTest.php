<?php

namespace Drupal\Tests\sdc\Unit;

use Drupal\sdc\Component\ComponentMetadata;
use Drupal\sdc\Exception\InvalidComponentException;
use Drupal\Tests\UnitTestCaseTest;

/**
 * Unit tests for the component metadata class.
 *
 * @coversDefaultClass \Drupal\sdc\Component\ComponentMetadata
 * @group sdc
 */
class ComponentMetadataTest extends UnitTestCaseTest {

  /**
   * Tests that the correct data is returned for each property.
   *
   * @dataProvider dataProviderMetadata
   */
  public function testMetadata(array $metadata_info, array $expectations) {
    $metadata = new ComponentMetadata($metadata_info, 'foo/', FALSE);
    $this->assertSame($expectations['path'], $metadata->getPath());
    $this->assertSame($expectations['status'], $metadata->getStatus());
    $this->assertSame($expectations['thumbnail'], $metadata->getThumbnailPath());
    $this->assertEquals($expectations['schemas'], $metadata->getSchemas());
  }

  /**
   * Tests the correct checks when enforcing schemas or not.
   *
   * @dataProvider dataProviderMetadata
   */
  public function testMetadataEnforceSchema(array $metadata_info, array $expectations, bool $missing_schema) {
    if ($missing_schema) {
      $this->expectException(InvalidComponentException::class);
      $this->expectExceptionMessage('The component "' . $metadata_info['id'] . '" does not provide schema information. Schema definitions are mandatory for components declared in modules. For components declared in themes, schema definitions are only mandatory if the "enforce_sdc_schemas" key is set to "true" in the theme info file.');
      new ComponentMetadata($metadata_info, 'foo/', TRUE);
    }
    else {
      new ComponentMetadata($metadata_info, 'foo/', TRUE);
      $this->assertTrue(TRUE);
    }
  }

  /**
   * Data provider for the test testMetadataEnforceSchema.
   *
   * @return array[]
   *   The batches of data.
   */
  public function dataProviderMetadata(): array {
    return [
      [
        [
          'path' => 'foo/bar/component-name',
          'id' => 'sdc:component-name',
          'machineName' => 'component-name',
          'name' => 'Component Name',
          'libraryDependencies' => ['core/drupal'],
          'group' => 'my-group',
        ],
        [
          'path' => 'bar/component-name',
          'status' => 'READY',
          'thumbnail' => '',
          'schemas' => NULL,
        ],
        TRUE,
      ],
      [
        [
          '$schema' => 'https://git.drupalcode.org/project/sdc/-/raw/1.x/src/metadata.schema.json',
          'id' => 'sdc:my-button',
          'machineName' => 'my-button',
          'path' => 'foo/my-other/path',
          'name' => 'Button',
          'description' => 'JavaScript enhanced button that tracks the number of times a user clicked it.',
          'libraryDependencies' => ['core/once'],
          'schemas' => [
            'props' => [
              'type' => 'object',
              'required' => ['text'],
              'properties' => [
                'text' => [
                  'type' => 'string',
                  'title' => 'Title',
                  'description' => 'The title for the button',
                  'minLength' => 2,
                  'examples' => ['Press', 'Submit now'],
                ],
                'iconType' => [
                  'type' => 'string',
                  'title' => 'Icon Type',
                  'enum' => [
                    'power',
                    'like',
                    'external',
                  ],
                ],
              ],
            ],
          ],
        ],
        [
          'path' => 'my-other/path',
          'status' => 'READY',
          'thumbnail' => '',
          'additionalProperties' => FALSE,
          'schemas' => [
            'props' => [
              'type' => 'object',
              'required' => ['text'],
              'additionalProperties' => FALSE,
              'properties' => [
                'text' => [
                  'type' => ['string'],
                  'title' => 'Title',
                  'description' => 'The title for the button',
                  'minLength' => 2,
                  'examples' => ['Press', 'Submit now'],
                ],
                'iconType' => [
                  'type' => ['string'],
                  'title' => 'Icon Type',
                  'enum' => [
                    'power',
                    'like',
                    'external',
                  ],
                ],
              ],
            ],
            'slots' => [
              'type' => 'object',
              'additionalProperties' => FALSE,
              'required' => [],
              'properties' => [],
            ],
          ],
        ],
        FALSE,
      ],
    ];
  }

}
