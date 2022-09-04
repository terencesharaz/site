<?php
/**
 * Plugin Name: 10Web Booster
 * Plugin URI: https://10web.io/page-speed-booster/
 * Description: Optimize your website speed and performance with 10Web Booster by compressing CSS and JavaScript.
 * Version: 2.2.16
 * Author: 10Web - Website speed optimization team
 * Author URI: https://10web.io/
 * Text Domain: tenweb-speed-optimizer
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('TWO_ALWAYS_CRITICAL')) {
    define('TWO_ALWAYS_CRITICAL', true);
}
if (!defined('TWO_PLUGIN_FILE')) {
    define( 'TWO_PLUGIN_FILE', __FILE__ );;
}

if(isset($_GET["two_check_redirect"]) && $_GET["two_check_redirect"] === "1"){
    return;
}

global $two_incompatible_errors;
$two_incompatible_errors = array();
require_once 'config.php';
if ( PHP_MAJOR_VERSION < 7 || ( PHP_MAJOR_VERSION === 7 && PHP_MINOR_VERSION === 0 ) ) {
    if ( !defined( 'TWO_INCOMPATIBLE_ERROR' ) ) {
        define( 'TWO_INCOMPATIBLE_ERROR', true );
    }
    $two_incompatible_errors[] = array( 'title' => __('PHP compatibility error:', 'tenweb-speed-optimizer'),
                                        'message' => __('PHP 7.1 or a newer version is required for 10Web Booster. Please update your PHP version to proceed.', 'tenweb-speed-optimizer') );
}
if ( get_site_transient( 'tenweb_so_auth_error_logs' ) ) {
    if ( !defined( 'TWO_INCOMPATIBLE_WARNING' ) ) {
        define( 'TWO_INCOMPATIBLE_WARNING', true );
    }
    $two_incompatible_errors[] = array( 'title' => __('Website already connected:', 'tenweb-speed-optimizer'),
                                        'message' => __('This website is already connected to the 10Web dashboard via the manager plugin. Please disconnect it from the manager plugin to be able to use 10Web Booster.', 'tenweb-speed-optimizer') );
    delete_site_transient( 'tenweb_so_auth_error_logs' );
}
if ( is_multisite() && !TENWEB_SO_HOSTED_ON_10WEB) {
    if ( !defined( 'TWO_HOSTED_MULTISITE' ) ) {
        define( 'TWO_HOSTED_MULTISITE', true );
    }
    if ( !defined( 'TWO_INCOMPATIBLE_ERROR' ) ) {
       define( 'TWO_INCOMPATIBLE_ERROR', true );
    }
  $two_incompatible_errors[] = array( 'title' => __('Multisite not supported:', 'tenweb-speed-optimizer'),
                                      'message' => __('This feature will be available soon.', 'tenweb-speed-optimizer') );
}




if ( defined( 'TWO_INCOMPATIBLE_ERROR' ) && TWO_INCOMPATIBLE_ERROR ) {
    add_action( 'admin_menu', function() {
        require_once TENWEB_SO_PLUGIN_DIR . 'OptimizerAdmin.php';
        add_options_page('10Web Booster', '10Web Booster', 'manage_options', 'two_settings_page', array(
          '\TenWebOptimizer\OptimizerAdmin',
          'settings_page',
        ));
    } );
    add_action( 'admin_enqueue_scripts', array( '\TenWebOptimizer\OptimizerAdmin', 'two_enqueue_admin_assets' ) );
} else {
    include_files();

    global $tenweb_subscription_id;
    $tenweb_subscription_id = get_transient(TENWEB_PREFIX . '_subscription_id');
    if((!isset($tenweb_subscription_id)  || (int)$tenweb_subscription_id<1) && $tenweb_subscription_id != "0"){
        $tenweb_subscription_id = \TenWebOptimizer\OptimizerUtils::two_update_subscription();
    } elseif ( $tenweb_subscription_id == "0" && !TENWEB_SO_HOSTED_ON_10WEB ){
        $tenweb_subscription_id = TENWEB_SO_FREE_SUBSCRIPTION_ID;
    }

    register_deactivation_hook(__FILE__, array('\TenWebOptimizer\OptimizerAdmin', 'two_deactivate'));
    register_uninstall_hook( __FILE__,  array('\TenWebOptimizer\OptimizerAdmin', 'two_uninstall') );
    global $TwoSettings;
    $TwoSettings =  \TenWebOptimizer\OptimizerSettings::get_instance();


    if (!isset($_GET['action']) || $_GET['action'] != 'deactivate') {
        register_activation_hook(__FILE__, array('\TenWebOptimizer\OptimizerAdmin', 'two_activate'));
        add_action("plugins_loaded", "two_init");
    }

}


function include_files() {
    require_once 'vendor/autoload.php';
}


function two_init()
{
    if(isset($_GET["two_preview"]) && $_GET["two_preview"]==="1"){
        add_filter("determine_current_user" , function ($user_id){
            return 0;
        },99);
    }

    add_action( 'wp_ajax_two_set_critical', 'two_set_critical' );
    add_action( 'wp_ajax_nopriv_two_set_critical', 'two_set_critical' );

    require __DIR__ . '/OptimizerApi.php';
    $OptimizerApi = new OptimizerApi();

    \TenWebIO\Init::getInstance();

    global $TwoSettings;
    if ( defined( 'WP_CLI' ) && WP_CLI ) { //Run only TWO CLI in WP_CLI mode
        require __DIR__ . '/OptimizerCli.php';
        return;
    }

    $two_disable_jetpack_optimization = $TwoSettings->get_settings("two_disable_jetpack_optimization");
    if ( 'on' === $two_disable_jetpack_optimization ) {
        add_filter( 'option_jetpack_active_modules', 'two_jetpack_module_override' );
        function two_jetpack_module_override( $modules ) {
            $disabled_modules = array(
                'lazy-images',
                'photon',
                'photon-cdn',
            );

            foreach ( $disabled_modules as $module_slug ) {
                $found = array_search( $module_slug, $modules );
                if ( false !== $found ) {
                    unset( $modules[ $found ] );
                }
            }

            return $modules;
        }
    }
    \TenWebOptimizer\OptimizerAdmin::get_instance();
    if(\Tenweb_Authorization\Login::get_instance()->check_logged_in()){
        \TenWebOptimizer\OptimizerMain::get_instance();
        \TenWebOptimizer\WebPageCache\OptimizerWebPageCacheWP::get_instance();
    }


    if(isset($_GET["two_action"]) && $_GET["two_action"] === 'generating_critical_css') {
        ob_start('two_critical', 0, PHP_OUTPUT_HANDLER_REMOVABLE);
    }
}

function two_critical($content){
    return \TenWebOptimizer\OptimizerUtils::clear_iframe_src($content);
}

function two_set_critical(){
   \TenWebOptimizer\OptimizerUtils::set_critical();
}





