<?php
/**
 * @file
 * Contains Drupal\block_render\Library\Library.
 */

namespace Drupal\block_render\Library;

/**
 * Single Library.
 */
class Library implements LibraryInterface {

  /**
   * Name of the library.
   *
   * @var string
   */
  protected $name;

  /**
   * Version number string.
   *
   * @var string
   */
  protected $version;

  /**
   * Construct the Library.
   *
   * @param string $name
   *   Name of the library.
   * @param string $version
   *   A version number string.
   */
  public function __construct($name, $version = '') {
    $this->name = $name;
    $this->version = $version;
  }

  /**
   * Sets the Name.
   *
   * @param string $name
   *   Name of the library.
   *
   * @return \Drupal\block_render\Library\Libarary
   *   Current library object.
   */
  public function setName($name) {
    $this->name = $name;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Sets the Name.
   *
   * @param string $version
   *   Name of the version.
   *
   * @return \Drupal\block_render\Library\Libarary
   *   Current library object.
   */
  public function setVersion($version) {
    $this->version = $version;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getVersion() {
    return $this->version;
  }

}
