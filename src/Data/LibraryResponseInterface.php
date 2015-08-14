<?php
/**
 * @file
 * Contains Drupal\block_render\Data\LibraryResponseInterface.
 */

namespace Drupal\block_render\Data;

/**
 * The asset response data.
 */
interface LibraryResponseInterface {

  /**
   * Returns the asset libraries.
   *
   * @return array
   *   Array of Libraries.
   */
  public function getLibraries();

}
