<?php

namespace TenWebOptimizer;

use JSMin\JSMin;
use WP_Rewrite;

/**
 * General helpers.
 */
if (!defined('ABSPATH')) {
    exit;
}

class OptimizerUtils
{

    const OPTIMIZED_BG_MARKER     = '++TWO_OPTIMIZED_BG_IMAGE++';
    const SVG_DATA                = "data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%20";
    const BACKGROUND_IMAGE_REGEXP_NEW__PERFECT__SLOW = "~(?:\})*(.*)\{(?:[^}]|\w\s\S)*(?:background[-image]*\s*:.*url.*\(\s*((?:[\'\"]{1}(?:.*)?[\'\"]{1})|(?:[^\'\"]{1}(?:.*)?[^\'\"]{1}))\s*\))+[^}]*\}~U";
    const BACKGROUND_IMAGE_REGEXP_OLD__NOT_PERFECT__FAST = "~(.*)\{(?:[^}]|\w\s\S)*(?:background[-image]*?\s*:.*?url.*?\(\s*((?:[\'|\"]{1}(?:.*?)?[\'|\"]{1})|(?:[^\'|\"]{1}(?:.*?)?[^\'|\"]{1}))\s*\))+[^}]*\}~";
    const FROM_PLUGIN = '10webspeedoptimizer';
    const MODES = array(
        'standard' => array(
            'mode'=>'standard',
            'two_delay_all_js_execution'=>false,
            'critical_enabled'=>false,
            'lazy_load_type' => 'browser'
        ),
        'balanced' => array(
            'mode'=>'balanced',
            'two_delay_all_js_execution'=>false,
            'critical_enabled'=>true,
            'lazy_load_type' => 'vanilla'
        ),
        'strong' => array(
            'mode'=>'strong',
            'two_delay_all_js_execution'=>true,
            'critical_enabled'=>false,
            'lazy_load_type' => 'vanilla'
        ),
        'extreme' => array(
            'mode'=>'extreme',
            'two_delay_all_js_execution'=>true,
            'critical_enabled'=>true,
            'lazy_load_type' => 'vanilla'
        ),
        'no_optimize' => array(
            'mode'=>'no_optimize',
        ),
    );


    /**
     * Returns true when mbstring is available.
     *
     * @param bool|null $override Allows overriding the decision.
     *
     * @return bool
     */
    public static function mbstring_available($override = null)
    {
        static $available = null;
        if (null === $available) {
            $available = \extension_loaded('mbstring');
        }
        if (null !== $override) {
            $available = $override;
        }

        return $available;
    }

    /**
     * Multibyte-capable strpos() if support is available on the server.
     * If not, it falls back to using \strpos().
     *
     * @param string      $haystack Haystack.
     * @param string      $needle   Needle.
     * @param int         $offset   Offset.
     * @param string|null $encoding Encoding. Default null.
     *
     * @return int|false
     */
    public static function strpos($haystack, $needle, $offset = 0, $encoding = null)
    {
        if (self::mbstring_available()) {
            return (null === $encoding) ? \mb_strpos($haystack, $needle, $offset) : \mb_strpos($haystack, $needle, $offset, $encoding);
        } else {
            return \strpos($haystack, $needle, $offset);
        }
    }

    /**
     * Attempts to return the number of characters in the given $string if
     * mbstring is available. Returns the number of bytes
     * (instead of characters) as fallback.
     *
     * @param string      $string   String.
     * @param string|null $encoding Encoding.
     *
     * @return int Number of charcters or bytes in given $string
     *             (characters if/when supported, bytes otherwise).
     */
    public static function strlen($string, $encoding = null)
    {
        if (self::mbstring_available()) {
            return (null === $encoding) ? \mb_strlen($string) : \mb_strlen($string, $encoding);
        } else {
            return \strlen($string);
        }
    }

    /**
     * Our wrapper around implementations of \substr_replace()
     * that attempts to not break things horribly if at all possible.
     * Uses mbstring if available, before falling back to regular
     * substr_replace() (which works just fine in the majority of cases).
     *
     * @param string      $string      String.
     * @param string      $replacement Replacement.
     * @param int         $start       Start offset.
     * @param int|null    $length      Length.
     * @param string|null $encoding    Encoding.
     *
     * @return string
     */
    public static function substr_replace($string, $replacement, $start, $length = null, $encoding = null)
    {
        if (self::mbstring_available()) {
            $strlen = self::strlen($string, $encoding);
            if ($start < 0) {
                if (-$start < $strlen) {
                    $start = $strlen + $start;
                } else {
                    $start = 0;
                }
            } else if ($start > $strlen) {
                $start = $strlen;
            }
            if (null === $length || '' === $length) {
                $start2 = $strlen;
            } else if ($length < 0) {
                $start2 = $strlen + $length;
                if ($start2 < $start) {
                    $start2 = $start;
                }
            } else {
                $start2 = $start + $length;
            }
            if (null === $encoding) {
                $leader = $start ? \mb_substr($string, 0, $start) : '';
                $trailer = ($start2 < $strlen) ? \mb_substr($string, $start2, null) : '';
            } else {
                $leader = $start ? \mb_substr($string, 0, $start, $encoding) : '';
                $trailer = ($start2 < $strlen) ? \mb_substr($string, $start2, null, $encoding) : '';
            }

            return "{$leader}{$replacement}{$trailer}";
        }

        return (null === $length) ? \substr_replace($string, $replacement, $start) : \substr_replace($string, $replacement, $start, $length);
    }

    /**
     * Decides whether this is a "subdirectory site" or not.
     *
     * @param bool $override Allows overriding the decision when needed.
     *
     * @return bool
     */
    public static function siteurl_not_root($override = null)
    {
        static $subdir = null;
        if (null === $subdir) {
            $parts = self::get_ao_wp_site_url_parts();
            $subdir = (isset($parts['path']) && ('/' !== $parts['path']));
        }
        if (null !== $override) {
            $subdir = $override;
        }

        return $subdir;
    }

    /**
     * Parse TWO_WP_SITE_URL into components using \parse_url(), but do
     * so only once per request/lifecycle.
     *
     * @return array
     */
    public static function get_ao_wp_site_url_parts()
    {
        static $parts = array();
        if (empty($parts)) {
            $parts = \wp_parse_url(TWO_WP_SITE_URL);
        }

        return $parts;
    }

    /**
     * Modify given $cdn_url to include the site path when needed.
     *
     * @param string $cdn_url          CDN URL to tweak.
     * @param bool   $force_cache_miss Force a cache miss in order to be able
     *                                 to re-run the filter.
     *
     * @return string
     */
    public static function tweak_cdn_url_if_needed($cdn_url, $force_cache_miss = false)
    {
        static $results = array();
        if (!isset($results[$cdn_url]) || $force_cache_miss) {

            // In order to return unmodified input when there's no need to tweak.
            $results[$cdn_url] = $cdn_url;
            // Behind a default true filter for backcompat, and only for sites
            // in a subfolder/subdirectory, but still easily turned off if
            // not wanted/needed...
            if (OptimizerUtils::siteurl_not_root()) {
                $site_url_parts = OptimizerUtils::get_ao_wp_site_url_parts();
                $cdn_url_parts = \wp_parse_url($cdn_url);
                $schemeless = self::is_protocol_relative($cdn_url);
                $cdn_url_parts = self::maybe_replace_cdn_path($site_url_parts, $cdn_url_parts);
                if (false !== $cdn_url_parts) {
                    $results[$cdn_url] = self::assemble_parsed_url($cdn_url_parts, $schemeless);
                }
            }
        }

        return $results[$cdn_url];
    }

    /**
     * When siteurl contans a path other than '/' and the CDN URL does not have
     * a path or it's path is '/', this will modify the CDN URL's path component
     * to match that of the siteurl.
     * This is to support "magic" CDN urls that worked that way before v2.4...
     *
     * @param array $site_url_parts Site URL components array.
     * @param array $cdn_url_parts  CDN URL components array.
     *
     * @return array|false
     */
    public static function maybe_replace_cdn_path(array $site_url_parts, array $cdn_url_parts)
    {
        if (isset($site_url_parts['path']) && '/' !== $site_url_parts['path']) {
            if (!isset($cdn_url_parts['path']) || '/' === $cdn_url_parts['path']) {
                $cdn_url_parts['path'] = $site_url_parts['path'];

                return $cdn_url_parts;
            }
        }

        return false;
    }

