<?php

use TenWebOptimizer\OptimizerCriticalCss;
use TenWebOptimizer\OptimizerUtils;

class OptimizerApi{
    public $modes = array();
    public function __construct()
    {
        $this->modes = \TenWebOptimizer\OptimizerUtils::get_modes();
        add_action( 'rest_api_init', array($this, "two_rest"));
    }
    public function two_rest(){
        register_rest_route('tenweb_so/v1', 'set_critical',
            array(
                'methods'  => 'POST',
                'callback' => array($this, "set_critical"),
                'permission_callback' => array($this, 'check_critical_authorization'),
                'args'     => [
                    'page_id' => [
                        'type'     => 'string',
                        'required' => true,
                        'validate_callback' => array($this, "validate_page_id")
                    ],
                    'token' => [
                        'type'    => 'string',
                        'required' => true,
                    ],
                ],
            )
        );


        register_rest_route('tenweb_so/v1', 'set_modes',
            array(
                'methods'  => 'POST',
                'callback' => array($this, "set_modes"),
                'permission_callback' => array($this, 'check_authorization'),
                'args'     => [
                    'page_id' => [
                        'type'     => 'string',
                        'required' => true,
                        'validate_callback' => array($this, "validate_page_id")
                    ],
                    'mode' => [
                        'type'    => 'string',
                        'required' => true,
                        'validate_callback' => array($this, "validate_mode")
                    ],
                    'is_custom' => [
                        'type'    => 'string',
                    ],
                ],
            )
        );

        register_rest_route('tenweb_so/v1', 'get_modes',
            array(
                'methods'  => 'POST',
                'callback' => array($this, "get_modes"),
                'permission_callback' => array($this, 'check_authorization'),
                'args'     => [
                    'page_id' => [
                        'type'     => 'string',
                        'required' => true,
                        'validate_callback' => array($this, "validate_page_id")
                    ],
                ],
            )
        );

        register_rest_route('tenweb_so/v1', 'clear_cache',
            array(
                'methods'  => 'POST',
                'permission_callback' => array($this, 'check_authorization'),
                'callback' => array($this, "clear_cache"),
            )
        );

        register_rest_route('tenweb_so/v1', 'get_page_id',
            array(
                'methods'  => 'POST',
                'permission_callback' => array($this, 'check_authorization'),
                'callback' => array($this, "get_page_id"),
                'args'     => [
                    'page_url' => [
                        'type'     => 'string',
                        'required' => true,
                    ],
                ],
            )
        );


        register_rest_route('tenweb_so/v1', 'get_pages',
            array(
                'methods'  => 'POST',
                'permission_callback' => array($this, 'check_authorization'),
                'callback' => array($this, "get_pages"),
            )
        );

        register_rest_route('tenweb_so/v1', 'delete_so_page',
            array(
                'methods'  => 'POST',
                'callback' => array($this, "delete_so_page"),
                'permission_callback' => array($this, 'check_authorization'),
                'args'     => [
                    'page_id' => [
                        'type'     => 'string',
                        'required' => true,
                        'validate_callback' => array($this, "validate_page_id")
                    ],
                ],
            )
        );

        register_rest_route('tenweb_so/v1', 'logout',
            array(
                'methods'  => 'POST',
                'callback' => array($this, "logout"),
                'permission_callback' => array($this, 'check_authorization'),
            )
        );

        register_rest_route('tenweb_so/v1', 'check_domain',
            array(
                'methods'  => 'POST',
                'permission_callback' => '__return_true',
                'callback' => array($this, "check_domain"),
            )
        );

        register_rest_route('tenweb_so/v1', 'get_benchmark_data',
            array(
                'methods'  => 'GET',
                'permission_callback' => array($this, 'check_authorization'),
                'callback' => array($this, "get_benchmark_data"),
            )
        );

        register_rest_route('tenweb_so/v1', 'rerun_benchmark',
            array(
                'methods'  => 'POST',
                'permission_callback' => array($this, 'check_authorization'),
                'callback' => array($this, "rerun_benchmark"),
            )
        );

        register_rest_route('tenweb_so/v1', 'get_webp_status',
            array(
                'methods'  => 'POST',
                'callback' => array($this, "get_webp_status"),
                'permission_callback' => array($this, 'check_authorization'),
            )
        );

        register_rest_route('tenweb_so/v1', 'set_webp_status',
          array(
            'methods'  => 'POST',
            'callback' => array($this, "set_webp_status"),
            'permission_callback' => array($this, 'check_authorization'),
            'args'     => [
              'webp_delivery' => [
                'type'     => 'boolean',
                'required' => true,
              ],
              'picture_webp_delivery' => [
                'type'    => 'boolean',
                'required' => true,
              ],
            ],
          )
        );

        register_rest_route('tenweb_so/v1', 'page_cache',
            array(
                'methods'  => 'POST',
                'permission_callback' => array($this, 'check_authorization'),
                'callback' => array($this, "page_cache"),
                'args'     => [
                    'status' => [
                        'type'     => 'boolean',
                        'required' => true,
                    ],
                ],
            )
        );
        register_rest_route('tenweb_so/v1', 'get_page_cache_status',
            array(
                'methods'  => 'GET',
                'permission_callback' => array($this, 'check_authorization'),
                'callback' => array($this, "get_page_cache_status"),
            )
        );

        register_rest_route('tenweb_so/v1', 'update_settings',
            array(
                'methods'  => 'POST',
                'callback' => array($this, "update_settings"),
                'permission_callback' => array($this, 'check_authorization'),
                'args'     => [
                    'key' => [
                        'type'     => 'string',
                        'required' => true,
                    ],
                    'val' => [
                        'type'    => 'string',
                        'validate_callback' => array($this, "validate_option_value")
                    ],
                ],
            )
        );

        register_rest_route('tenweb_so/v1', 'get_settings',
            array(
                'methods'  => 'GET',
                'callback' => array($this, "get_settings"),
                'permission_callback' => array($this, 'check_authorization'),
            )
        );

        register_rest_route('tenweb_so/v1', 'get_incompatible_active_plugins',
            array(
                'methods'  => 'GET',
                'callback' => array($this, "get_incompatible_active_plugins"),
                'permission_callback' => array($this, 'check_authorization'),
            )
        );
    }


