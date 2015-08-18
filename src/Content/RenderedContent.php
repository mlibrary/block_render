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
   * @param int $single
   *   Indicator if a single item should be returned.
   */
  public function __construct(array $content = array(), $single = FALSE) {
    foreach ($content as $id => $item) {
      $this->addContent($id, $item);
    }
    $this->single = $single;
  }

  /**
   * Prevent properties from being set.
   *
   * @param string $name
   *   Property name.
   * @param mixed $value
   *   Value of the property.
   */
  public function __set($name, $value) {
    throw new \LogicException('You cannot set properties.');
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