    /**
     * Given an array or components returned from \parse_url(), assembles back
     * the complete URL.
     * If optional
     *
     * @param array $parsed_url URL components array.
     * @param bool  $schemeless Whether the assembled URL should be
     *                          protocol-relative (schemeless) or not.
     *
     * @return string
     */
    public static function assemble_parsed_url(array $parsed_url, $schemeless = false)
    {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        if ($schemeless) {
            $scheme = '//';
        }
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    /**
     * Returns true if given $url is protocol-relative.
     *
     * @param string $url URL to check.
     *
     * @return bool
     */
    public static function is_protocol_relative($url)
    {
        $result = false;
        if (!empty($url)) {
            $result = (0 === strpos($url, '//'));
        }

        return $result;
    }

    /**
     * Canonicalizes the given path regardless of it existing or not.
     *
     * @param string $path Path to normalize.
     *
     * @return string
     */
    public static function path_canonicalize($path)
    {
        $patterns = array(
            '~/{2,}~',
            '~/(\./)+~',
            '~([^/\.]+/(?R)*\.{2,}/)~',
            '~\.\./~',
        );
        $replacements = array(
            '/',
            '/',
            '',
            '',
        );

        return preg_replace($patterns, $replacements, $path);
    }

    /**
     * Returns true if the string is a valid regex.
     *
     * @param string $string String, duh.
     *
     * @return bool
     */
    public static function str_is_valid_regex($string)
    {
        set_error_handler(function () {
        }, E_WARNING);
        $is_regex = (false !== preg_match($string, ''));
        restore_error_handler();

        return $is_regex;
    }

    /**
     * Returns true if a certain WP plugin is active/loaded.
     *
     * @param string $plugin_file Main plugin file.
     *
     * @return bool
     */
    public static function is_plugin_active($plugin_file)
    {
        static $ipa_exists = null;
        if (null === $ipa_exists) {
            if (!function_exists('\is_plugin_active')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $ipa_exists = function_exists('\is_plugin_active');
        }

        return $ipa_exists && \is_plugin_active($plugin_file);
    }

    public static function replace_font($css)
    {
        global $TwoSettings;
        $two_async_font = $TwoSettings->get_settings("two_async_font");
        if (isset($two_async_font) && $two_async_font == "on") {
            /*$re = '~(?>@font-face\s*{\s*|\G(?!\A))(\S+)\s*:\s*valet([^;]+);\s*~';*/
            $re = '/@font-face.*{\K[^}]*(?=})/';
            preg_match_all($re, $css, $matches, PREG_SET_ORDER, 0);

            foreach ($matches as $el) {
                if (isset($el[0])) {
                    if (strpos($el[0], "font-display") !== false) {
                        $re1 = '/font-display\s*:\s*\K[^;]*(?=;)/';
                        preg_match_all($re1, $el[0], $elMatches, PREG_SET_ORDER, 0);
                        if (isset($elMatches, $elMatches[0], $elMatches[0][0])) {
                            $style = str_replace($elMatches[0][0], "swap;", $el[0]);
                            $css = str_replace($el[0], $style, $css);
                        }
                    } else {
                        $style = $el[0] . ";font-display: swap;";
                        $css = str_replace($el[0], $style, $css);
                    }
                }
            }
        }

        return $css;
    }

    public static function replace_google_font_url($url){
        if(strpos($url, "display")){
            $url = str_replace("&amp;", "&", $url);
            $parsed_url = wp_parse_url(urldecode($url));
            parse_str($parsed_url["query"], $url_params);
            if(isset($url_params["display"])){
                $url = str_replace($url_params["display"], "swap", $url);
            }else{
                $url = add_query_arg('display', 'swap', $url);
            }
        }else{
            $url = add_query_arg('display', 'swap', $url);
        }
        return $url;
    }

    public static function serve_different_sizes_for_critical_bg_image($images_data){
        //tenweb_optimizer_mobile
        $imagesArray = array();
        $css = "";
        if(is_array($images_data)) {
          $allSizes = get_intermediate_image_sizes();
          foreach ( $images_data as $image_data ) {
            if ( isset( $image_data[ "bg_url" ] ) && isset( $image_data[ "selector" ] ) && is_array( $image_data[ "selector" ] ) ) {
              $imageId = self::getImageIdByUrl( strtok($image_data[ "bg_url" ] , '?') );
              if ( $imageId ) {
                  $css_rule = implode(":not(.two_bg), ",$image_data["selector"]);
                  $css_rule.=":not(.two_bg)";
                //create an array with all image sizes
                foreach ( $allSizes as $i => $size ) {
                  $imagesArray[ $size ] = wp_get_attachment_image_src( $imageId, $size );
                }
                if ( isset( $imagesArray[ 'tenweb_optimizer_mobile' ] ) ) {
                  if ( isset( $imagesArray[ 'tenweb_optimizer_mobile' ][ 0 ] ) && !empty( $imagesArray[ 'tenweb_optimizer_mobile' ][ 0 ] ) ) {
                    $mobileRule = " background-image: url(" . $imagesArray[ 'tenweb_optimizer_mobile' ][ 0 ] . ") !important; ";
                  }
                }
                //fallback to elementor images
                if ( empty( $mobileRule ) ) {
                  foreach ( $imagesArray as $size => $imageArray ) {
                    if ( !empty( $imageArray ) ) {
                      if ( $size === 'medium_large' && empty( $mobileRule ) ) {
                        if ( isset( $imageArray[ 0 ] ) && !empty( $imageArray[ 0 ] ) ) {
                          $mobileRule = " background-image: url(" . $imageArray[ 0 ] . ") !important; ";
                        }
                        continue;
                      }
                    }
                  }
                }
                //generate media css blocks and add to the end of css file
                if ( !empty( $mobileRule ) ) {
                  $mobileCss = "\r\n" . rtrim( $css_rule, ',' )
                    . ' { ' . $mobileRule . ' } ';

                  $css .= ' ' . $mobileCss;
                }
              }
            }
          }

        }
        if ($css) {
            $css = self::replace_bg($css);
            $css = "/* Autogenerated by 10Web Booster plugin*/\r\n
                    @media (min-width: 320px) and (max-width: 480px) { \r\n" . $css . "}";
        }

        return $css;

    }


    public static function getImageIdByUrl($url)
    {
        global $wpdb;
        // If the URL is auto-generated thumbnail, remove the sizes and get the URL of the original image
        $url = preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $url);
        //    $imgid = attachment_url_to_postid($url);
        $wp_uploads = wp_upload_dir();
        $attachment_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value = %s", str_replace($wp_uploads['baseurl'] . '/', '', $url)));

        return $attachment_id;
    }

    /**
     * This is the copy of WP function to regenerate metadata if it is set but is missing sizes for some reason.
     * The original is in wp-includes/media.php
     *
     * Maybe attempts to generate attachment metadata, if missing.
     *
     * @param WP_Post $attachment Attachment object.
     */
    public static function wp_maybe_generate_attachment_metadata( $attachment ) {
      if ( empty( $attachment ) || empty( $attachment->ID ) ) {
        return;
      }

      $attachment_id = (int) $attachment->ID;
      $file          = get_attached_file( $attachment_id );
      $meta          = wp_get_attachment_metadata( $attachment_id );

      if ( empty( $meta ) && file_exists( $file ) ) {
        $_meta = get_post_meta( $attachment_id );
        $_lock = 'wp_generating_att_' . $attachment_id;

        if ( ( ! array_key_exists( '_wp_attachment_metadata', $_meta ) || empty( $_meta[ '_wp_attachment_metadata' ][ 'sizes' ] ) ) && ! get_transient( $_lock ) ) {
          set_transient( $_lock, $file );
          wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $file ) );
          delete_transient( $_lock );
        }
      }
    }

    public static function replace_bg($css)
    {
        $replaced_images = array();
        global $TwoSettings;
        $two_lazyload = $TwoSettings->get_settings("two_lazyload");
        $two_bg_lazyload = $TwoSettings->get_settings("two_bg_lazyload");
        $critical = new OptimizerCriticalCss();
        if (TWO_LAZYLOAD && isset($two_bg_lazyload) && $two_bg_lazyload == "on" && !$critical->use_uncritical) {
            //$re = '~\bbackground[-image]*?\s*:.*?url.*?\(\s*[\'|"]?(.*?)?[\'|"]?\s*\)~i';
            //$re = '~url\s*\(\s*[\'|"]?(.*?)?[\'|"]?\s*\)~i';
            //$re = '~\bbackground[-image]*?\s*:.*?url.*?\(\s*[\'|"]?(.*?)?[\'|"]\s*\)~i';
            $re = '~\bbackground[-image]*?\s*:.*?url.*?\(\s*?(.*?)?\s*\)~i';
            $svg_data = self::SVG_DATA . '%20' . '%22%3E%3C/svg%3E';
            preg_match_all($re, $css, $matches);
            $ext_list = array("svg", "jpeg", "png", "gif", "jpg");
            if (isset($matches) && is_array($matches) && isset($matches[1])) {
                $images = $matches[1];
                foreach ($images as $src) {
                    $src = str_replace(array("'", '"'), array("") ,$src);
                    $url = strtok($src, '?');
                    $ext = pathinfo($url, PATHINFO_EXTENSION);
                    if (!in_array($ext, $ext_list)) {
                        continue;
                    }
                    if (in_array($src, $replaced_images)) {
                        continue;
                    }
                    //pass serve_different_sizes_for_bg_image added backgrounds
                    if (self::strpos($src, self::OPTIMIZED_BG_MARKER) !== false) {
                        continue;
                    }
                    $flag_continue = false;
                    global $TwoSettings;
                    $two_exclude_lazyload = $TwoSettings->get_settings("two_exclude_lazyload");
                    if (isset($two_exclude_lazyload) && !empty($two_exclude_lazyload)) {
                        $exclude_lazyload = explode(",", $two_exclude_lazyload);
                        foreach ($exclude_lazyload as $path) {
                            if (strpos($src, $path) !== false) {
                                $flag_continue = true;
                            }
                        }
                    }
                    if ($flag_continue) {
                        continue;
                    }
                    $replaced_images[] = $src;
                    $pos = strpos($src, "#}");
                    if ($pos === false) {
                        $css = str_replace($src, $svg_data . "#}" . $src, $css);}
                    }
                }
            }

        return $css;
    }

    /**
     * Cache compare.
     *
     * @param array $args
     */
    public static function cache_compare($args = [])
    {
        $type = $args['type'];
        $post_id = $args['post_id'];
        $new_cache_files = $args['new_cache_files'];
        $old_cache_files = get_post_meta($post_id, 'two_cache_' . $type, true);
        if (!empty($new_cache_files) && empty($old_cache_files)) {
            add_post_meta($post_id, 'two_cache_' . $type, []);
        }
        if (!empty($new_cache_files)) {
            update_post_meta($post_id, 'two_cache_' . $type, $new_cache_files);
            if (!empty($old_cache_files)) {
                $dir_gzip = OptimizerCache::get_path();
                foreach ($old_cache_files as $old_file) {
                    if (!in_array($old_file, $new_cache_files)) {
                        $old_file_name = ($type == 'gzip') ? $old_file : $type . '/' . $old_file;
                        self::delete_cache_file($old_file_name);
                        if ($type == 'gzip') {
                            foreach (['deflate', 'none', 'gzip'] as $val) {
                                $file_gzip = $old_file . '.' . $val;
                                if (is_file($dir_gzip . $file_gzip)) {
                                    self::delete_cache_file($file_gzip);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Cache files parsing array.
     * @return array
     */
    public static function cache_files_parsing_array()
    {
        $js = [];
        $css = [];
        $gzip = [];
        $file_name = '_all_cache_files.txt';
        if (is_file(TWO_CACHE_DIR . $file_name)) {
            $files = file_get_contents(TWO_CACHE_DIR . $file_name);
            if (!empty($files)) {
                OptimizerUtils::delete_cache_file($file_name);
                $files = json_decode($files);
                foreach ($files as $file) {
                    preg_match('/^css\/(.*).css$/', $file, $matches_css);
                    preg_match('/^js\/(.*).js$/', $file, $matches_js);
                    preg_match('/^(.*).php$/', $file, $matches_php);
                    if (!empty($matches_css)) {
                        $css[] = str_replace('css/', '', $matches_css[0]);
                    }
                    if (!empty($matches_js)) {
                        if (strpos($matches_js[0], 'two_snippet_') < -1) {
                            $js[] = str_replace('js/', '', $matches_js[0]);
                        }
                    }
                    if (!empty($matches_php)) {
                        if (strpos($matches_php[0], 'two_snippet_') < -1) {
                            $gzip[] = $matches_php[0];
                        }
                    }
                }
            }
        }

        return array('js' => array_unique($js), 'css' => array_unique($css), 'gzip' => array_unique($gzip));
    }

    /**
     * Delete all cache on DB.
     *
     * @param array $args
     *
     * @return bool
     */
    public static function delete_all_cache_db($args = [])
    {
        global $wpdb;
        $tbl = $wpdb->prefix . 'postmeta';
        $css = $wpdb->delete($tbl, array('meta_key' => 'two_cache_css'));
        $js = $wpdb->delete($tbl, array('meta_key' => 'two_cache_js'));
        $gzip = $wpdb->delete($tbl, array('meta_key' => 'two_cache_gzip'));

        return true;
    }

    /**
     * Deleted recursively directory and its entire contents.
     *
     * @param string $dir
     * @param array  $not_allow_delete
     *
     * @return mixed
     */
    public static function delete_all_cache_file($dir = '', $not_allow_delete = [], $not_allow_folder = null)
    {

        if (is_dir($dir)) {
            $objects = scandir($dir);
            if(!is_array($objects)){
                return false;
            }
            foreach ($objects as $key => $object) {
                if ($object === $not_allow_folder) {
                    continue;
                }
                if ($object != "." && $object != ".." && $object != "index.html") {
                    if (is_dir($dir . '/' . $object) && !is_link($dir . '/' . $object)) {
                        self::delete_all_cache_file(rtrim($dir, '/') . '/' . $object, $not_allow_delete);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                    unset($objects[$key]);
                }
            }
            if (count($objects) === 2 && (empty($not_allow_delete) || !in_array($dir, $not_allow_delete) || empty($not_allow_folder))) {
                rmdir($dir);
            }

            return true;
        }
        return true;
    }


    public static function delete_files_by_prefix($prefix)
    {
        $dir = OptimizerCache::get_path();
        $mask = $dir . $prefix;
        array_map('unlink', glob($mask));
    }


    /**
     * Delete cache file.
     *
     * @param string $file
     *
     * @return bool
     */
    public static function delete_cache_file($file = '')
    {
        $file = OptimizerCache::get_path() . $file;
        if (is_file($file)) {
            $delete = @unlink($file);
            if ($delete) {
                return true;
            }
        }

        return false;
    }


    public static function get_page_url()
    {
        if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                $link = "https";
            } else {
                $link = "http";
            }
            // Here append the common URL characters.
            $link .= "://";
            // Append the host(domain name, ip) to the URL.
            $link .= sanitize_text_field( $_SERVER['HTTP_HOST'] );
            // Append the requested resource location to the URL
            $link .= sanitize_text_field( $_SERVER['REQUEST_URI'] );

            return $link;
        }

        return "";
    }

    public static function is_pagespeed_enabled()
    {
        return defined('TW_NGX_PAGESPEED') && TW_NGX_PAGESPEED === 'enabled';
    }

    public static function is_pagespeed_lazyload_enabled()
    {
        return self::is_pagespeed_enabled() && defined('TW_NGX_PAGESPEED_FILTERS') && in_array('lazyload_images', TW_NGX_PAGESPEED_FILTERS, true);
    }

    public static function is_pagespeed_image_optimization_enables()
    {
        return self::is_pagespeed_enabled() && defined('TW_NGX_PAGESPEED_FILTERS') &&
            (
                in_array('convert_gif_to_png', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('recompress_png', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('convert_png_to_jpeg', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('convert_jpeg_to_progressive', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('recompress_jpeg', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('convert_jpeg_to_webp', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('convert_to_webp_lossless', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('convert_to_webp_animated', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('recompress_webp', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('inline_images', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('resize_images', TW_NGX_PAGESPEED_FILTERS, true)
            );
    }

    public static function is_pagespeed_js_defer_enabled()
    {
        return self::is_pagespeed_enabled() && defined('TW_NGX_PAGESPEED_FILTERS') && in_array('defer_javascript', TW_NGX_PAGESPEED_FILTERS, true);
    }


    public static function purge_pagespeed_cache()
    {
        if (OptimizerUtils::is_pagespeed_enabled()) {
            $url = rtrim(get_home_url(), '/') . '/*';
            $response = wp_remote_request($url, array('method' => 'PURGE'));
        }

        return true;

    }

    /**
     * remove markers that serve_different_sizes_for_bg_image added for backgrounds
     *
     * @param $css
     *
     * @return string|string[]
     */
    public static function removeBgImageMarkers($css)
    {
        return str_replace(
            self::OPTIMIZED_BG_MARKER,
            '',
            str_replace(
                self::OPTIMIZED_BG_MARKER . self::SVG_DATA . "#}",
                '',
                $css
            )
        );
    }


    /**
     * Run a match on the array's keys
     *
     * @param     $pattern
     * @param     $input
     * @param int $flags
     *
     * @return array
     */
    public static function preg_grep_keys($pattern, $input, $flags = 0)
    {
        return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
    }


    /**
     * Checks if the current request is a WP REST API request.
     *
     * Case #1: After WP_REST_Request initialisation
     * Case #2: Support "plain" permalink settings
     * Case #3: It can happen that WP_Rewrite is not yet initialized,
     *          so do this (wp-settings.php)
     * Case #4: URL Path begins with wp-json/ (your REST prefix)
     *          Also supports WP installations in subfolders
     *
     * @returns boolean
     */
    public static function is_rest()
    {
        if ((defined('REST_REQUEST') && REST_REQUEST) // (#1)
            || (isset($_GET['rest_route']))) // (#2)
            return true;
        // (#3)
        global $wp_rewrite;
        if ($wp_rewrite === null)
            $wp_rewrite = new WP_Rewrite();

        // (#4)
        $rest_url = wp_parse_url(trailingslashit(rest_url()));
        $current_url = wp_parse_url(add_query_arg(array()));

        return strpos($current_url['path'], $rest_url['path'], 0) === 0;
    }

    /**
     * Get parameters from a URL string
     *
     * @param $url
     * @param $name
     *
     * @return bool|mixed
     */
    public static function get_url_query($url, $name)
    {
        $url_params = wp_parse_url($url);
        if (is_array($url_params) && isset($url_params["query"])) {
            parse_str($url_params["query"], $query_array);
            if (is_array($query_array) && isset($query_array[$name])) {
                $url_param = $query_array[$name];

                return $url_param;
            }
        }

        return false;
    }

    /**
     * Remove domain part of a url
     *
     * @param $url
     *
     * @return string
     */
    public static function remove_domain_part($url)
    {
        $urlparts = wp_parse_url($url);
        $extracted = "";
        if (isset($urlparts['path'])) {
            $extracted = $urlparts['path'];
        }
        if (isset($urlparts['query'])) {
            $extracted .= '?' . $urlparts['query'];
        }

        return $extracted;
    }

    public static function get_javascipt_type($tag)
    {
        preg_match('/type="(.+?)"/', $tag, $matches);

        return isset($matches[1]) ? $matches[1] : 'text/javascript';
    }

    /**
     * Injects/replaces the given payload markup into `$this->content`
     * at the specified location.
     * If the specified tag cannot be found, the payload is appended into
     * $this->content along with a warning wrapped inside <!--noptimize--> tags.
     *
     * @param        $content
     * @param string $payload Markup to inject.
     * @param array  $where   Array specifying the tag name and method of injection.
     *                        Index 0 is the tag name (i.e., `</body>`).
     *                        Index 1 specifies ˛'before', 'after' or 'replace'. Defaults to 'before'.
     *
     * @return string
     */
    public static function inject_in_html($content, $payload, $where)
    {
        $position = self::strpos($content, $where[0]);
        if (false !== $position) {
            // Found the tag, setup content/injection as specified.
            if ('after' === $where[1]) {
                $replacement = $where[0] . $payload;
            } else if ('replace' === $where[1]) {
                $replacement = $payload;
            } else {
                $replacement = $payload . $where[0];
            }
            // Place where specified.
            $content = self::substr_replace($content, $replacement, $position, // Using plain strlen() should be safe here for now, since
                // we're not searching for multibyte chars here still...
                strlen($where[0]));
        } else {
            // Couldn't find what was specified, just append and add a warning.
            $content .= $payload;
        }

        return $content;
    }


    public static function isJson($string)
    {
        return is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string)));
    }

    public static function findArr($arr, $field, $value)
    {
        foreach ($arr as $key => $inner_arr) {
            if ($inner_arr[$field] === $value)
                return $arr[$key];
        }

        return false;
    }

    public static function get_worker_script(){
        $critical = new OptimizerCriticalCss();
        return '
        <script data-pagespeed-no-defer ' . OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE . ' type="text/javascript">
            let two_page_critical_data = {
                "critical_enabled":"'.$critical->critical_enabled.'",
                "use_uncritical":"'.$critical->critical_enabled.'",
                "uncritical_load_type":"'.$critical->uncritical_load_type.'"
            }
        </script>
         <script data-pagespeed-no-defer ' . OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE . ' id="two_worker" type="javascript/worker">
            '.trim(JSMin::minify(file_get_contents(TENWEB_SO_PLUGIN_DIR.'includes/external/js/two_worker.js'))).'
        </script>
        '.trim(JSMin::minify('<script data-pagespeed-no-defer ' . OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE . ' type="text/javascript">
                                two_worker_styles_list = [];
                                
                                var two_script_list = typeof two_worker_data_js === "undefined" ? [] : two_worker_data_js.js;
                                var two_css_list = typeof two_worker_data_css === "undefined" ? [] : two_worker_data_css.css;
                                var two_fonts_list = typeof two_worker_data_font === "undefined" ? [] : two_worker_data_font.font;
                                var wcode = new Blob([
                                                        document.querySelector("#two_worker").textContent
                                                      ], { type: "text/javascript" });

                                var two_worker = new Worker(window.URL.createObjectURL(wcode) );
                                var two_worker_data = {"js" : two_script_list, "css": two_css_list , "font":two_fonts_list}
                                two_worker.postMessage(two_worker_data);
                                two_worker.addEventListener("message",function(e) {
                                  var data = e.data; 
                                  if(data.type === "css" && data.status === "ok"){
                                        if(window.two_page_loaded){
                                            two_connect_style(data);
                                        }else{
                                            two_worker_styles_list.push(data);
                                        } 
                                  } else if(data.type === "js" && data.status === "ok" ){
                                     two_script_list[data.id].old_url = two_script_list[data.id].url; 
                                     two_script_list[data.id].url = data.url; 
                                  } else if (data.type === "css" && data.status === "error") {
                                      console.log("error in fetching, connecting style now")
                                      two_connect_failed_style(data)
                                 } else if(data.type === "font"){
                                      two_connect_font(data);
                                 }
                                });
                                
                                function UpdateQueryString(key, value, url) {
                                    if (!url) url = window.location.href;
                                    var re = new RegExp("([?&])" + key + "=.*?(&|#|$)(.*)", "gi"),
                                        hash;
                                
                                    if (re.test(url)) {
                                        if (typeof value !== "undefined" && value !== null) {
                                            return url.replace(re, "$1" + key + "=" + value + "$2$3");
                                        } 
                                        else {
                                            hash = url.split("#");
                                            url = hash[0].replace(re, "$1$3").replace(/(&|\?)$/, "");
                                            if (typeof hash[1] !== "undefined" && hash[1] !== null) {
                                            url += "#" + hash[1];
                                        }
                                            return url;
                                        }
                                }
                                else {
                                    if (typeof value !== "undefined" && value !== null) {
                                        var separator = url.indexOf("?") !== -1 ? "&" : "?";
                                        hash = url.split("#");
                                        url = hash[0] + separator + key + "=" + value;
                                        if (typeof hash[1] !== "undefined" && hash[1] !== null) {
                                            url += "#" + hash[1];
                                        }
                                            return url;
                                        }
                                        else {
                                        return url;
                                    }
                                    }
                                }
          
                                function two_connect_failed_style(data) {
                                    var link  = document.createElement("link");
                                    link.className = "fallback_two_worker";
                                    link.rel  = "stylesheet";
                                    link.type = "text/css";
                                    
                                    link.href = data.url;
                                    link.media = "none";
                                    link.onload =  function () { if(this.media==="none"){ if (data.media) {this.media=data.media;}else{this.media="all";}console.log(data.media);} if(data.connected_length == data.length && typeof two_replace_backgrounds != "undefined"){two_replace_backgrounds();};};                                
                                    document.getElementsByTagName("head")[0].appendChild(link);
                                    if(data.connected_length == data.length){
                                        two_replace_backgrounds();
                                    }
                                }
                              
                                function two_connect_style(data) {
                                    var link  = document.createElement("link");
                                    link.className = "loaded_two_worker";
                                    link.rel  = "stylesheet";
                                    link.type = "text/css";
                                    link.href = data.url;
                                    link.media = data.media;
                                    link.onload = function () {if(data.connected_length == data.length && typeof two_replace_backgrounds != "undefined"){two_replace_backgrounds();};};
                                    document.getElementsByTagName("head")[0].appendChild(link);
                                    
                                }
                                
                                
                                var two_event ;
                                
                                function two_connect_script(i) { 
                                  if(i === 0){
                                      two_event = event;
                                  }
                                  if(typeof two_script_list[i] !== "undefined"){
                                      var script = document.createElement("script");
                                      script.type = "text/javascript";
                                      script.async = false;
                                   
                                      if(two_script_list[i].inline){
                                           var js_code =atob(two_script_list[i].code);
                                            var blob = new Blob([js_code], {type : "text/javascript"});
                                            two_script_list[i].url = URL.createObjectURL(blob);
  
                                          script.className = "loaded_two_worker_js two_js_inline";
                                      }else { 
                                          script.className = "loaded_two_worker_js";
                                      }
                                      if(typeof two_script_list[i].id !== "undefined" && two_script_list[i].id !== ""){
                                          script.setAttribute("id", two_script_list[i].id);
                                      }
                                      
                                      for(let attr_name in two_script_list[i]["attributes"]){
                                        script.setAttribute(attr_name, two_script_list[i]["attributes"][attr_name]);
                                      }
                                      
                                      if(typeof two_script_list[i].url != "undefined"){
                                          script.dataset.src= two_script_list[i].url;
                                          document.getElementsByTagName("head")[0].appendChild(script);
                                      }
                                      i++;
                                      two_connect_script(i);  
                                      
                                  } else {
                                   document.querySelectorAll(".loaded_two_worker_js").forEach((elem) => {
                                       if (elem.dataset.src){
                                              elem.setAttribute("src",elem.dataset.src);
                                       }
                                   });
                                  }
                                }
                                function two_connect_font(data){ 
                                    let font_face = data.font_face;
                                    
                                    if(font_face.indexOf("font-display")>=0){
                                        const regex = /font-display:[ ]*[a-z]*[A-Z]*;/g;
                                        while ((m = regex.exec(font_face)) !== null) {
                                            if (m.index === regex.lastIndex) {
                                                regex.lastIndex++;
                                            }
                                            
                                            m.forEach((match, groupIndex) => {
                                                console.log(match);
                                                font_face.replace(match, "font-display: swap;");
                                            });
                                        }
                                    }else{
                                        font_face = font_face.replace("}", ";font-display: swap;}");
                                    }
                                    if(typeof data.main_url != "undefined"){
                                        font_face = font_face.replace(data.main_url, data.url);
                                    }
                                    var newStyle = document.createElement("style");
                                    newStyle.className = "two_critical_font";
                                    newStyle.appendChild(document.createTextNode(font_face));
                                    document.head.appendChild(newStyle);
                                }
                                 let connect_stile_timeout = setInterval(function (){
                                     console.log(window.two_page_loaded);
                                     if(window.two_page_loaded){
                                        clearInterval(connect_stile_timeout);
                                        two_worker_styles_list.forEach(function(item, index){
                                              two_connect_style(item);
                                        });
                                        two_worker_styles_list = [];
                                     }
                                },500);  
                      </script>'));
    }

    public static function clear_iframe_src($content){
        if (preg_match_all('#<iframe[^>]*src[^>]*>#Usmi', $content, $matches)) {
            // only used is image optimization is NOT active but lazyload is.
            foreach ($matches[0] as $tag) {
                $new_tag = str_replace(' src=', ' src="" data-two_src=', $tag);
                $content = str_replace($tag, $new_tag, $content);
            }
        }
        $tags_to_remove = array(
          array(
            'tag' => 'link',
            'attribute' => 'media',
            'value' => 'print',
          ),
          array(
            'tag' => 'style',
            'attribute' => 'media',
            'value' => 'print',
          ),
          array(
            'tag' => 'script',
            'attribute' => 'src',
            'value' => 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js',
          ),
        );
        $regex_to_remove_tags = array();
        foreach ( $tags_to_remove as $tag ) {
          $regex_to_remove_tags[] = '(<' . $tag[ 'tag' ] . '[^>]*' . $tag[ 'attribute' ] . '=[\'"]' . $tag[ 'value' ] . '[\'"].*\/(' . $tag[ 'tag' ] . ')?>)';
        }
        if (preg_match_all('#' . implode( '|', $regex_to_remove_tags ) . '#Usmi', $content, $matches)) {
            foreach ($matches[0] as $tag) {
                $content = str_replace($tag, '', $content);
            }
        }
        $content = OptimizerUtils::inject_in_html($content, '<script data-pagespeed-no-defer two-no-delay src="' . plugins_url('external/js/two_elementor_video_to_iframe.js', __FILE__) . '"></script>', array('</body>', 'before'));
        /*this code to remove all iframes */
        $content = OptimizerUtils::inject_in_html($content, '<script data-pagespeed-no-defer two-no-delay>    
                                                                                                           const two_frames = window.frames;
                                                                                                            for (let i = 0; i < two_frames.length; i++) {
                                                                                                                two_frames[i].stop();
                                                                                                            }
                                                                                                           let clear_iframe_interval = setInterval(function(){
                                                                                                                   const two_dom_frames = window.frames;
                                                                                                                    for (let i = 0; i < two_dom_frames.length; i++) {
                                                                                                                        two_dom_frames[i].stop();
                                                                                                                    }
                                                                                                           },20);  
                                                                                                           setTimeout(function(){
                                                                                                                  clearInterval(clear_iframe_interval);
                                                                                                           },2000);  
                                                                                                            
                                                                        </script>', array('</body>', 'before'));

        return $content;
    }

    public static function split_css_to_arr($code){
        $return_data = array();
        $return_data["font"] = $code;
        preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $code, $match);
        if(isset($match[0]) && !empty($match[0])){
            $return_data["urls"] = $match[0];
        }
        return $return_data;
    }

    public static function get_default_critical_pages($status = false) {
      // This theme works better without critical. There is a CLS with uncritical loaded.
      $theme = wp_get_theme();
      if ( 'Twenty Twenty-Two' == $theme->name ) {
        $criticalPages = array();
      }
      else {
        global $TwoSettings;
        $homeUrl = get_home_url();
        $pageId = 'front_page';
        $waitUntil = 'domcontentloaded';
        $loadType = 'async';
        $criticalPages = array(
          'front_page' => array(
            'title' => 'Home',
            'url' => $homeUrl,
            'id' => $pageId,
            'sizes' => $TwoSettings->get_settings("two_critical_sizes"),
            'load_type' => $loadType,
            'wait_until' => $waitUntil,
          )
        );
        if($status){
            $criticalPages["front_page"]["status"] = "in_progress";
        }
      }
      return $criticalPages;
    }

    /**
     * @param $regeneration_mode string 'front_page' will generate for front page only
     * @param bool $rightAfterConnect
     * @return void
     */
    public static function regenerate_critical( $regeneration_mode = 'front_page', $rightAfterConnect = false ){
        global $TwoSettings;

        $two_critical_pages = OptimizerUtils::getCriticalPages();
        $url_query = $TwoSettings->get_settings("two_critical_url_args");

        if (empty($two_critical_pages)) {
            $homeUrl = get_home_url();
            $pageId = 'front_page';
            $waitUntil = 'domcontentloaded';
            $data = array(
                'action' => 'two_critical',
                'data' => array(
                    'page_url' => $homeUrl,
                    'page_id' => $pageId,
                    'page_sizes' => $TwoSettings->get_settings("two_critical_sizes"),
                    'wait_until' => $waitUntil,
                    'url_query' => $TwoSettings->get_settings("two_critical_url_args"),
                    'task' => 'generate',
                    'newly_connected_website' => $rightAfterConnect,
                ),
                'two_critical_sizes' => $TwoSettings->get_settings("two_critical_sizes"),
                'two_critical_pages' => self::get_default_critical_pages(true),
            );
            OptimizerCriticalCss::generateCriticalCSS($data);

            if(OptimizerUtils::is_wpml_active()) {
              OptimizerUtils::generate_wpml_home_pages_critical_css($data);
            }


        } else {
            $regenerate_data = get_transient("two_regenerate_critical_data");
            if( is_array( $regenerate_data ) && !empty( $regenerate_data ) ) {
                $two_critical_pages = $regenerate_data;
            } else {
                if ( 'front_page' == $regeneration_mode ) {
                  // Invalidate all critical css.
                  self::update_critical_statuses($two_critical_pages, "not_started");
                  // Regenerate front page only.
                  foreach ($two_critical_pages as $key=>$two_page) {
                      if ( 'front_page' != $key ) {
                          unset($two_critical_pages[$key]);
                      }
                  }
                }
                self::update_critical_statuses($two_critical_pages, "in_progress");
            }
            $two_critical_sizes = $TwoSettings->get_settings("two_critical_sizes");
            foreach ($two_critical_pages as $key=>$two_page){
                unset($two_critical_pages[$key]);
                $critical_sizes = array();
                if(isset($two_page["sizes"]) && is_array($two_page["sizes"])){
                    foreach ($two_page["sizes"] as $size_id){
                        if(is_array($size_id) && isset($size_id["uid"])){
                            $size_id = $size_id["uid"];
                        }
                        if(isset($two_critical_sizes[$size_id])){
                            $critical_sizes[] = $two_critical_sizes[$size_id];
                        }
                    }
                }
                $data = array(
                    'data'=>array(
                        'page_url' => $two_page["url"],
                        'page_id' => $two_page["id"],
                        'page_sizes' => $critical_sizes,
                        'wait_until' => $two_page["wait_until"],
                        'url_query' => $url_query,
                        'task' => 'generate',
                        'newly_connected_website' => $rightAfterConnect,
                    ),
                );
                if(isset($two_page["use_uncritical"])){
                    $data["data"]["use_uncritical"] = $two_page["use_uncritical"];
                }
                OptimizerCriticalCss::generateCriticalCSS($data);
                $two_critical_pages_count = count($two_critical_pages);
                set_transient("two_regenerate_critical_data", $two_critical_pages, 60*$two_critical_pages_count);
                break;
            }
        }
    }



    public static function update_critical_statuses($two_critical_pages, $status){
        global $TwoSettings;
        foreach ($two_critical_pages as $key=>$two_page){
            $two_critical_pages[$key]["status"] = $status;
        }
        $TwoSettings->update_setting("two_critical_pages", $two_critical_pages);
    }

    public static function init_defines()
    {
        global $TwoSettings;
        if (!defined('TWO_LAZYLOAD')) {
            define('TWO_LAZYLOAD', true);
        }

        if (!defined('TWO_WP_SITE_URL')) {
            if (function_exists('domain_mapping_siteurl')) {
                define('TWO_WP_SITE_URL', domain_mapping_siteurl(get_current_blog_id()));
            } else {
                define('TWO_WP_SITE_URL', site_url());
            }
        }
        if (!defined('TWO_WP_CONTENT_URL')) {
            if (function_exists('get_original_url')) {
                define('TWO_WP_CONTENT_URL', str_replace(get_original_url(TWO_WP_SITE_URL), TWO_WP_SITE_URL, content_url()));
            } else {
                define('TWO_WP_CONTENT_URL', content_url());
            }
        }
        if (!defined('TWO_WP_CONTENT_NAME')) {
            define('TWO_WP_CONTENT_NAME', '/' . wp_basename(WP_CONTENT_DIR));
        }
        if (!defined('TWO_WP_ROOT_URL')) {
            define('TWO_WP_ROOT_URL', str_replace(TWO_WP_CONTENT_NAME, '', TWO_WP_CONTENT_URL));
        }
        if (!defined('TWO_CACHE_URL')) {
            if (is_multisite()) {
                $blog_id = get_current_blog_id();
                define('TWO_CACHE_URL', TWO_WP_CONTENT_URL . TENWEB_SO_CACHE_CHILD_DIR . $blog_id . '/');
            } else {
                define('TWO_CACHE_URL', TWO_WP_CONTENT_URL . TENWEB_SO_CACHE_CHILD_DIR);
            }
        }
        if (!defined('WP_ROOT_DIR')) {
            define('WP_ROOT_DIR', substr(WP_CONTENT_DIR, 0, strlen(WP_CONTENT_DIR) - strlen(TWO_WP_CONTENT_NAME)));
        }
        if (!defined('TWO_HASH')) {
            define('TWO_HASH', wp_hash(TWO_CACHE_URL));
        }
        if (!defined('TWO_CACHE_NOGZIP')) {
          $two_gzip = $TwoSettings->get_settings( "two_gzip" );
          if ( !TENWEB_SO_HOSTED_ON_10WEB && $two_gzip === "on" ) {
            define( 'TWO_CACHE_NOGZIP', false );
          }
          else {
            define( 'TWO_CACHE_NOGZIP', true );
          }
        }
        if (!defined('TWO_CACHEFILE_PREFIX')) {
            define('TWO_CACHEFILE_PREFIX', 'two_');
        }
    }

    public static function flushCloudflareCache($postId = null)
    {
        if (class_exists('\CF\WordPress\Hooks')) {
            $cloudflareHooks = new \CF\WordPress\Hooks();
            if (is_int($postId )) {
                $cloudflareHooks->purgeCacheByRelevantURLs($postId);
            } else {
                $cloudflareHooks->purgeCacheEverything();
            }
        }
    }

    public static function get_tenweb_connection_link( $endpoint = 'sign-up', $args = [] ){
      // copied from manager.py
      $return_url = get_admin_url() . 'options-general.php';
      if(is_multisite()) {
        $return_url = network_admin_url() . 'options-general.php';
      }

      $return_url_args = array('page' => 'two_settings_page');
      $register_url_args = array(
        'site_url' => urlencode(get_site_url()),
        'utm_source' => '10webspeedoptimizer',
        'from_plugin' => self::FROM_PLUGIN,
        'utm_medium' => 'freeplugin',
        'nonce' => wp_create_nonce('two_10web_connection'),
        'subscr_id' => TENWEB_SO_FREE_SUBSCRIPTION_ID
      );

      if(!empty($args)) {
        $register_url_args = $register_url_args + $args;
        $return_url_args = $return_url_args + $args;
      }

      $register_url_args['return_url'] = urlencode(add_query_arg($return_url_args, $return_url));

      $plugin_from = get_site_option("tenweb_manager_installed");
      if($plugin_from !== false) {
        $plugin_from = json_decode($plugin_from, true);
        if(is_array($plugin_from) && reset($plugin_from) !== false) {
          $register_url_args['plugin_id'] = reset($plugin_from);
          if(isset($plugin_from["type"])) {
            $register_url_args['utm_source'] = $plugin_from["type"];
          }
        }
      }

      $url = add_query_arg($register_url_args, TENWEB_DASHBOARD . '/' . $endpoint . '/');
      return $url;
    }
    public static function getCriticalPages() {
      global $TwoSettings;

      $two_critical_pages_from_options = $TwoSettings->get_settings( "two_critical_pages" );
      $two_critical_pages = self::get_meta_values( 'two_critical_pages' );
      if ( $two_critical_pages_from_options ) {
        $two_critical_pages = array_replace( $two_critical_pages_from_options, $two_critical_pages );
      }

      return $two_critical_pages;
    }

    public static function stripslashes_deep($value){
      // copied from wp-includes/formatting.php
      return self::map_deep( $value, function($value){
        return is_string( $value ) ? stripslashes( $value ) : $value;
      });

    }

    public static function map_deep($value, $callback){
      // copied from wp-includes/formatting.php

      if(is_array($value)) {
        foreach($value as $index => $item) {
          $value[$index] = self::map_deep($item, $callback);
        }
      } elseif(is_object($value)) {
        $object_vars = get_object_vars($value);
        foreach($object_vars as $property_name => $property_value) {
          $value->$property_name = self::map_deep($property_value, $callback);
        }
      } else {
        $value = call_user_func($callback, $value);
      }
      return $value;
    }

    public static function get_meta_values( $key = '' ) {
      if ( empty( $key ) ) {
        return null;
      }
      global $wpdb;

      $query = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = %s", $key ) );
      $result = array();
      foreach ( $query as $row ) {
        $result[ $row->post_id ] = unserialize( $row->meta_value );
      }

      return $result;
    }

  public static function check_if_hosted_website(){
    if (is_file(WPMU_PLUGIN_DIR . '/10web-manager/10web-manager.php')) {
      return true;
    }

    return false;
  }

  /* WPML functions*/
  public static function get_wpml_home_urls(){
    if(!OptimizerUtils::is_wpml_active()) {
      return [];
    }

    $front_page_id = get_option('page_on_front');

    // if $front_page_id is empty or 0, it means home page is archive page and there is no translation for that page
    if(!$front_page_id) {
      return [];
    }

    $element_id = $front_page_id;
    $element_type = get_post_type($front_page_id);

    $home_pages = [];
    $languages = apply_filters('wpml_active_languages', "");

    foreach($languages as $lang_code => $language_data) {
      $post_id = apply_filters('wpml_object_id', $element_id, $element_type, false, $lang_code);
      if(!$post_id){
        continue;
      }
      $home_pages[$lang_code] = [
        "post_id" => $post_id,
        "permalink" => get_permalink($post_id),
        "title" => get_the_title($post_id)
      ];
    }

    return $home_pages;
  }

  public static function add_wpml_home_pages_into_critical_pages($critical_pages=null, $home_url=null){
    /**
     * The function added home pages generated by WPML into critical_pages list, if home page is in that list. The
     * function doesn't generate critical css.
     * */

    global $TwoSettings;

    if($critical_pages === null){
      $critical_pages = OptimizerUtils::getCriticalPages();
    }

    if($home_url === null && $critical_pages["front_page"]){
      $home_url = $critical_pages["front_page"]["url"];
    }

    if(!$home_url){
      return $critical_pages;
    }

    foreach(OptimizerUtils::get_wpml_home_urls() as $lang_code => $post_data) {
      if(rtrim($post_data["permalink"], "/") === rtrim($home_url, "/")) {
        continue;
      }

      if(isset($critical_pages[$post_data["id"]])){
        continue;
      }

      $page_data = $critical_pages["front_page"];
      $page_data["title"] = $post_data["title"];
      $page_data["url"] = $post_data["permalink"];
      $page_data["id"] = $post_data["post_id"];
      $critical_pages[$post_data["post_id"]] = $page_data;
    }

    $TwoSettings->update_setting("two_critical_pages", $critical_pages);

    return $critical_pages;
  }

  public static function generate_wpml_home_pages_critical_css($data){
    self::add_wpml_home_pages_into_critical_pages();

    $wpml_data = ["data" => $data["data"]];
    $home_page_url = $data["data"]["page_url"];
    foreach(self::getCriticalPages() as $page){
      if($page["url"] == $home_page_url){
        continue;
      }

      $is_wpml_page = false;
      foreach(self::get_wpml_home_urls() as $wpml_page) {
        if($wpml_page["post_id"] == $page["id"]){
          $is_wpml_page = true;
          break;
        }
      }

      if($is_wpml_page === false){
        continue;
      }

      $wpml_data["data"]["page_url"] = $page["url"];
      $wpml_data["data"]["page_id"] = $page["id"];
      OptimizerCriticalCss::generateCriticalCSS($wpml_data);
    }
  }

  public static function get_wpml_post_flag_url($post_id){
    if($post_id == "front_page") {
        $post_id = get_option('page_on_front');
    }

    if(!$post_id){
      $lang_code = apply_filters( 'wpml_default_language', null );
    }else{
      $post_language_details = apply_filters('wpml_post_language_details', NULL, $post_id);
      $lang_code = $post_language_details["language_code"];
    }

    return plugins_url("sitepress-multilingual-cms/res/flags/" . $lang_code . '.png');
  }

  public static function is_wpml_active(){
    return defined('ICL_SITEPRESS_VERSION');
  }


  public static function get_modes($name = null){
        if(isset($name)){
            return self::MODES[$name];
        }
        $modes = self::MODES;
        $global_mode = get_option("two_default_mode");
        if(is_array($global_mode)){
            $modes["global"] = $global_mode;
        }
        return $modes;
  }

  public static function testWebPDelivery() {
      $requestUrl = TENWEB_SO_URL . '/test/webp_test.jpg';
      $requestArgs = [
        'headers' => [
          'ACCEPT' => 'image/webp'
        ]
      ];
      global $TwoSettings;
      $wpResult = wp_remote_get($requestUrl, $requestArgs);
      if ( !is_wp_error( $wpResult ) ) {
        if ( isset( $wpResult['headers']['content-type'] ) && 'image/webp' === $wpResult['headers']['content-type'] ) {
          $TwoSettings->update_setting("two_webp_delivery_working", "1");
          return true;
        }
      }
      $TwoSettings->update_setting("two_webp_delivery_working", "0");
      return false;
    }

  public static function clear_third_party_cache() {
    global $wp_fastest_cache, $kinsta_cache, $nginx_purger;

    // if W3 Total Cache is being used, clear the cache
    if ( function_exists( 'w3tc_pgcache_flush' ) ) {
      w3tc_pgcache_flush();
    }
    // if WP Super Cache is being used, clear the cache
    else if ( function_exists( 'wp_cache_clean_cache' ) ) {
      global $file_prefix, $supercachedir;
      if ( empty( $supercachedir ) && function_exists( 'get_supercache_dir' ) ) {
        $supercachedir = get_supercache_dir();
      }
      wp_cache_clean_cache( $file_prefix );
    }
    else if ( class_exists( 'WpeCommon' ) ) {
      //be extra careful, just in case 3rd party changes things on us
      if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
        \WpeCommon::purge_memcached();
      }
      if ( method_exists( 'WpeCommon', 'clear_maxcdn_cache' ) ) {
        \WpeCommon::clear_maxcdn_cache();
      }
      if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
        \WpeCommon::purge_varnish_cache();
      }
    }
    else if ( method_exists( 'WpFastestCache', 'deleteCache' ) && !empty( $wp_fastest_cache ) ) {
      $wp_fastest_cache->deleteCache( true );
    }
    else if ( class_exists( '\Kinsta\Cache' ) && !empty( $kinsta_cache ) ) {
      $kinsta_cache->kinsta_cache_purge->purge_complete_caches();
    }
    else if ( class_exists( '\WPaaS\Cache' ) ) {
      if ( !\WPaaS\Cache::has_ban() ) {
        remove_action( 'shutdown', [ '\WPaaS\Cache', 'purge' ], PHP_INT_MAX );
        add_action( 'shutdown', [ '\WPaaS\Cache', 'ban' ], PHP_INT_MAX );
      }
    }
    else if ( class_exists( 'WP_Optimize' ) && defined( 'WPO_PLUGIN_MAIN_PATH' ) ) {
      if (!class_exists('WP_Optimize_Cache_Commands')) include_once(WPO_PLUGIN_MAIN_PATH . 'cache/class-cache-commands.php');

      if ( class_exists( 'WP_Optimize_Cache_Commands' ) ) {
        $wpoptimize_cache_commands = new \WP_Optimize_Cache_Commands();
        $wpoptimize_cache_commands->purge_page_cache();
      }
    }
    else if ( class_exists( 'Breeze_Admin' ) ) {
      do_action('breeze_clear_all_cache');
    }
    else if ( defined( 'LSCWP_V' ) ) {
      do_action( 'litespeed_purge_all' );
    }
    else if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
      sg_cachepress_purge_cache();
    }
    else if ( class_exists( 'autoptimizeCache' ) ) {
      \autoptimizeCache::clearall();
    }
    else if ( class_exists( 'Cache_Enabler' ) ) {
      \Cache_Enabler::clear_total_cache();
    }
    else if ( defined( 'NGINX_HELPER_BASEPATH' ) && !empty( $nginx_purger ) ) {
      $nginx_purger->purge_all();
    }
    else if ( function_exists( 'rocket_clean_domain' ) ) {
      rocket_clean_domain();
    }
  }

  public static function triggerPostOptimizationTasks()
    {
        global $TwoSettings;
        $domain_id = get_site_option('tenweb_domain_id');
        $access_token = get_site_option(TENWEB_PREFIX . '_access_token');
        if($access_token && $domain_id) {
            $response = wp_remote_post( TENWEB_SO_CRITICAL_URL."/v1/workspaces/performance/".$domain_id."/post-optimization-tasks", array(
                'timeout'     => 89,
                'redirection' => 15,
                'blocking'    => true,
                'headers'     => array(
                    "accept" => "application/x.10webperformance.v1+json",
                    "authorization" => "Bearer ".$access_token,
                ),
                'body'        => array(
                    'notification_id' => sanitize_text_field( $_POST['notification_id'] ),
                    'has_excluded_slider' => $TwoSettings->get_settings('two_exclude_rev') || $TwoSettings->get_settings('two_exclude_slider_by_10web')
                ),
                'cookies'     => array()
            ));
            update_option("two_critical_data_import_response_".time(), [
                $response['body'],
                wp_remote_retrieve_response_code($response)
            ]);
        }
    }


  public static function set_global_mode()
  {
      global $TwoSettings;
      $modes = self::get_modes();
      $two_critical_status = $TwoSettings->get_settings("two_critical_status");
      $two_delay_all_js_execution = $TwoSettings->get_settings("two_delay_all_js_execution");

      if ($two_critical_status == "true" && $two_delay_all_js_execution == "on") {
          $mode = "extreme";
      } else if ($two_critical_status == "true" && $two_delay_all_js_execution != "on") {
          $mode = "balanced";
      } else if ($two_critical_status != "true" && $two_delay_all_js_execution === "on") {
          $mode = "strong";
      } else {
          $mode = "standard";
      }
      if (isset($modes[$mode])) {
          foreach ($modes[$mode] as $key => $val) {
              if (isset($settings_names[$key])) {
                  $TwoSettings->update_setting($key, $val);
              } else if ($key === "critical_enabled") {
                  if ($val) {
                      $TwoSettings->update_setting("two_critical_status", "true");
                  } else {
                      $TwoSettings->update_setting("two_critical_status", "");
                  }
              }
          }
          update_option("two_default_mode", $modes[$mode]);
      }
  }


  public static function get_post_id($page_url = null){
      global $post;
      $home_url = rtrim(get_home_url(), "/" );
      $page_url = rtrim($page_url, "/" );
      $id = 0;
      if(!empty($page_url)){
          if($home_url === $page_url){
              return 'front_page';
          }
          $id = url_to_postid($page_url);
          if($id === 0 && class_exists( 'WooCommerce' )){
              $shop_page_id = wc_get_page_id( 'shop' );
              $shop_page_url = rtrim(get_permalink( wc_get_page_id( 'shop' ) ), "/");
              if($shop_page_url === $page_url){
                  $id = $shop_page_id;
              }
          }
          if($id===0){
              $page_for_posts = get_option( 'page_for_posts' );
              $post_page_id = intval($page_for_posts);
              $post_page_url = rtrim(get_permalink($post_page_id),"/");
              if($post_page_url === $page_url){
                  $id = $post_page_id;
              }
          }
      }
      if($id === 0){
          if (is_front_page()) {
              $id = 'front_page';
          }elseif (class_exists( 'WooCommerce' ) && is_shop()){
              $id = wc_get_page_id( 'shop' );
          }
          elseif (is_home()){
              $page_for_posts = get_option( 'page_for_posts' );
              $id = intval($page_for_posts);
          }
          else if (!empty($post)) {
              $id = $post->ID;
          }
      }
      return $id;
  }
  public static function two_update_subscription(){
        $tenweb_subscription_id = false;
        $domain_id = get_site_option('tenweb_domain_id');
        $access_token = get_site_option(TENWEB_PREFIX . '_access_token');
        $workspace_id = get_site_option(TENWEB_PREFIX . '_workspace_id');
        if(isset($access_token, $domain_id, $workspace_id) && !empty($access_token) && !empty($domain_id) && !empty($workspace_id)){
            $response = wp_remote_post( TENWEB_SO_CRITICAL_URL."/v1/workspaces/".$workspace_id."/domains/".$domain_id."/get_subscription", array(
                'timeout'     => 5,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking'    => true,
                'headers'     => array(
                    "accept" => "application/x.10webperformance.v1+json",
                    "authorization" => "Bearer ".$access_token,
                ),
                'body'        => array(),
                'cookies'     => array()
            ));
            if ( !is_wp_error( $response )  && isset($response["body"], $response["response"]["code"]) && $response["response"]["code"] === 200) {
                $response_body = json_decode($response["body"], true);
                if(isset($response_body["data"]["subscription_id"]) && $response_body["data"]["success"]){
                    $subscription_id = $response_body["data"]["subscription_id"];
                    $tenweb_subscription_id = $subscription_id;
                    set_transient(TENWEB_PREFIX . '_subscription_id', $subscription_id, 12 * HOUR_IN_SECONDS);
                }
            }else{
                set_transient(TENWEB_PREFIX . '_subscription_id', "0", 1 * HOUR_IN_SECONDS);
            }
            return $tenweb_subscription_id;
        }
  }

  public static function two_critical_status($page_id = false){
      global $TwoSettings;
      $two_critical_pages = self::getCriticalPages();
      if(is_array($two_critical_pages)) {
          if ($page_id === false) {
              foreach ($two_critical_pages as $critical_page) {
                  self::two_critical_status($critical_page['id']);
              }
          }elseif (isset($two_critical_pages[$page_id])){
              $critical_in_progress_key = "two_critical_in_progress_" . $page_id;
              $critical_in_progress = get_transient($critical_in_progress_key);
              if ($critical_in_progress !== "1") {
                  if (isset($two_critical_pages[$page_id]["status"]) && $two_critical_pages[$page_id]["status"] == "in_progress") {
                      $two_critical_pages[$page_id]["status"] = "not_started";
                      $TwoSettings->update_setting("two_critical_pages", $two_critical_pages);
                  }
              }
          }
      }
  }

  public static function two_redirect( $url ) {
    while (ob_get_level() !== 0) {
      ob_end_clean();
    }
    wp_redirect( $url );
    exit();
  }


  public static function check_redirect($url, $arg = true){
      if($arg){
          $url = add_query_arg( array(
              'two_check_redirect' => '1',
          ), $url );
      }
      $headers= get_headers($url , true);
      if(isset($headers["Location"])){
          $location = $headers["Location"];
          if(!empty($location)){
              if(is_array($location)){
                  $location = end($location);
              }
              $redirect_url_parse = parse_url($location);
              $main_url_parse = parse_url($url);
              if(isset($redirect_url_parse['host']) && isset($main_url_parse['host'])){
                  if($redirect_url_parse['host'] === $main_url_parse['host']){
                      return true;
                  }
              }
              return false;
          }
          return false;
      }
      return true;
  }


  public static function is_paid_user() {
    global $tenweb_subscription_id;
    return ( defined('TENWEB_SO_HOSTED_ON_10WEB') && TENWEB_SO_HOSTED_ON_10WEB ) || ( defined('TENWEB_SO_FREE_SUBSCRIPTION_ID') && ($tenweb_subscription_id != TENWEB_SO_FREE_SUBSCRIPTION_ID) );
  }

  public static function is_tenweb_booster_connected()
  {
      return ((defined('TENWEB_SO_HOSTED_ON_10WEB') && TENWEB_SO_HOSTED_ON_10WEB) || (defined('TENWEB_CONNECTED_SPEED') && \Tenweb_Authorization\Login::get_instance()->check_logged_in() && \Tenweb_Authorization\Login::get_instance()->get_connection_type() == TENWEB_CONNECTED_SPEED));
  }
  /**
  * For hosting cache , run only connected sites
  **/
  public static function update_post($id = 0){
      if(TENWEB_SO_HOSTED_ON_10WEB){
          return;
      }
      if($id === 0){
          $id = get_option('page_on_front');
          if($id === "0" || $id === 0){
              $recent_post = wp_get_recent_posts( [
                  'numberposts'      => 1,
                  'post_type'        => 'post',
                  'post_status'      => 'publish',
              ], OBJECT );
              if(is_array($recent_post) && isset($recent_post[0]->ID)){
                  $id = $recent_post[0]->ID;
              }
          }
      }
      if($id !==0){
          $post_data = array('ID' => $id);
          wp_update_post( wp_slash( $post_data ) );
      }
  }

  /**
  * For not hosted sites
  **/
  public static function set_critical(){
      if(isset($_POST["token"], $_POST["page_id"]) && get_transient("two_critical" . $_POST["page_id"]) === $_POST["token"]) {
          delete_transient("two_critical".sanitize_text_field( $_POST["page_id"] ));
          if(isset($_FILES["covered_css"])){
              $uploadfile = $_FILES['covered_css']['tmp_name'];
              delete_transient("two_critical_in_process");
              $triggerPostOptimizationTasks = !empty($_POST['newly_connected_website']) && !empty($_POST['notification_id']);
              update_option('two_critical_data_import_data_'.time(), $triggerPostOptimizationTasks, false);
              \TenWebOptimizer\OptimizerCriticalCss::createCriticalCSS($uploadfile, $triggerPostOptimizationTasks);
              echo '{"status":"ok"}';
              die(0);
          }

          die("no covered_css data");
      }

      die("Invalid token");
  }


  public static function download_critical(){
      if(isset($_GET["two_update_critical"], $_GET["page_id"]) && $_GET["two_update_critical"]==="1"){
          $return_data = array(
              'success' => false,
              'message' => "error"
          );
          $page_id = sanitize_text_field($_GET["page_id"]);
          $triggerPostOptimizationTasks = !empty($_GET['newly_connected_website']) && !empty($_GET['notification_id']);
          $domain_id = get_site_option('tenweb_domain_id');
          $access_token = get_site_option(TENWEB_PREFIX . '_access_token');
          $file_content_response = wp_remote_get( TENWEB_SO_CRITICAL_URL."/v1/critical/".$domain_id."/pages/".$page_id."/get", array(
              'timeout'     => 5,
              'redirection' => 5,
              'httpversion' => '1.0',
              'blocking'    => true,
              'headers'     => array(
                  "accept" => "application/x.10webperformance.v1+json",
                  "authorization" => "Bearer ".$access_token,
              ),
              'cookies'     => array()
          ));

          if(isset($file_content_response["body"])){
              $file_content = $file_content_response["body"];
              $file_content_arr = json_decode($file_content, true);
              if(isset($file_content_arr["data"]["data"]["covered_css"]["value"])){
                  \TenWebOptimizer\OptimizerCriticalCss::createCriticalCSS(false, $triggerPostOptimizationTasks, $file_content_arr["data"]["data"]["covered_css"]["value"]);
                  $return_data["success"] = true;
                  $return_data["message"] = "success";
              }
          }
          echo json_encode($return_data);
          die;
      }
  }
}
