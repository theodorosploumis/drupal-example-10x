<?php

namespace Drupal\sdc;

/**
 * Shared utilities for SDC.
 */
class Utilities {

  /**
   * Takes two absolute paths and makes one relative to the other.
   *
   * @param string $full_path
   *   The full path.
   * @param string $base
   *   The base to make relative to.
   *
   * @return string
   *   The relative path.
   */
  public static function makePathRelative(string $full_path, string $base): string {
    $app_root = \Drupal::root();
    $base_from_root = str_starts_with($base, $app_root)
      ? substr($base, strlen($app_root) + 1)
      : $base;
    $num_dots = empty($base_from_root)
      ? 0
      : count(explode(DIRECTORY_SEPARATOR, $base_from_root));
    $dots = implode(DIRECTORY_SEPARATOR, array_fill(0, $num_dots, '..'));
    $full_path_from_root = str_starts_with($full_path, $app_root)
      ? substr($full_path, strlen($app_root) + 1)
      : $full_path;
    return sprintf(
      '%s%s%s',
      $dots ?: '.',
      DIRECTORY_SEPARATOR,
      $full_path_from_root,
    );
  }

  /**
   * Chooses an emoji representative for the input string.
   *
   * @param string $input
   *   The input string.
   *
   * @return string
   *   The emoji code.
   */
  public static function emojiForString(string $input): string {
    // Compute a cheap and reproducible float between 0 and 1 for based on the
    // component ID.
    $max_length = 40;
    $input = strtolower($input);
    $input = strtr($input, '-_:', '000');
    $input = substr($input, 0, $max_length);
    $chars = str_split($input);
    $chars = count($chars) < 20
      ? array_pad($chars, 20, '0')
      : $chars;
    $sum = array_reduce($chars, static fn(int $total, string $char) => $total + ord($char), 0);
    $num = $sum / 4880;

    // Compute an int between 128512 and 128512, which is the sequential emoji
    // range we are interested in.
    $html_entity = floor(129338 + $num * (129431 - 129338));
    $emoji = mb_convert_encoding("&#$html_entity;", 'UTF-8', 'HTML-ENTITIES');
    return is_string($emoji) ? $emoji : '';
  }

}
