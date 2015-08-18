<?php
/**
 * @file
 * Contains Drupal\Tests\block_render\Unit\Content\RenderedContentTest.
 */

namespace Drupal\Tests\block_render\Unit\Content;

use Drupal\block_render\Content\RenderedContent;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the rendered content.
 *
 * @group block_render
 */
class RenderedContentTest extends UnitTestCase {

  /**
   * Tests the construct.
   */
  public function testRenderedContent() {

    $content = $this->getMockBuilder('Drupal\Component\Utility\SafeStringInterface')
      ->getMock();

    $rendered = new RenderedContent(['test' => $content], FALSE);

    $this->assertEquals($content, $rendered->getContent()['test']);
    $this->assertFalse($rendered->isSingle());

    $rendered = new RenderedContent(['test' => $content], TRUE);

    $this->assertEquals($content, $rendered->getContent()['test']);
    $this->assertTrue($rendered->isSingle());

    $rendered = new RenderedContent(['test' => $content, 'test2' => $content], TRUE);

    $this->assertEquals($content, $rendered->getContent()['test']);
    $this->assertEquals($content, $rendered->getContent()['test2']);
    $this->assertFalse($rendered->isSingle());
  }

  /**
   * Tests a failure of the rendered content object.
   */
  public function testRenderedContentFailure() {
    $this->setExpectedException('\PHPUnit_Framework_Error');

    new RenderedContent(['test' => 'string']);
  }

  /**
   * Tests setting a property on the class.
   */
  public function testSet() {
    $this->setExpectedException('\LogicException', 'You cannot set properties.');

    $content = new RenderedContent();
    $content->content = 'some value';
  }

  /**
   * Tests adding content.
   */
  public function testAddContent() {
    $content = $this->getMockBuilder('Drupal\Component\Utility\SafeStringInterface')
      ->getMock();

    $rendered = new RenderedContent();

    $rendered->addContent('test', $content);
    $rendered->addContent('test2', $content);

    $this->assertEquals($content, $rendered->getContent()['test']);
    $this->assertEquals($content, $rendered->getContent()['test2']);
  }

  /**
   * Tests adding content failure.
   */
  public function testAddContentFailure() {
    $this->setExpectedException('\PHPUnit_Framework_Error');

    $rendered = new RenderedContent();
    $rendered->addContent('test', 'string');
  }

}
