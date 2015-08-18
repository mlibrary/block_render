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
class RenderedContentNormalizer extends NormalizerBase {

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = ['Drupal\block_render\Content\RenderedContentInterface'];

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = array()) {
    $result = array();
    foreach ($object as $id => $content) {
      $result[$id] = (string) $content;
    }

    if ($object->isSingle()) {
      $result = reset($result);
    }

    return $result;
  }

}
