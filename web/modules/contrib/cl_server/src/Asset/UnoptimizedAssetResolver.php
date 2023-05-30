<?php

namespace Drupal\cl_server\Asset;

use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\cl_server\Util;
use Drupal\Core\Asset\AttachedAssetsInterface;
use Drupal\Core\Language\LanguageInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The asset resolver that forces skipping optimizations.
 */
class UnoptimizedAssetResolver implements AssetResolverInterface {

  /**
   * The decorated resolver.
   *
   * @var \Drupal\Core\Asset\AssetResolverInterface
   */
  private $resolver;

  /**
   * Weather or not to skip optimization.
   *
   * @var bool
   */
  private bool $skipOptimization = FALSE;

  /**
   * Creates a new asset resolver.
   *
   * @param \Drupal\Core\Asset\AssetResolverInterface $resolver
   *   The actual resolver.
   */
  public function __construct(AssetResolverInterface $resolver, RequestStack $request_stack) {
    $this->resolver = $resolver;
    $request = $request_stack->getCurrentRequest();
    $this->skipOptimization = $request && Util::isRenderController($request);
  }

  /**
   * @inheritDoc
   */
  public function getCssAssets(AttachedAssetsInterface $assets, $optimize, LanguageInterface $language = NULL) {
    return $this->resolver->getCssAssets(
      $assets,
      $this->skipOptimization ? FALSE : $optimize,
      $language
    );
  }

  /**
   * @inheritDoc
   */
  public function getJsAssets(AttachedAssetsInterface $assets, $optimize, LanguageInterface $language = NULL) {
    return $this->resolver->getJsAssets(
      $assets,
      $this->skipOptimization ? FALSE : $optimize,
      $language
    );
  }

}
