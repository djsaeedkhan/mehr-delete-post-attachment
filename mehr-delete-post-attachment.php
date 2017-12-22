<?php 
/*
  Plugin Name: حذف ضمیمه های پست
  Plugin URI: http://plugin-nevis.ir/deletepostattachment
  Description: پلاگین نویس :: مدیریت حذف ضمیمه های پست
  Version: 1.96.4
  Author: پلاگین نویس
  Author URI: http://mehregan-system.com/
  License: GPLv2+
  Text Domain: mehrdeletepostattachment
*/
class MehrDPA{
	function __construct() {
	global $wpdb;
	$this->pre='depoat';
	$this->ProductRow = 16;
	 
	//define show basket in page
	add_filter('the_content',array($this,'shop_basket')); //
	add_filter('login_redirect',array($this,'admin_default_page'));

	add_action('admin_menu', array($this,'wpa_add_menu'));
	add_action('admin_enqueue_scripts', array($this, 'wpa_styles'));
	add_action('wp_enqueue_scripts', array($this, 'wpa_styles'));
	register_activation_hook(__FILE__, array($this, 'wpa_install'));
	register_deactivation_hook(__FILE__, array($this, 'wpa_uninstall'));
}
//------------------------------------------------------------------
function wpa_add_menu(){//Admin Menu
	add_menu_page( 'حذف ضمیمه پست', 'حذف ضمیمه پست', 'manage_options', 'dpa-dashboard', array(__CLASS__,'wpa_page_file_path'), plugins_url('images/logo.jpg', __FILE__),'99.3.9');
}
//------------------------------------------------------------------
function wpa_page_file_path() {
	$screen = get_current_screen();
	if(strpos($screen->base, 'dpa-order')!== false){include( dirname(__FILE__) . '/includes/order.php' );}
	elseif (strpos($screen->base, 'dpa-setting')!== false){include( dirname(__FILE__) . '/includes/setting.php' );}
	elseif (strpos($screen->base, 'dpa-ordinfo')!== false){include( dirname(__FILE__) . '/includes/orderinfo.php' );}
	elseif (strpos($screen->base, 'dpa-plan')!== false){include( dirname(__FILE__) . '/includes/plan.php' );}
	else {include( dirname(__FILE__) . '/includes/dashboard.php' );}
}
//------------------------------------------------------------------
function PluginDir()
{
	return plugin_dir_url(__FILE__);	
}
//------------------------------------------------------------------
function wpa_styles($page){
	wp_enqueue_style('wp-analytify-style',plugins_url('css/style.css',__FILE__));
}
//------------------------------------------------------------------
function wpa_install() {
}
//------------------------------------------------------------------
function wpa_uninstall() {
}
//------------------------------------------------------------------
function has_files_to_upload( $id ) {
	return ( ! empty( $_FILES ) ) && isset( $_FILES[ $id ] );
}
//------------------------------------------------------------------
}
$query=new MehrDPA();
?>