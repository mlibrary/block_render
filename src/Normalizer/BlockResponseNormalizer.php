<?php
/**
 * @file
 * Contains Drupal\block_render\Normalizer\BlockResponseNormalizer.
 */

namespace Drupal\block_render\Normalizer;

use Drupal\serialization\Normalizer\NormalizerBase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class to Normalize the Libraries.
 */
class BlockResponseNormalizer extends NormalizerBase {

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = ['Drupal\block_render\Response\BlockResponseInterface'];

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = array()) {
    return [
      'dependencies' => $this->serializer->normalize($object->getAssets()->getLibraries(), $format, $context),
      'assets' => [
        'header' => $object->getAssets()->getHeader(),
        'footer' => $object->getAssets()->getFooter(),
      ],
      'content' => $this->serializer->normalize($object->getContent(), $format, $context),
    ];
  }

}
