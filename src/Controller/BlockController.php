<?php
/**
 * @file
 * Contains \Drupal\block_render\Controller\Block.
 */

namespace Drupal\block_render\Controller;

use Drupal\block\BlockInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Block Controllers.
 */
class BlockController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Constructor to add the dependencies.
   */
  public function __construct(
    AccountInterface $current_user,
    EntityManagerInterface $entity_manager,
    TranslationInterface $translator,
    RequestStack $request) {

    $this->currentUser = $current_user;
    $this->entityManager = $entity_manager;
    $this->stringTranslation = $translator;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('entity.manager'),
      $container->get('string_translation'),
      $container->get('request_stack')
    );
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
    if (!$block->getPlugin()->access($this->getCurrentUser())) {
      throw new AccessDeniedHttpException($this->t('Access Denied to Block with ID @id', ['@id' => $block->id()]));
    }

    $config = $this->getRequest()->query->all();
    $block->getPlugin()->setConfiguration($config);

    return $this->getEntityManager()->getViewBuilder('block')->view($block);
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
   * Gets the Current User session.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   Current User session object.
   */
  public function getCurrentUser() {
    return $this->currentUser;
  }

  /**
   * Gets the Entity Manager object.
   *
   * @return \Drupal\Core\Entity\EntityManagerInterface
   *   Entity Manager object.
   */
  public function getEntityManager() {
    return $this->entityManager;
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
