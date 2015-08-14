<?php
/**
 * @file
 * Contains Drupal\block_render\Data\AssetResponse.
 */

namespace Drupal\block_render\Data;

use Drupal\block_render\Data\LibraryResponseInterface;

/**
 * The asset response data.
 */
class AssetResponse implements AssetResponseInterface {

  /**
   * Libraries.
   *
   * @var \Drupal\block_render\Data\LibraryResponseInterface
   */
  protected $libraries;

  /**
   * Header Assets.
   *
   * @var array
   */
  protected $header;

  /**
   * Footer Assets.
   *
   * @var array
   */
  protected $footer;

  /**
   * Create the Asset Response object.
   *
   * @param \Drupal\block_render\Data\LibraryResponseInterface $libraries
   *   A library response object.
   * @param array $header
   *   Header Assets.
   * @param array $footer
   *   Footer Assets.
   */
  public function __construct(LibraryResponseInterface $libraries = NULL, array $header = array(), array $footer = array()) {
    $this->libraries = $libraries;
    $this->header = $header;
    $this->footer = $footer;
  }

  /**
   * Sets the asset libraries.
   *
   * @param \Drupal\block_render\Data\LibraryResponseInterface $libraries
   *   A library response object.
   *
   * @return \Drupal\block_render\Data\AssetResponse
   *   Return the asset response object.
   */
  public function setLibraries(LibraryResponseInterface $libraries) {
    $this->libraries = $libraries;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    return $this->libraries;
  }

  /**
   * Sets the header assets.
   *
   * @param array $header
   *   Array of Headers.
   *
   * @return \Drupal\block_render\Data\AssetResponse
   *   Return the asset response object.
   */
  public function setHeader(array $header) {
    $this->header = $header;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getHeader() {
    return $this->header;
  }

  /**
   * Sets the footer assets.
   *
   * @param array $footer
   *   Array of footer assets.
   *
   * @return \Drupal\block_render\Data\AssetResponse
   *   Return the asset response object.
   */
  public function setFooter(array $footer) {
    $this->footer = $footer;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFooter() {
    return $this->footer;
  }

}
