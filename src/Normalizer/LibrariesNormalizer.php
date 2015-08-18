<?php
/**
 * @file
 * Contains Drupal\block_render\Normalizer\LibrariesNormalizer.
 */

namespace Drupal\block_render\Normalizer;

use Drupal\serialization\Normalizer\NormalizerBase;

/**
 * Class to Normalize the Libraries.
 */
class LibrariesNormalizer extends NormalizerBase {

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = ['Drupal\block_render\Libraries\LibrariesInterface'];

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = array()) {
    $libraries = array();
    foreach ($object as $library) {
      $libraries[$library->getName()] = $library->getVersion();
    }

    return $libraries;
  }

}