    public function set_critical(){
        OptimizerUtils::set_critical();
    }

    public function get_incompatible_active_plugins() {
        $data_for_response = array(
            'success'=>false,
            'message'=>"Cannot get incompatible plugins",
        );
        try {
            $data_for_response["success"] = true;
            $data_for_response["message"] = "Successfully";
            $two_incompatible_plugins =  \TenWebOptimizer\OptimizerAdmin::get_conflicting_plugins();
            $data_for_response["two_incompatible_plugins"] = $two_incompatible_plugins;
            return new \WP_REST_Response($data_for_response, 200);
        } catch(Exception $exception) {
            return new \WP_REST_Response($data_for_response, 500);
        }
    }

    public function get_settings(){
        $data_for_response = array(
            'success'=>false,
            'message'=>"Cannot get settings",
        );
        try {
            $two_settings = get_option("two_settings");
            $two_settings = json_decode($two_settings, true);
            $two_critical_pages = OptimizerUtils::getCriticalPages();
            if (isset($two_critical_pages) && is_array($two_critical_pages)) {
                $two_settings["two_critical_pages"] = $two_critical_pages;
            }
            $two_triggerPostOptimizationTasks = get_option("two_triggerPostOptimizationTasks");
            $data_for_response["success"] = true;
            $data_for_response["message"] = "Successfully";
            $data_for_response["two_triggerPostOptimizationTasks"] = $two_triggerPostOptimizationTasks;
            $data_for_response["settings"] = $two_settings;

            return new \WP_REST_Response($data_for_response, 200);
        } catch(Exception $exception) {
            return new \WP_REST_Response($data_for_response, 500);
        }
    }

