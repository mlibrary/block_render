<?php
/**
 * @file
 * Contains Drupal\block_render\Data\LibraryResponse.
 */

namespace Drupal\block_render\Data;

/**
 * The asset response data.
 */
class LibraryResponse implements LibraryResponseInterface {

  /**
   * Libraries.
   *
   * @var array
   */
  protected $libraries;

  /**
   * Create the Asset Response object.
   *
   * @param array $libraries
   *   An array of libraries.
   */
  public function __construct(array $libraries = array()) {
    $this->libraries = $libraries;
  }

  /**
   * Sets the asset libraries.
   *
   * @param array $libraries
   *   Array of Libraries.
   *
   * @return \Drupal\block_render\Data\AssetResponse
   *   Return the asset response object.
   */
  public function setLibraries(array $libraries) {
    $this->libraries = $libraries;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    return $this->libraries;
  }

}
