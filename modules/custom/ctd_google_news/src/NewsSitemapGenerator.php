<?php
namespace Drupal\ctd_google_news;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\File\FileSystem;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use GuzzleHttp\Exception\RequestException;
use \XMLWriter;

class NewsSitemapGenerator

{
  /**
   * @var ConfigFactory
   */
  protected $configFactory;

  /**
   * @var FileSystem
   */
  protected $fs;

  /**
   * NewsSitemapGenerator constructor.
   * @param ConfigFactory $configFactory
   * @param FileSystem $fs
   */
  public function __construct(ConfigFactory $configFactory, FileSystem $fs)
  {
    $this->configFactory = $configFactory;
    $this->fs = $fs;
  }

  public function generateSitemapIndex($nodeToAdd) {
    global $base_url;
    // Create https news.xml file
    $baseConfigUrl = $this->getSetting('base_url', $base_url);
    $cid = 'news-sitemap-cid';
    $nodeSet = $this->getCurrentNodeSet($cid, $nodeToAdd);
    $fileName = '/news-secure.xml';
    $result = $this->generateXmlFile($baseConfigUrl, $nodeSet, $fileName);
    // Create http news.xml file
    $baseConfigUrl = str_replace('https:', 'http:', $baseConfigUrl);
    $fileName = '/news.xml';
    $result = $this->generateXmlFile($baseConfigUrl, $nodeSet, $fileName);
    return $result;
  }

  /**
   * Helper function to generate xml file
   *
   * @param $baseConfigUrl
   * @param $nodeSet
   * @param $fileName
   *
   * @return bool
   */
  public function generateXmlFile($baseConfigUrl, $nodeSet, $fileName) {
    $newsDir = $this->fs->realpath('public://sitemaps');
    $writer = new XMLWriter();
    $writer->openUri($newsDir . $fileName);
    $writer->setIndent(TRUE);
    
    $writer->startDocument('1.0', 'UTF-8');

    // Start urlset element
    $writer->startElement('urlset');
    $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    $writer->writeAttribute('xmlns:news', 'http://www.google.com/schemas/sitemap-news/0.9');

    $siteName = \Drupal::config('system.site')->get('name');

    foreach ($nodeSet as $node) {

      //$keyArray = [$node->field_category->referencedEntities()[0]->getName()];

      if (!empty($node->field_tags->referencedEntities())) {
        foreach ($node->field_tags->referencedEntities() as $tag) {
          $keyArray[] = $tag->getName();
        }
      }

      $defaultKeywords = $this->getSetting('default_keywords', 'Entertainment,Celebrities');
      // remove duplicates, empty values as well as clean spaces for default keyword entries
      $keywords = implode(',', array_unique(array_merge(array_map('trim', array_filter(explode(',', $defaultKeywords))), $keyArray)));


      $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$node->nid->value);
      // Start url element
      $writer->startElement('url');
      // Start loc element
      $writer->writeElement('loc', $baseConfigUrl . $alias);

      // Start news:news element
      $writer->startElement('news:news');

      // Start news:publication element
      $writer->startElement('news:publication');
      $writer->writeElement('news:name', $siteName);
      $writer->writeElement('news:language', 'en');

      // End news:publication element
      $writer->endElement();

      // NOTE - Not sure the most efficient way to add -08:00.
      $writer->writeElement('news:publication_date', $node->field_publish_date_display->value . "-08:00");
      $writer->writeElement('news:title', $node->title->value);
      $writer->writeElement('news:keywords', $keywords);
      
      // End news:news element
      $writer->endElement();
      
      //End url element:
      $writer->endElement();
    }

    // End urlset element
    $writer->endElement();
    $writer->endDocument();

    $bytes = $writer->flush();

    return is_int($bytes);
  }

  /**
   * Returns a news sitemap setting or a default value if setting does not
   * exist.
   *
   * @param string $name
   *   Name of the setting, like 'max_links'.
   *
   * @param mixed $default
   *   Value to be returned if the setting does not exist in the configuration.
   *
   * @return mixed
   *   The current setting from configuration or a default value.
   */
  public function getSetting($name, $default = FALSE) {
    $setting = $this->configFactory
      ->get('ctd_google_news.settings')
      ->get($name);
    return NULL !== $setting ? $setting : $default;
  }

  /**
   * @return string
   */
  public function getSitemap()
  {
    $this->fs->realpath('public://sitemaps');

    // Assign filename base on protocol.
    $fileName = !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? '/news-secure.xml' : '/news.xml';

    return file_get_contents($this->fs->realpath('public://sitemaps') . $fileName);
  }

  /**
   * Stores a specific sitemap setting in configuration.
   *
   * @param string $name
   *   Setting name, like 'max_links'.
   * @param mixed $setting
   *   The setting to be saved.
   *
   * @return $this
   */
  public function saveSetting($name, $setting) {
    $this->configFactory->getEditable("ctd_google_news.settings")
      ->set($name, $setting)->save();
    return $this;
  }


  /**
   * Return an array of nodes.
   * @param $cid
   * @param $nodeToAdd
   * @param bool $reset
   * @return Array
   */
  private function getCurrentNodeSet($cid, $nodeToAdd, $reset = FALSE)
  {
    if ($reset) {
      // Query database for nodes.
      $query = \Drupal::entityQuery('node');
      //Set the range for 48 Hours
      $range = time() - 60*60*48;

      $group = $query->andConditionGroup()
        ->condition('type', 'article')
        ->condition('status', 1);
        //->condition('created', $range, '>=');

      $nodeSet = $query
        ->condition($group)
        ->range(0, 1000)
        ->execute();

      $nodes = Node::loadMultiple($nodeSet);

      $expire = strtotime('+5 minutes');
      \Drupal::cache()->set($cid, $nodes, $expire);

      return $nodes;
    }

    // Attempt to fetch from cache.
    $cached = \Drupal::cache()->get($cid);

    if ($cached) {
      $nodes = $cached->data;
      // Insert nodes in this fashion to avoid querying
      // for all nodes.
      $this->insertNode($nodes, $nodeToAdd);
      $expire = strtotime('+5 minutes');
      \Drupal::cache()->set($cid, $nodes, $expire);
      return $nodes;
    } else {
      return $this->getCurrentNodeSet($cid, $nodeToAdd, TRUE);
    }
  }

  /**
   * Helper function to insert a new node.
   * @param $nodes
   * @param $nodeToAdd
   */
  private function insertNode(&$nodes, $nodeToAdd) {
    // Check to see if there is a node to add
    if ($nodeToAdd) {
      $nid = $nodeToAdd->id();
      // Only add nodes that were created within the past two days.
      $range = time() - 60*60*48;
      $createdDate = $nodeToAdd->created->value;
      if ($createdDate >= $range) {
        $nodes[$nid] = $nodeToAdd;
      }
    }
  }
  
  /**
   * Ping Google to let them know that the Google News Sitemap has updated.
   */
  public function pingGoogle() {
    $sitemapUrl = $this->getSetting('base_url') . '/news.xml';
    $http_client = \Drupal::httpClient();
    $googlePingUrl = "http://www.google.com/webmasters/sitemaps/ping?sitemap=" . urlencode($sitemapUrl);
    try {
      // Ping Google.
      $request = $http_client->request('GET', $googlePingUrl);
    }
    catch (RequestException $e) {
      \Drupal::logger('ctd_google_news')->error($e->getMessage());
    }
  }

}