    public function update_settings(WP_REST_Request $request){
        $data_for_response = array(
            'success'=>false,
            'message'=>"Cannot set option",
        );

        try {
            global $TwoSettings;
            $settings_names = $TwoSettings->settings_names;
            $key = sanitize_text_field($request["key"]);
            if(!isset($request["val"])){
                $option = $TwoSettings->get_settings($key);
                $data_for_response["success"] = true;
                $data_for_response["message"] = "Successfully";
                $data_for_response[$key] = $option;
                return new \WP_REST_Response($data_for_response, 200);
            }else{
                $val = sanitize_text_field($request["val"]);
            }
            if(isset($settings_names[$key])){
                if(isset($settings_names[$key]["type"]) && $settings_names[$key]["type"]==="textarea"){
                    $option = $TwoSettings->get_settings($key);
                    if(!empty($option)){
                        $arr_option = explode(",", $option);
                        $el_key = array_search($val, $arr_option, false);
                        if ($el_key !== false) {
                            unset($arr_option[$el_key]);
                            $val = implode(",", $arr_option);
                            $data_for_response["success"] = true;
                            $data_for_response["message"] = "Option deleted successfully.";
                        }else{
                            $val = $option.",".$val;
                        }
                    }
                }
                if(!$data_for_response["success"]){
                    $data_for_response["success"] = true;
                    $data_for_response["message"] = "Option updated successfully.";
                }
                $TwoSettings->update_setting($key, $val);
                return new \WP_REST_Response($data_for_response, 200);
            }else{
                $data_for_response["message"] = "Option name not found.";
                $data_for_response["error"] = "Option name not found.";
                return new \WP_REST_Response($data_for_response, 500);
            }
        }
        catch(Exception $exception) {
            return new \WP_REST_Response($data_for_response, 500);
        }
    }


    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */

    public function logout(WP_REST_Request $request){
        $data_for_response = array(
            'success' => false,
            'message' => "Cannot logout client",
            'code' => 'not_ok'
        );
        try {
            $login = \Tenweb_Authorization\Login::get_instance();
            if($login !== null) {
                $login->logout(false);
                $data_for_response['success'] = true;
                $data_for_response['message'] = "Successfully logged out";
                $data_for_response['code'] = "ok";
            }
        } catch(Exception $exception) {
            $data_for_response['message'] = 'Error in logging out client';
            $data_for_response['error'] = $exception->getMessage().' in '.$exception->getFile().' on '.$exception->getLine();
            return new \WP_REST_Response($data_for_response, 500);
        }

        return new \WP_REST_Response($data_for_response, 200);
    }


    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */

