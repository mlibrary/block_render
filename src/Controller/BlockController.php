<?php
/**
 * @file
 * Contains \Drupal\block_render\Controller\Block.
 */

namespace Drupal\block_render\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\block\BlockInterface;

/**
 * Block Controllers.
 */
class BlockController extends ControllerBase {

  /**
   * Render Controller.
   *
   * @param \Drupal\block\BlockInterface $block
   *   The block to render.
   *
   * @return array
   *   Build array of the requested block.
   */
  public function render(BlockInterface $block) {
    return $this->entityManager()->getViewBuilder('block')->view($block);
  }

  /**
   * Render Title.
   *
   * @param \Drupal\block\BlockInterface $block
   *   The block to get the title from.
   *
   * @return string
   *   Title of the page.
   */
  public function renderTitle(BlockInterface $block) {
    return $block->label();
  }

}
