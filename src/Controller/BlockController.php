<?php
/**
 * @file
 * Contains \Drupal\block_render\Controller\Block.
 */

namespace Drupal\block_render\Controller;

use Drupal\block\BlockInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Block Controllers.
 */
class BlockController extends ControllerBase {

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Constructor to add the dependencies.
   */
  public function __construct(RequestStack $request) {
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('request_stack'));
  }

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
    if (!$block->getPlugin()->access($this->currentUser())) {
      throw new AccessDeniedHttpException($this->t('Access Denied to Block with ID @id', ['@id' => $block->id()]));
    }

    // Add the configuration to the block.
    $config = $this->getRequest()->query->all();
    $block->getPlugin()->setConfiguration($config);

    // Build the block.
    $build = $this->entityManager()->getViewBuilder('block')->view($block);

    // Add the query arguments to the cache contexts.
    $contexts = isset($build['#cache']['contexts']) ? $build['#cache']['contexts'] : array();
    $build['#cache']['contexts'] = Cache::mergeContexts(['url.query_args'], $contexts);

    return $build;
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

  /**
   * Gets the current request.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   Request Object.
   */
  public function getRequest() {
    return $this->request->getCurrentRequest();
  }

}
