<?php
/**
 * @file
 * Drupal\Tests\block_render\Unit\Plugin\rest\resource\BlockRenderResource.
 */

namespace Drupal\Tests\block_render\Unit\Plugin\rest\resource;

use Drupal\block_render\Plugin\rest\resource\BlockRenderMultipleResource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Tests REST endpoint for rendered Blocks.
 *
 * @group block_render
 *
 * @covers Drupal\block_render\Plugin\rest\resource\BlockRenderResourceBase
 */
class BlockRenderMultipleResourceTest extends BlockRenderResourceBase {

  /**
   * Test Response to GET requests.
   */
  public function testGet() {
    $configuration = array();
    $plugin_id = $this->randomMachineName();
    $plugin_definition = array();
    $serializer_formats = ['test'];
    $logger = $this->getLogger();
    $current_user = $this->getCurrentUser();
    $translator = $this->getStringTranslationStub();
    $builder = $this->getBuilder();

    $storage = $this->getStorage();
    $storage->expects($this->once())
      ->method('loadMultiple')
      ->will($this->returnValue(array()));

    $entity_manager = $this->getEntityManager();
    $entity_manager->expects($this->once())
      ->method('getStorage')
      ->with('block')
      ->will($this->returnValue($storage));

    $stack = new RequestStack();
    $stack->push(new Request());

    $resource = new BlockRenderMultipleResource(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $serializer_formats,
      $logger,
      $current_user,
      $entity_manager,
      $builder,
      $translator,
      $stack
    );

    $response = $resource->get();
    $content = $response->getResponseData();

    $this->assertInternalType('array', $content);
    $this->assertEmpty($content);
  }

  /**
   * Tests getting multiple blocks.
   */
  public function testGetMultiple() {
    $configuration = array();
    $plugin_id = $this->randomMachineName();
    $plugin_definition = array();
    $serializer_formats = ['test'];
    $logger = $this->getLogger();
    $current_user = $this->getCurrentUser();
    $translator = $this->getStringTranslationStub();
    $builder = $this->getBuilder();

    $block_id = $this->randomMachineName();
    $block = $this->getMockBuilder('Drupal\block\BlockInterface')
      ->getMock();

    $storage = $this->getStorage();
    $storage->expects($this->once())
      ->method('loadMultiple')
      ->with([$block_id])
      ->will($this->returnValue([$block_id => $block]));

    $entity_manager = $this->getEntityManager();
    $entity_manager->expects($this->once())
      ->method('getStorage')
      ->with('block')
      ->will($this->returnValue($storage));

    $stack = new RequestStack();
    $stack->push(new Request());

    $resource = new BlockRenderMultipleResource(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $serializer_formats,
      $logger,
      $current_user,
      $entity_manager,
      $builder,
      $translator,
      $stack
    );

    $response = $resource->getMultiple([$block_id]);
    $content = $response->getResponseData();

    $this->assertInternalType('array', $content);
    $this->assertEmpty($content);
  }

  // @TODO Tests for the other methods.

}
