<?php
/**
 * @file
 * Contains Drupal\block_render\Plugin\rest\resource\BlockRenderResource.
 */

namespace Drupal\block_render\Plugin\rest\resource;

use Drupal\block\BlockInterface;
use Drupal\block_render\BlockBuilder;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    EntityManagerInterface $entity_manager,
    BlockBuilder $builder,
    TranslationInterface $translator,
    RequestStack $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->entityManager = $entity_manager;
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

      return new ResourceResponse($this->getBuilder()->build($block, $loaded));
    }
    else {

      $block_ids = $this->getRequest()->get('blocks');

      // Deliver a list of blocks ids.
      if (!$block_ids) {
        $blocks = $storage->loadMultiple();

        $list = array();
        foreach ($blocks as $block) {
          $list[] = [
            'id' => $block->id(),
            'label' => $block->label(),
            'theme' => $block->getTheme(),
          ];
        }

        return new ResourceResponse($list);
      }

      // Deliever multiple rendered blocks.
      $blocks = $storage->loadMultiple($block_ids);

      if (!$blocks) {
        throw new NotFoundHttpException($this->t('No Blocks found'));
      }

      return new ResourceResponse($this->getBuilder()->buildMultiple($blocks, $loaded));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $collection = parent::routes();
    $collection->get('block_render.GET.json')->setDefault('block_id', NULL);
    return $collection;
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

}
