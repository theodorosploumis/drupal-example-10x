<?php

/**
 * @file
 * Enum for supported extension types.
 */

namespace Drupal\sdc;

/**
 * Enum for supported extension types.
 */
enum ExtensionType: string {
  case Module = 'module';
  case Theme = 'theme';
}
