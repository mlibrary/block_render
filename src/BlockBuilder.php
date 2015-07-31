<?php
/**
 * @file
 * Contains Drupal\block_render\BlockBuiler.
 */

namespace Drupal\block_render;

use Drupal\Core\Asset\AttachedAssets;
use Drupal\Core\Asset\AttachedAssetsInterface;
use Drupal\Core\Asset\AssetCollectionRendererInterface;
use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\Core\Asset\LibraryDependencyResolverInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\block\BlockInterface;

/**
 * Build a block from a given id.
 */
class BlockBuilder {

  /**
   * The asset resolver.
   *
   * @var \Drupal\Core\Asset\AssetResolverInterface
   */
  protected $assetResolver;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Library Discovery.
   *
   * @var \Drupal\Core\Asset\LibraryDiscoveryInterface
   */
  protected $libraryDiscovery;

  /**
   * Library Dependency Resolver.
   *
   * @var \Drupal\Core\Asset\LibraryDependencyResolverInterface
   */
  protected $libraryDependencyResolver;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterfac
   */
  protected $renderer;

  /**
   * The CSS asset collection renderer service.
   *
   * @var \Drupal\Core\Asset\AssetCollectionRendererInterface
   */
  protected $cssRenderer;

  /**
   * The JS asset collection renderer service.
   *
   * @var \Drupal\Core\Asset\AssetCollectionRendererInterface
   */
  protected $jsRenderer;

  /**
   * Construct the object with the necessary dependencies.
   *
   * @param \Drupal\Core\Asset\AssetResolverInterface $asset_resolver
   *   The asset Resolver to resolve the assets.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The Renderer.
   */
  public function __construct(
    AssetResolverInterface $asset_resolver,
    ConfigFactoryInterface $config,
    LibraryDiscoveryInterface $library_discovery,
    LibraryDependencyResolverInterface $library_dependency_resolver,
    RendererInterface $renderer,
    AssetCollectionRendererInterface $css_renderer,
    AssetCollectionRendererInterface $js_renderer) {

    $this->assetResolver = $asset_resolver;
    $this->config = $config;
    $this->libraryDiscovery = $library_discovery;
    $this->libraryDependencyResolver = $library_dependency_resolver;
    $this->renderer = $renderer;
    $this->cssRenderer = $css_renderer;
    $this->jsRenderer = $js_renderer;
  }

  /**
   * Builds multiple blocks.
   *
   * @param \Drupal\block\BlockInterface $block
   *   Block to render.
   * @param array $loaded
   *   Libraries that have already been loaded.
   *
   * @return array
   *   An array of content and assets to be rendered.
   */
  public function build(BlockInterface $block, array $loaded = array()) {
    $response = $this->buildMultiple([$block], $loaded);
    $response['content'] = reset($response['content']);
    return $response;
  }

  /**
   * Builds multiple blocks.
   *
   * @param array $blocks
   *   Array of Blocks to render.
   * @param array $loaded
   *   Libraries that have already been loaded.
   *
   * @return array
   *   An array of content and assets to be rendered.
   */
  public function buildMultiple(array $blocks, array $loaded = array()) {
    $attached = array();
    $content = array();

    foreach ($blocks as $block) {

      // Build the block content.
      $build = $block->getPlugin()->build();

      // Get the attached assets.
      if (isset($build['#attached'])) {
        foreach ($build['#attached'] as $type => $items) {
          if (!isset($attached[$type])) {
            $attached[$type] = array();
          }
          $attached[$type] = array_merge($attached[$type], $items);
        }
        unset($build['#attached']);
      }

      // Render the block. Render Plain is used to prevent the cachable metadata
      // from being added to the response, which throws a fatal error. The build
      // is typecasted as a string, because an object is returned.
      $content[$block->id()] = (string) $this->getRenderer()->renderPlain($build);
    }

    // Get all of the Assets.
    $assets = AttachedAssets::createFromRenderArray(['#attached' => $attached]);

    if ($loaded) {
      $assets->setAlreadyLoadedLibraries($loaded);
    }

    // Get the Librarys.
    $library_names = $this->getLibrariesToLoad($assets);
    $libraries = array();
    foreach ($library_names as $library_name) {
      list($extension, $name) = explode('/', $library_name);
      $data = $this->getLibraryDiscovery()->getLibraryByName($extension, $name);
      $libraries[$library_name] = isset($data['version']) ? $data['version'] : '';
    }

    // Get the performence configuration.
    $performence = $this->getConfig()->get('system.performance');

    // Get the CSS & JS Assets.
    $css = $this->getAssetResolver()->getCssAssets($assets, $performence->get('css.preprocess'));
    $js = $this->getAssetResolver()->getJsAssets($assets, $performence->get('js.preprocess'));

    $header = $this->getCssRenderer()->render($css) + $this->getJsRenderer()->render($js[0]);
    $header = array_map([$this, 'cleanAssetProperties'], $header);

    $footer = $this->getJsRenderer()->render($js[1]);
    $footer = array_map([$this, 'cleanAssetProperties'], $footer);

    return [
      'dependencies' => $libraries,
      'assets' => [
        'header' => $header,
        'footer' => $footer,
      ],
      'content' => $content,
    ];

  }