    public function get_page_cache_status(WP_REST_Request $request){
        $data_for_response = array(
            'success'=>false,
            'page_cache'=>true,
            'message'=>"Cannot get page cache status",
            'clear_cache_date' => '',
        );
        try {
            global $TwoSettings;
            $data_for_response['clear_cache_date'] = $TwoSettings->get_settings("two_clear_cache_date" , "" );
            $two_page_cache = $TwoSettings->get_settings("two_page_cache" , "");
            $data_for_response["success"] = true;
            if ($two_page_cache === "on"){
                $data_for_response["message"] = "Page cache enabled";
                $data_for_response["page_cache"] = true;
            } else {
                $data_for_response["message"] = "Page cache disabled";
                $data_for_response["page_cache"] = false;
            }
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in getting page cache status';
            $data_for_response['error'] = $exception->getMessage().' in '.$exception->getFile().' on '.$exception->getLine();
            return new \WP_REST_Response($data_for_response, 500);
        }

        return new \WP_REST_Response($data_for_response, 200);
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */

    public function page_cache(WP_REST_Request $request){
        $data_for_response = array(
            'success'=>false,
            'message'=>"Cannot change page cache status",
        );
        try {
            $status = $request["status"];
            global $TwoSettings;
            if($status){
                $TwoSettings->update_setting("two_page_cache", "on");
                $data_for_response["success"] = true;
                $data_for_response["message"] = "Page cache enabled";
            }else{
                $TwoSettings->update_setting("two_page_cache", "");
                $data_for_response["success"] = true;
                $data_for_response["message"] = "Page cache disabled";
            }
            \TenWebOptimizer\OptimizerAdmin::clear_cache(false, true);
        } catch(Exception $exception) {
            $data_for_response['message'] = 'Error in updating page cache status';
            $data_for_response['error'] = $exception->getMessage().' in '.$exception->getFile().' on '.$exception->getLine();
            return new \WP_REST_Response($data_for_response, 500);
        }

        return new \WP_REST_Response($data_for_response, 200);
    }


    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */

    public function delete_so_page(WP_REST_Request $request){
        $data_for_response = array(
            'success' => false,
            'message' => "Cannot delete page",
        );
        try {
            global $TwoSettings;
            $page_id = $request["page_id"];
            if($page_id === "front_page"){
                delete_option("two_mode_front_page");
                $two_critical_pages = $TwoSettings->get_settings("two_critical_pages");
                unset($two_critical_pages[$page_id], $two_critical_pages[""]);
                $TwoSettings->update_setting("two_critical_pages", $two_critical_pages);
            } else {
                delete_post_meta($page_id, "two_mode");
                delete_post_meta($page_id, 'two_critical_pages' );
            }
            $prefix = "critical/two_".$page_id."_*.*";
            \TenWebOptimizer\OptimizerUtils::delete_files_by_prefix($prefix);
            \TenWebOptimizer\OptimizerAdmin::clear_cache(false, true);
            $data_for_response['success'] = true;
            $data_for_response['message'] = 'Page has been deleted';
            OptimizerUtils::update_post();
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in deleting page';
            $data_for_response['error'] = $exception->getMessage().' in '.$exception->getFile().' on '.$exception->getLine();
            return new \WP_REST_Response($data_for_response, 500);
        }

        return new \WP_REST_Response($data_for_response, 200);
    }


    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function get_pages(WP_REST_Request $request){
        $data_for_response = array(
            'success' => false,
            'message' => "Pages not found",
            'data' => []
        );

        try {
            $global_mode = get_option("two_default_mode");
            if (is_array($global_mode) && isset($global_mode["mode"])) {
                $global_mode = $global_mode["mode"];
            } else {
                $global_mode = false;
            }
            $count_posts = wp_count_posts( 'post' );
            $count_pages = wp_count_posts( 'page' );
            global $TwoSettings;
            $so_pages_list = array(
                'global_mode' => $global_mode,
                'test_mode' => 'on' == $TwoSettings->get_settings("two_test_mode") ? 'on' : 'off',
                'page_count' => $count_pages->publish,
                'post_count' => $count_posts->publish,
                'pages' => array(),
            );
            $two_optimized_pages = \TenWebOptimizer\OptimizerUtils::getCriticalPages();

            $args = array(
                'post_type' => 'page',
                'meta_key' => 'two_mode',
            );
            $optimized_posts = new WP_Query($args);
            if (isset($optimized_posts->posts)) {
                foreach ($optimized_posts->posts as $post) {
                    if (isset($post->ID) && !isset($two_optimized_pages[$post->ID])) {
                        $two_optimized_pages[$post->ID] = array(
                            'id' => $post->ID,
                            'title' => $post->post_title,
                            'url' => get_permalink( $post->ID ),
                            'status' => "success",
                        );
                    }
                }
            }
            if (is_array($two_optimized_pages)) {
                foreach ($two_optimized_pages as $so_page) {
                    $so_page_data = array(
                        'page_id' => $so_page["id"],
                        'title' => $so_page["title"],
                        'url' => $so_page["url"],
                        'status' => $so_page["status"],
                    );


                    if ($so_page["id"] === "front_page") {
                        $page_mode = get_option("two_mode_front_page");
                        $two_optimized_date_front_page = get_option("two_optimized_date_front_page");
                    } else {
                        $so_page["id"] = (int)$so_page["id"];
                        $so_page_data["page_id"] = (int)$so_page_data["page_id"];
                        $page_mode = get_post_meta($so_page["id"], "two_mode", true);
                        $two_optimized_date = get_post_meta($so_page["id"], "two_optimized_date_front_page", true);
                    }
                    if (is_array($page_mode) && isset($page_mode["mode"])) {
                        $page_mode_name = $page_mode["mode"];
                    } else {
                        $page_mode_name = false;
                    }
                    $so_page_data["mode"] = $page_mode_name;
                    if (isset($request["is_custom"]) && intval($request["is_custom"]) == 1) {
                        if (!isset($page_mode["is_custom"]) || !$page_mode["is_custom"]) {
                            continue;
                        }
                    }

                    if (isset($so_page["critical_date"])) {
                        $so_page_data["date"] = $so_page["critical_date"];
                    } else {
                        if (isset($two_optimized_date)) {
                            $so_page_data["date"] = $two_optimized_date;
                        } elseif (isset($two_optimized_date_front_page)) {
                            $so_page_data["date"] = $two_optimized_date_front_page;
                        }
                    }

                    $so_page_data["is_custom"] = 0;
                    if (isset($page_mode["is_custom"])) {
                        $so_page_data["is_custom"] = $page_mode["is_custom"];
                    }

                    $so_pages_list["pages"][] = $so_page_data;
                }
                $data_for_response["success"] = true;
                $data_for_response["message"] = "Pages found successfully";
                $data_for_response["data"] = $so_pages_list;
            }
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in getting pages';
            $data_for_response['error'] = $exception->getMessage().' in '.$exception->getFile().' on '.$exception->getLine();

            return new \WP_REST_Response($data_for_response, 500);
        }
        return new \WP_REST_Response($data_for_response, 200);
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function check_domain(WP_REST_Request $request){
        if (get_site_option(TENWEB_PREFIX . '_is_available') !== '1') {
            update_site_option(TENWEB_PREFIX . '_is_available', '1');
        }
        $parameters = self::wp_unslash_conditional($request->get_body_params());

        if (isset($parameters['confirm_token'])) {
            $confirm_token_saved = get_site_transient(TENWEB_PREFIX . '_confirm_token');
            if ($parameters['confirm_token'] === $confirm_token_saved) {
                $data_for_response = array(
                    "code" => "ok",
                    "data" => "it_was_me"  // do not change
                );
                $headers_for_response = array('tenweb_check_domain' => "it_was_me");
            } else {
                $data_for_response = array(
                    "code" => "ok",
                    "data" => "it_was_not_me" // do not change
                );
                $headers_for_response = array('tenweb_check_domain' => "it_was_not_me");
            }
        } else {
            $data_for_response = array(
                "code" => "ok",
                "data" => "alive"  // do not change
            );
            $headers_for_response = array('tenweb_check_domain' => "alive");
        }

        $tenweb_hash = $request->get_header('tenweb-check-hash');
        if (!empty($tenweb_hash)) {
            $encoded = '__' . $tenweb_hash . '.';
            $encoded .= base64_encode(json_encode($data_for_response));
            $encoded .= '.' . $tenweb_hash . '__';

            $data_for_response['encoded'] = $encoded;
            Helper::set_error_log('tenweb-check-hash', $encoded);
        }

        return new \WP_REST_Response($data_for_response, 200, $headers_for_response);
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function clear_cache(WP_REST_Request $request) {
        $data_for_response = array(
            'success'=>false,
            'message'=>"Cache not cleared",
        );
        try {
            \TenWebOptimizer\OptimizerUtils::two_update_subscription();
            OptimizerUtils::update_post();
            if(\TenWebOptimizer\OptimizerAdmin::clear_cache(false, true, true, true,'front_page', true)){
                $data_for_response["success"] = true;
                $data_for_response["message"] = "Cache cleared";
            }
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in clearing cache';
            $data_for_response['error'] = $exception->getMessage().' in '.$exception->getFile().' on '.$exception->getLine();

            return new \WP_REST_Response($data_for_response, 500);
        }

        return new \WP_REST_Response($data_for_response, 200);
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function get_benchmark_data(WP_REST_Request $request){
        $data_for_response = array(
            'success'=>false,
            'message'=>"No Benchmark Data",
        );
        try {
            $benchmark = \TenWebOptimizer\OptimizerBenchmark::get_instance();
            if(isset($benchmark) && $benchmarkData = $benchmark->getData()){
                $data_for_response = [
                    'success' => true,
                    'message' => 'Success',
                    'data' => $benchmarkData,
                ];
            }
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in getting benchmark data';
            $data_for_response['error'] = $exception->getMessage().' in '.$exception->getFile().' on '.$exception->getLine();

            return new \WP_REST_Response($data_for_response, 500);
        }

        return new \WP_REST_Response($data_for_response, 200);
    }


    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function rerun_benchmark(WP_REST_Request $request){
        $data_for_response = array(
            'success'=>false,
            'message'=>"Benchmark failed",
        );
        try {
            $benchmark = \TenWebOptimizer\OptimizerBenchmark::get_instance();
            if(isset($benchmark) && $benchmarkData = $benchmark->test()){
                $data_for_response = [
                    'success' => true,
                    'message' => 'Success',
                    'data' => $benchmarkData,
                ];
            }
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in rerunning benchmark';
            $data_for_response['error'] = $exception->getMessage().' in '.$exception->getFile().' on '.$exception->getLine();

            return new \WP_REST_Response($data_for_response, 500);
        }

        return new \WP_REST_Response($data_for_response, 200);
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function get_modes(WP_REST_Request $request){
        $data_for_response = array(
            'success' => false,
            'message' => "Mode not found",
        );
        try {
            $page_id = $request["page_id"];
            if($page_id === "all"){
                $mode = get_option("two_default_mode", false);
            }elseif ($page_id === "front_page"){
                $mode = get_option("two_mode_front_page");
            } else{
                $mode = get_post_meta($page_id, "two_mode", true);
            }
            if(is_array($mode) && isset($mode["mode"]) && isset($this->modes[$mode["mode"]])){
                $mode = $mode["mode"];
                $data_for_response["success"] = true;
                $data_for_response["message"] = "success";
                $data_for_response["mode"] = $mode;
            }
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in getting modes';
            $data_for_response['error'] = $exception->getMessage().' in '.$exception->getFile().' on '.$exception->getLine();
            return new \WP_REST_Response($data_for_response, 500);
        }

        return new \WP_REST_Response($data_for_response, 200);
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function set_modes(WP_REST_Request $request){
        $data_for_response = array(
            'success'=>false,
            'message'=>"Invalid mode",
        );
        try {
            global $TwoSettings;
            $settings_names = $TwoSettings->settings_names;
            $mode = $request["mode"];
            $page_id = $request["page_id"];
            $is_custom = intval($request["is_custom"]);
            $no_optimize_pages_list = get_option("no_optimize_pages");

            if ($page_id != "front_page" && $page_id != "all") {
                $page_url = get_permalink($page_id);
            } elseif ($page_id === "front_page") {
                $page_url = get_home_url();
            }

            if (isset($page_url)) {
                if ($mode == "no_optimize") {
                    if (!is_array($no_optimize_pages_list)) {
                        $no_optimize_pages_list = array();
                    }
                    $no_optimize_pages_list[$page_id] = $page_url;

                } else if (is_array($no_optimize_pages_list)) {
                    unset($no_optimize_pages_list[$page_id]);
                }
                update_option("no_optimize_pages", $no_optimize_pages_list);
            }
            if (isset($this->modes[$mode])) {
                if ($page_id == "all") {
                    foreach ($this->modes[$mode] as $key => $val) {
                        if($key === "two_delay_all_js_execution"){
                            if ($val) {
                                $TwoSettings->update_setting("two_delay_all_js_execution", "on");
                            } else {
                                $TwoSettings->update_setting("two_delay_all_js_execution", "");
                            }
                        }
                        elseif (isset($settings_names[$key])) {
                            $TwoSettings->update_setting($key, $val);
                        } elseif ($key === "critical_enabled") {
                            if ($val) {
                                $TwoSettings->update_setting("two_critical_status", "true");
                            } else {
                                $TwoSettings->update_setting("two_critical_status", "");
                            }
                        }
                    }
                    update_option("two_default_mode", $this->modes[$mode]);
                } else {
                    $tenweb_subscription_id = get_transient(TENWEB_PREFIX . '_subscription_id');
                    $is_free = ($tenweb_subscription_id === TENWEB_SO_FREE_SUBSCRIPTION_ID && !TENWEB_SO_HOSTED_ON_10WEB);
                    OptimizerCriticalCss::generate_critical_css_by_id($page_id, $is_free);
                    $this->modes[$mode]["is_custom"] = 0;
                    if ($is_custom === 1) {
                        $this->modes[$mode]["is_custom"] = 1;
                    }
                    if ($page_id === "front_page") {
                        update_option("two_mode_front_page", $this->modes[$mode]);
                    } else {
                        update_post_meta($page_id, "two_mode", $this->modes[$mode]);
                    }
                }
                OptimizerUtils::update_post();
                $data_for_response["success"] = true;
                $data_for_response["message"] = "Mode installed successfully";
            } else {

                return new \WP_REST_Response($data_for_response, 404);
            }

            \TenWebOptimizer\OptimizerAdmin::clear_cache(false, true);
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in applying page';
            $data_for_response['error'] = $exception->getMessage().' in '.$exception->getFile().' on '.$exception->getLine();

            return new \WP_REST_Response($data_for_response, 500);
        }
        return new \WP_REST_Response($data_for_response, 200);
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function get_webp_status(WP_REST_Request $request) {
        $data_for_response = array(
            'success' => false,
            'message' => "Cannot get webp status.",
        );
        try {
            global $TwoSettings;
            $webp_status = array();

            if (TENWEB_SO_HOSTED_ON_10WEB) {
                $webp_status["hosting"] = '10Web';
                $webp_status["webp_delivery"] = $TwoSettings->get_settings("two_enable_nginx_webp_delivery");
            } else {
                if (TENWEB_SO_HOSTED_ON_NGINX) {
                    $webp_status["hosting"] = 'NGINX';
                } else {
                    $webp_status["hosting"] = 'APACHE';
                    $webp_status["htaccess_writable"] = TENWEB_SO_HTACCESS_WRITABLE;
                    $webp_status["webp_delivery"] = $TwoSettings->get_settings("two_enable_htaccess_webp_delivery");
                }
                $two_webp_delivery_working = \TenWebOptimizer\OptimizerUtils::testWebPDelivery();
                $webp_status["webp_delivery_working"] = $two_webp_delivery_working;
                $webp_status["picture_webp_delivery"] = $TwoSettings->get_settings("two_enable_picture_webp_delivery");
            }

            if ($webp_status) {
                $data_for_response["success"] = true;
                $data_for_response["message"] = "WebP status collected successfully.";
                $data_for_response["data"] = $webp_status;
            }
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in getting webp status';
            $data_for_response['error'] = $exception->getMessage().' in '.$exception->getFile().' on '.$exception->getLine();

            return new \WP_REST_Response($data_for_response, 500);
        }

        return new \WP_REST_Response($data_for_response, 200);
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function set_webp_status(WP_REST_Request $request){
        $data_for_response = array(
          'success'=>false,
          'message'=>"Nothing to change",
        );
        try {
            global $TwoSettings;
            $webp_delivery = $request["webp_delivery"] ? 'on' : '';
            $picture_webp_delivery = $request["picture_webp_delivery"] ? 'on' : '';

            if ($TwoSettings->get_settings("two_enable_picture_webp_delivery") != $picture_webp_delivery) {
                $TwoSettings->update_setting('two_enable_picture_webp_delivery', $picture_webp_delivery);
                $data_for_response["success"] = true;
            }
            if (TENWEB_SO_HOSTED_ON_10WEB) {
                if ($TwoSettings->get_settings("two_enable_nginx_webp_delivery") != $webp_delivery) {
                    $TwoSettings->update_setting('two_enable_nginx_webp_delivery', $webp_delivery);
                    $data_for_response["success"] = true;
                }
            } else if (!TENWEB_SO_HOSTED_ON_NGINX && TENWEB_SO_HTACCESS_WRITABLE) {
                if ($TwoSettings->get_settings("two_enable_htaccess_webp_delivery") != $webp_delivery) {
                    $TwoSettings->update_setting('two_enable_htaccess_webp_delivery', $webp_delivery);
                    $data_for_response["success"] = true;
                }
            }
            if ($data_for_response["success"]) {
                $code = apply_filters('two_save_settings_code', 0);
                if ('nginx_webp_delivery' === $code) {
                    $data_for_response["config_changed"] = false;
                } else {
                    $data_for_response["config_changed"] = true;
                }
                $data_for_response["message"] = "WebP status changed successfully";
            }
            \TenWebOptimizer\OptimizerAdmin::clear_cache(false, true);
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in setting webp status';
            $data_for_response['error'] = $exception->getMessage().' in '.$exception->getFile().' on '.$exception->getLine();

            return new \WP_REST_Response($data_for_response, 500);
        }
        return new \WP_REST_Response($data_for_response, 200);
    }



  /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function get_page_id(WP_REST_Request $request){
        $data_for_response = array(
            'success'=>false,
            'message'=>"Invalid url",
        );
        try {
            $check_redirect = \TenWebOptimizer\OptimizerUtils::check_redirect($request["page_url"]);
            if($check_redirect){
                $page_id = \TenWebOptimizer\OptimizerUtils::get_post_id($request["page_url"]);
                if ($page_id) {
                    $data_for_response["success"] = true;
                    $data_for_response["message"] = "Page found successfully";
                    $data_for_response["page_id"] = $page_id;
                } else {
                    $data_for_response['message'] = 'Page not found';
                    return new \WP_REST_Response($data_for_response, 404);
                }
            } else {
                $data_for_response['message'] = 'Url has redirect';
                return new \WP_REST_Response($data_for_response, 400);
            }
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in getting pageId';
            $data_for_response['error'] = $exception->getMessage().' in '.$exception->getFile().' on '.$exception->getLine();

            return new \WP_REST_Response($data_for_response, 500);
        }
        return new \WP_REST_Response($data_for_response, 200);
    }




    public function check_critical_authorization(WP_REST_Request $request){
        $token = $request->get_param("token");
        $page_id = $request->get_param("page_id");
        return isset($token, $page_id) && get_transient("two_critical" . $page_id) === $token;
    }

    public function validate_mode($param, $request, $key){
        return isset($this->modes[$param]);
    }
    public function validate_option_value($param, $request, $key){
        global $TwoSettings;
        $valid_params = array(
            "",
            "on",
            "off",
            "true",
            "false",
            "1",
            "0",
            "vanilla",
            "browser",
        );
        $settings_names = $TwoSettings->settings_names;
        $option_name = sanitize_text_field($request["key"]);
        if(isset($settings_names[$option_name])){
            if(isset($settings_names[$option_name]["type"]) && $settings_names[$option_name]["type"]==="textarea"){
                return true;
            }
            $el_key = in_array($param, $valid_params, false);
            if ($el_key !== false) {
                return true;
            }
        }
        return false;

    }

    public function validate_page_id($param, $request, $key){
        return ($param==="front_page" || $param ==="all"|| (int)($param)>0);
    }

    public function check_authorization(WP_REST_Request $request){
        if (!\Tenweb_Authorization\Login::get_instance()->check_logged_in()){
            $data_for_response = array(
                "code"    => "unauthorized",
                "message" => "unauthorized",
                "data"    => array(
                    "status" => 401
                )
            );
            return new WP_Error('rest_forbidden', $data_for_response, 401);
        }
        $authorize = \Tenweb_Authorization\Login::get_instance()->authorize($request);
        if (is_array($authorize)) {
            return new WP_Error('rest_forbidden', $authorize, 401);
        }
        return true;
    }

    /*
        * wp 4.4 adds slashes, removes them
        *
        * https://core.trac.wordpress.org/ticket/36419
        **/
    private static function wp_unslash_conditional($data)
    {

        global $wp_version;
        if ($wp_version < 4.5) {
            $data = wp_unslash($data);
        }

        return $data;
    }
}
