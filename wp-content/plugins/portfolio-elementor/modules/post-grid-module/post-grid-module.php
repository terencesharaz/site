<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/*
* Plugin Functions
*/
require 'includes/functions.php';
/*
* Elementor
*/
require 'includes/extend-elementor.php';
/*
* Plugin Directory
*/
function pwgd_get_plugin_directory_url() {
    return plugin_dir_url(__FILE__);
}