  /**
   * Cleans asset properties for easier consumption.
   *
   * @param array $asset
   *   Render array of assets.
   *
   * @return array
   *   An array with type and '#' removed.
   */
  public function cleanAssetProperties(array $asset) {
    $new = array();
    unset($asset['#type']);

    foreach ($asset as $key => $value) {
      $new[ltrim($key, '#')] = $value;
    }

    return $new;
  }

  /**
   * Returns the libraries that need to be loaded.
   *
   * For example, with core/a depending on core/c and core/b on core/d:
   * @code
   * $assets = new AttachedAssets();
   * $assets->setLibraries(['core/a', 'core/b', 'core/c']);
   * $assets->setAlreadyLoadedLibraries(['core/c']);
   * $resolver->getLibrariesToLoad($assets) === ['core/a', 'core/b', 'core/d']
   * @endcode
   *
   * @param \Drupal\Core\Asset\AttachedAssetsInterface $assets
   *   The assets attached to the current response.
   *
   * @return string[]
   *   A list of libraries and their dependencies, in the order they should be
   *   loaded, excluding any libraries that have already been loaded.
   */
  protected function getLibrariesToLoad(AttachedAssetsInterface $assets) {
    return array_diff(
      $this->getLibraryDependencyResolver()->getLibrariesWithDependencies($assets->getLibraries()),
      $this->getLibraryDependencyResolver()->getLibrariesWithDependencies($assets->getAlreadyLoadedLibraries())
    );
  }

  /**
   * Gets the Asset Resolver object.
   *
   * @return \Drupal\Core\Asset\AssetResolverInterface
   *   Asset Resolver object.
   */
  public function getAssetResolver() {
    return $this->assetResolver;
  }

  /**
   * Gets the Config Factory.
   *
   * @return \Drupal\Core\Config\ConfigFactoryInterface
   *   Config Factory object.
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * Gets the Library Discovery.
   *
   * @return \Drupal\Core\Asset\LibraryDiscoveryInterface
   *   Library Discovery object.
   */
  public function getLibraryDiscovery() {
    return $this->libraryDiscovery;
  }

  /**
   * Gets the Library Dependency Resolver.
   *
   * @return \Drupal\Core\Asset\LibraryDependencyResolverInterface
   *   Library Dependency Resolver object.
   */
  public function getLibraryDependencyResolver() {
    return $this->libraryDependencyResolver;
  }

  /**
   * Gets the Renderer service.
   *
   * @return \Drupal\Core\Render\RendererInterface
   *   Renderer object.
   */
  public function getRenderer() {
    return $this->renderer;
  }

  /**
   * Gets the CSS Renderer service.
   *
   * @return \Drupal\Core\Asset\AssetCollectionRendererInterface
   *   Renderer object.
   */
  public function getCssRenderer() {
    return $this->cssRenderer;
  }

  /**
   * Gets the Javascript Renderer service.
   *
   * @return \Drupal\Core\Asset\AssetCollectionRendererInterface
   *   Renderer object.
   */
  public function getJsRenderer() {
    return $this->jsRenderer;
  }

}
