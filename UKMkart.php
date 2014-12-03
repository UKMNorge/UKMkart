<?php  
/* 
Plugin Name: UKM Kart
Plugin URI: http://www.ukm-norge.no
Description: Genererer kart over fylkeskontakter og URGrepresentanter
Author: UKM Norge / M Mandal 
Version: 1.0 
Author URI: http://www.ukm-norge.no
*/
require_once('config.php');
require_once('functions.inc.php');
$UKMkart_GD_LOG = array();
$UKMkart_GD_LOG_GROUP = '';

add_filter('UKMWPNETWDASH_messages', 'UKMkart_network_dash_messages');

function UKMkart_network_dash_messages( $MESSAGES ) {
	require_once('UKM/kontakt.class.php');
	$kontakter = array();
	$res = sql_res('ukm.no');
	
	$ERROR = 0;	
	while( $r = mysql_fetch_assoc( $res ) ) {
		$object = new kontakt( $r['id'] );
		$image = $object->get('image');
		if( isset( $_GET['debug'] ) ) {
			var_dump( $image );
		}
		if( empty( $image ) || strpos( $image, 'placeholder/person' ) != false ) {
			$ERROR++;
		}
	}
	if( $ERROR ) {
		$MESSAGES[] = array('level' 	=> 'alert-error',
							'module'	=> 'UKMkart',
							'header'	=> $ERROR . ' fylkeskontakter har ikke bilde!',
							'body' 		=> 'Grunnet kontaktkartet på om.ukm.no er dette mer kritisk enn det høres ut til',
							'link'		=> 'admin.php?page=UKMkart&action=ukm'
					);
	}
	return $MESSAGES;
}

## HOOK MENU AND SCRIPTS
if(is_admin()) {
	global $blog_id, $UKMkart_GD_LOG_GROUP, $UKMkart_GD_LOG;
	if($blog_id == 1)
		add_action('network_admin_menu', 'UKMkart_menu');
		
	add_action('UKMmonstring_save_contact', 'UKMkart_update');
}

function lg($group) {
	global $UKMkart_GD_LOG_GROUP;
	$UKMkart_GD_LOG_GROUP = $group;
}
function l($message,$level='neutral') {
	global $UKMkart_GD_LOG, $UKMkart_GD_LOG_GROUP;
	$UKMkart_GD_LOG[] = array('group'=> $UKMkart_GD_LOG_GROUP, 'level' => $level, 'message' => $message);
}


function UKMkart_menu() {
	$page = add_menu_page('Kart', 'Kart', 'editor', 'UKMkart', 'UKMkart', 'http://ico.ukm.no/map-menu.png',130);
	add_action( 'admin_print_styles-' . $page, 'UKMkart_script' );
}

## INCLUDE SCRIPTS
function UKMkart_script() {
	wp_enqueue_script('bootstrap_js');
	wp_enqueue_style('bootstrap_css');

	//wp_enqueue_style('UKMkart_css', plugin_dir_url( __FILE__ ).'ukmkart.css');
}

function UKMkart() {
	global $imconf;
	if(!isset($_GET['action']))
		$_GET['action'] = 'info';

	switch($_GET['action']) {
		case 'info': 
			$infos['active_tab'] = $_GET['action'];
			echo TWIG('layout.twig.html', array('map_url' => $imconf->url->maps) , dirname(__FILE__) );
			break;
		case 'urg':
			require_once('controller_urg.inc.php');
			$infos['pagetitle'] = 'URG';
			$infos['active_tab'] = $_GET['action'];
			echo TWIG('kartgen.twig.html', $infos, dirname( __FILE__ ));
			break;
		case 'ukm':
			require_once('controller_ukm.inc.php');
			$infos['pagetitle'] = 'Fylkeskontakt';
			$infos['active_tab'] = $_GET['action'];
			echo TWIG('kartgen.twig.html', $infos, dirname( __FILE__ ));
			break;
	}
}

function UKMkart_update($contact_id) {
	if( is_numeric( $contact_id ) ) {
		require_once('UKM/kontakt.class.php');
		
		$kontakt = new kontakt( $_POST['c_id'] );
		$locked = $kontakt->g('system_locked');
		
		if($locked == 'true') {
			$email = $kontakt->g('email');
			// Re-gen URG-kart (UKMkart-module)
			if(strpos( $email, '@urg.ukm.no') ) {
				require_once(plugin_dir_path(__FILE__).'controller_urg.inc.php');
			} elseif ( strpos( $email , '@ukm.no') ) {
				require_once(plugin_dir_path(__FILE__).'controller_ukm.inc.php');
			}
		}
	}

}