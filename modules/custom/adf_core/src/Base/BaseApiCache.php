<?php

namespace Drupal\adf_core\Base;

use Drupal\adf_core\Util\UtilElement;
use Drupal\adf_core\Util\UtilString;

/**
 *
 */
class BaseApiCache {

  /**
   * @param $category
   * @param $key
   * @param $arguments
   * @param $data
   * @param int $expire
   * @param string[] $tags
   * @throws \Exception
   */
  public static function set($category, $key, $arguments, $data, $expire = 30, array $tags = []) {
    if (UtilElement::getSize($data) > 1024 * 1024) {
      throw new \Exception("The data in cache such as a string, array or object that gets serialised should not be over 1MB in size; Category:" . $category . ", Key:" . $key . ", Arguments: " . print_r($arguments, TRUE));
    }
    else {
      $name_cache = BaseApiCache::getNameCache($category, $key, $arguments);
      $expire = time() + $expire * 60;
      \Drupal::cache()->set($name_cache, $data, $expire, array_merge([$name_cache], $tags));
    }
  }

  /**
   * @param $category
   * @param $key
   * @param $arguments
   * @return bool
   */
  public static function get($category, $key, $arguments = []) {
    $name_cache = BaseApiCache::getNameCache($category, $key, $arguments);
    $cache = \Drupal::cache()->get($name_cache);
    if ($cache != NULL) {
      return $cache->data;
    }
    else {
      return FALSE;
    }
  }

  /**
   * @param $category
   * @param $key
   * @param $arguments
   * @throws \Exception
   */
  public static function delete($category, $key, $arguments) {
    $name_cache = BaseApiCache::getNameCache($category, $key, $arguments);
    \Drupal::cache()->delete($name_cache);
  }

  /**
   * Marks cache items from all bins with any of the specified tags as invalid.
   *
   * @param string[] $tags
   *   The list of tags to invalidate cache items for.
   */
  public static function invalidateTags(array $tags) {
    \Drupal::service('cache_tags.invalidator')
      ->invalidateTags($tags);
  }

  /**
   *
   * @param $category
   * @param $key
   * @param $arguments
   * @return string
   */
  private static function getNameCache($category, $key, $arguments) {
    $hash = UtilString::getHash(empty($arguments) ? "none" : $arguments);
    $name_cache = "adf:" . $category . ":" . $key . ":" . $hash;
    return $name_cache;
  }

  /**
   * @param $key
   * @return bool
   */
  public static function getGlobal($key) {
    $cache = BaseApiCache::get("global", $key);
    return $cache;
  }

  /**
   * @param $key
   * @param $data
   * @param $expire
   */
  public static function setGlobal($key, $data, $expire = 60) {
    BaseApiCache::set("global", $key, [], $data, $expire);
  }

  /**
   * @param $key
   */
  public static function deleteGlobal($key) {
    BaseApiCache::delete("global", $key, []);
  }

  /**
   * @param $key
   * @return string
   */
  private static function getWebSessionId($key) {
    return $key . \Drupal::service('session')->getId();
  }

  /**
   * @param $key
   * @return bool
   */
  public static function getSession($key) {
    return BaseApiCache::get("session", BaseApiCache::getWebSessionId($key));
  }

  /**
   * @param $key
   * @param $data
   * @param int $expire
   */
  public static function setSession($key, $data, $expire = 30) {
    BaseApiCache::set("session", BaseApiCache::getWebSessionId($key), [], $data, $expire);
  }

}
