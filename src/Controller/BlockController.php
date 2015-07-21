<?php
/**
 * @file
 * Contains Drupal\block_render\Controller\Block
 */

namespace Drupal\block_render\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\block\BlockInterface;

class BlockController extends ControllerBase {

  public function render(BlockInterface $block) {
    return $block->getPlugin()->build();
  }

}
