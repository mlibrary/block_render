<?php
/**
 * @file
 * Contains Drupal\block_render\Content\RenderedContent.
 */

namespace Drupal\block_render\Content;

use Drupal\block_render\Immutable;
use Drupal\Component\Utility\SafeStringInterface;

/**
 * Contains the rendered content.
 */
final class RenderedContent extends Immutable implements RenderedContentInterface {

  /**
   * Rendered Content array.
   *
   * @var array
   */
  protected $content;

  /**
   * Single.
   *
   * @var bool
   */
  protected $single;

  /**
   * Sets the initial content.
   *
   * @param array $content
   *   Array of Drupal\Component\Utility\SafeStringInterface objects.
   * @param bool $single
   *   Indicator if a single item should be returned.
   */
  public function __construct(array $content = array(), $single = FALSE) {
    $this->content = array();
    $this->single = $single;

    foreach ($content as $id => $item) {
      $this->addContent($id, $item);
    }
  }

  /**
   * Sets the content.
   *
   * @param string $id
   *   Identifier of the content.
   * @param \Drupal\Component\Utility\SafeStringInterface $safe_string
   *   A safe string of the rendered content.
   *
   * @return \Drupal\block_render\Cotnent\RenderedContent
   *   Rendered Content object.
   */
  public function addContent($id, SafeStringInterface $safe_string) {
    $this->content[$id] = $safe_string;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return $this->content;
  }

  /**
   * {@inheritdoc}
   */
  public function isSingle() {
    return ($this->single && count($this->content) === 1) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    return new \ArrayIterator($this->content);
  }

}
