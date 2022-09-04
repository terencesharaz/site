<?php

namespace TenWebOptimizer\WebPageCache;

/**
 * Base class other (more-specific) classes inherit from.
 */
if(!defined('ABSPATH')) {
  exit;
}

/**
 * This class will work with wp hooks/filter. Will decide when to clear cache, enable/disable web caching, etc.
 *
 * */
class OptimizerWebPageCacheWP {

  protected static $instance = null;
  public $wp_config_file_path = ABSPATH . '/wp-config.php';
  public $page_cache_config_dir = WP_CONTENT_DIR . '/10web-page-cache-config';
  public $wp_cache_define = "define( 'WP_CACHE', true );\ndefine( 'TWO_PLUGIN_DIR_CACHE', '" . TENWEB_SO_PLUGIN_DIR . "' );";
  public $advanced_cache_path = WP_CONTENT_DIR . '/advanced-cache.php';

  function __construct(){

    if(isset($_GET["action"]) && $_GET["action"] === 'two_clear_page_cache') {
      $this->maybe_clear_page_cache();
    }

    add_action("update_option_two_settings", array($this, 'enable_disable_page_cache'), 10, 3);

    add_filter('page_row_actions', array($this, 'post_row_actions'), 10, 2);
    add_filter('post_row_actions', array($this, 'post_row_actions'), 10, 2);
  }

  public function delete_all_cache(){
    OptimizerWebPageCache::delete_all_cached_pages();
  }

  public function post_updated($post_id, $post_after, $post_before){
    OptimizerWebPageCache::delete_cache_by_post_id($post_id);
  }

  public function enable_disable_page_cache($old_value, $value, $option){

    $value = json_decode($value, true);
    $value = (isset($value["two_page_cache"])) ? $value["two_page_cache"] : "";

    if( $value === "on" ) {
      $this->store_page_cache_configs();
      if( !defined( 'TENWEB_SO_ADVANCED_CACHE' ) ) {
        $this->enable_page_cache();
      } else if ( !defined('WP_CACHE' ) || !defined("TWO_PLUGIN_DIR_CACHE" ) ) {
        $this->add_wp_cache_constant();
        define( "TWO_PLUGIN_DIR_CACHE", TENWEB_SO_PLUGIN_DIR );
      }
    }
    else {
       $this->disable_page_cache();
    }
  }

  public function enable_page_cache(){
    if($this->add_wp_cache_constant() === false) {
      return false;
    }

    return copy(TENWEB_SO_PLUGIN_DIR . 'includes/WebPageCache/advanced-cache.php', $this->advanced_cache_path);
  }

  public function disable_page_cache(){
    if($this->remove_wp_cache_constant() === false) {
      return false;
    }

    if($this->delete_page_cache_configs() === false) {
      return false;
    }

    if (!file_exists($this->advanced_cache_path)) {
        return true;
    }

    return unlink($this->advanced_cache_path);
  }

  public function add_wp_cache_constant(){

    if(!is_writable($this->wp_config_file_path) || !is_readable($this->wp_config_file_path)) {
      return false;
    }

    $file_content = file_get_contents($this->wp_config_file_path);
    if(!strpos($file_content, "TWO_PLUGIN_DIR_CACHE")){
        $replacement = "<?php\n# BEGIN WP Cache by 10Web\n" . $this->wp_cache_define . "\n# END WP Cache by 10Web\n";
        $file_content = preg_replace('@<\?php\s*@i', $replacement, $file_content, 1);
        return !!file_put_contents($this->wp_config_file_path, $file_content);
    }
    return true;
  }

  public function remove_wp_cache_constant(){
    if(!is_writable($this->wp_config_file_path) || !is_readable($this->wp_config_file_path)) {
      return false;
    }

    $file_content = file_get_contents($this->wp_config_file_path);
    $file_content = preg_replace('/# BEGIN WP Cache by 10Web[\s\S]*# END WP Cache by 10Web/', "", $file_content);
    return !!file_put_contents($this->wp_config_file_path, $file_content);
  }

  public function store_page_cache_configs(){
    if(!is_dir($this->page_cache_config_dir) && !mkdir($concurrentDirectory = $this->page_cache_config_dir, 0777) && !is_dir($concurrentDirectory)) {
      return false;
    }
    $configs = [];
    $configs["two_settings"] = get_option("two_settings");
    return !!file_put_contents($this->page_cache_config_dir . '/config.json', json_encode($configs));
  }

  public function delete_page_cache_configs(){
      if(file_exists($this->page_cache_config_dir . '/config.json')){
          return unlink($this->page_cache_config_dir . '/config.json');
      }
      return true;
  }

  public function maybe_clear_page_cache(){

    if(!empty($_GET["permalink"]) && wp_verify_nonce("two_clear_page_cache") !== null) {
      OptimizerWebPageCache::delete_cache_by_url(sanitize_url( $_GET["permalink"] ));
    }
    $redirect_to = (!empty($_SERVER['HTTP_REFERER'])) ? sanitize_text_field( $_SERVER['HTTP_REFERER'] ) : "/wp-admin/edit.php";

    wp_safe_redirect($redirect_to);
    die;
  }

  public function post_row_actions($actions, $post){
    global $TwoSettings;
    if($TwoSettings->get_settings("two_page_cache") !== "on") {
      return $actions;
    }


    $url = wp_nonce_url(admin_url('admin-post.php?action=two_clear_page_cache&permalink=' . get_permalink($post)), "two_clear_page_cache");
    $actions['two_clear_page_cache'] = sprintf('<a href="%s">%s</a>', $url, __('Clear page cache', 'tenweb-speed-optimizer'));

    return $actions;
  }

  public static function get_instance(){
    if(null == self::$instance) {
      self::$instance = new self;
    }

    return self::$instance;
  }

}