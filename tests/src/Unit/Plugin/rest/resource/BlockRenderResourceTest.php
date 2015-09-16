<?php
/**
 * @file
 * Drupal\Tests\block_render\Unit\Plugin\rest\resource\BlockRenderResource.
 */

namespace Drupal\Tests\block_render\Unit\Plugin\rest\resource;

use Drupal\block_render\Plugin\rest\resource\BlockRenderResource;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Tests REST endpoint for rendered Blocks.
 *
 * @group block_render
 */
class BlockRenderResourceTest extends UnitTestCase {


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

    $stack = new RequestStack();
    $stack->push(new Request());

    $plugin = $this->getMockBuilder('Drupal\Core\Block\BlockPluginInterface')
      ->getMock();

    $plugin->expects($this->once())
      ->method('access')
      ->will($this->returnValue(TRUE));

    $block_id = $this->randomMachineName();

    $block = $this->getMockBuilder('Drupal\block\BlockInterface')
      ->getMock();

    $block->expects($this->exactly(2))
      ->method('getPlugin')
      ->will($this->returnValue($plugin));

    $storage = $this->getMockBuilder('Drupal\Core\Entity\EntityStorageInterface')
      ->getMock();

    $storage->expects($this->once())
      ->method('load')
      ->will($this->returnValue($block));

    $entity_manager = $this->getMockBuilder('Drupal\Core\Entity\EntityManagerInterface')
      ->getMock();

    $entity_manager->expects($this->once())
      ->method('getStorage')
      ->will($this->returnValue($storage));

    $builder = $this->getMockBuilder('Drupal\block_render\BlockBuilderInterface')
      ->getMock();

    $builder->expects($this->once())
      ->method('build')
      ->will($this->returnValue($block_id));

    $resource = new BlockRenderResource(
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

    $response = $resource->get($block_id);
    $content = $response->getResponseData();

    $this->assertInternalType('string', $content);
    $this->assertEquals($block_id, $content);

    // @TODO write tests for other conditions and failures.
  }

  /**
   * Gets the logger.
   */
  public function getLogger() {
    return $this->getMockBuilder('Psr\Log\LoggerInterface')
      ->getMock();
  }

  /**
   * Gets the logger.
   */
  public function getCurrentUser() {
    return $this->getMockBuilder('Drupal\Core\Session\AccountInterface')
      ->getMock();
  }

}
