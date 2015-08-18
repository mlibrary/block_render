<?php
/**
 * @file
 * Contains Drupal\block_render\Content\RenderedContent.
 */

namespace Drupal\block_render\Content;

use Drupal\Component\Utility\SafeStringInterface;

/**
 * Contains the rendered content.
 */
interface RenderedContentInterface extends \IteratorAggregate {

  /**
   * Gets the content.
   *
   * @return arrat
   *   Array of Drupal\Component\Utility\SafeStringInterface objects.
   */
  public function getContent();

  /**
   * Determines if this is a single item.
   *
   * @return bool
   *   Whether a single item should be returned.
   */
  public function isSingle();

}