<?php
/**
 * @file
 * Contains Drupal\block_render\Plugin\rest\resource\BlockRenderResource.
 */

namespace Drupal\block_render\Plugin\rest\resource;

use Drupal\block\BlockInterface;
use Drupal\block_render\BlockBuilder;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * REST endpoint for rendered Blocks.
 *
 * @RestResource(
 *   id = "block_render",
 *   label = @Translation("Block Render"),
 *   uri_paths = {
 *     "canonical" = "/block-render/{block_id}"
 *   }
 * )
 */
class BlockRenderResource extends ResourceBase {

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
   * The block builder.
   *
   * @var \Drupal\block_render\BlockBuilder
   */
  protected $builder;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * The available serialization formats.
   *
   * @var array
   */
  protected $serializerFormats = array();

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountInterface $current_user,
    EntityManagerInterface $entity_manager,
    BlockBuilder $builder,
    TranslationInterface $translator,
    RequestStack $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->entityManager = $entity_manager;
    $this->currentUser = $current_user;
    $this->builder = $builder;
    $this->stringTranslation = $translator;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('current_user'),
      $container->get('entity.manager'),
      $container->get('block_render.block_builder'),
      $container->get('string_translation'),
      $container->get('request_stack')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns a rendered block entry for the specified block.
   *
   * @param NULL|string $block_id
   *   Reference to the block to render.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing the rendered block.
   */
  public function get($block_id = NULL) {
    $loaded = $this->getRequest()->get('loaded', array());
    $storage = $this->getEntityManager()->getStorage('block');

    // Deliver a single block.
    if ($block_id) {
      $block = $storage->load($block_id);

      if (!$block) {
        throw new NotFoundHttpException($this->t('Block with ID @id was not found', ['@id' => $block_id]));
      }

      if (!$block->getPlugin()->access($this->getCurrentUser())) {
        throw new AccessDeniedHttpException($this->t('Access Denied to Block with ID @id', ['@id' => $block_id]));
      }

      $config = $this->getRequest()->query->all();
      $block->getPlugin()->setConfiguration($config);

      $response = new ResourceResponse($this->getBuilder()->build($block, $loaded));
      $response->addCacheableDependency($block);

      return $response;
    }
    else {

      $block_ids = $this->getRequest()->get('blocks');

      // Deliver a list of blocks ids.
      if (!$block_ids) {
        $blocks = $storage->loadMultiple();

        $list = array();
        foreach ($blocks as $key => $block) {
          if (!$block->getPlugin()->access($this->getCurrentUser())) {
            unset($blocks[$key]);
            continue;
          }

          $list[] = [
            'id' => $block->id(),
            'label' => $block->label(),
            'theme' => $block->getTheme(),
          ];
        }

        $response = new ResourceResponse($list);

        foreach ($blocks as $block) {
          $response->addCacheableDependency($block);
        }

        return $response;
      }

      // Deliever multiple rendered blocks.
      $blocks = $storage->loadMultiple($block_ids);

      if (!$blocks) {
        throw new NotFoundHttpException($this->t('No Blocks found'));
      }

      $config = $this->getRequest()->query->all();
      foreach ($blocks as $key => $block) {

        if (!$block->getPlugin()->access($this->getCurrentUser())) {
          unset($blocks[$key]);
          continue;
        }

        if (!isset($config[$block->id()])) {
          continue;
        }

        $block->getPlugin()->setConfiguration($config[$block->id()]);
      }

      $response = new ResourceResponse($this->getBuilder()->buildMultiple($blocks, $loaded));

      foreach ($blocks as $block) {
        $response->addCacheableDependency($block);
      }

      return $response;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $collection = parent::routes();

    foreach ($this->getFormats() as $format) {
      $collection->get('block_render.GET.' . $format)->setDefault('block_id', NULL);
    }

    return $collection;
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
   * Gets the Builder service.
   *
   * @return \Drupal\block_render\BlockBuilder
   *   Renderer object.
   */
  public function getBuilder() {
    return $this->builder;
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

  /**
   * Gets the supported formats.
   *
   * @return array
   *   Supported Formats.
   */
  public function getFormats() {
    return $this->serializerFormats;
  }

}
