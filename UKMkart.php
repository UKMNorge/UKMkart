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


## HOOK MENU AND SCRIPTS
if(is_admin()) {
	global $blog_id, $UKMkart_GD_LOG_GROUP, $UKMkart_GD_LOG;
	if($blog_id == 1)
		add_action('admin_menu', 'UKMkart_menu',200);
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
	$page = add_menu_page('Kart', 'Kart', 'editor', 'UKMkart', 'UKMkart', 'http://ico.ukm.no/hus-menu.png',499);
	add_action( 'admin_print_styles-' . $page, 'UKMkart_script' );
}

## INCLUDE SCRIPTS
function UKMkart_script() {
	wp_enqueue_script('bootstrap_js');
	wp_enqueue_style('bootstrap_css');

	wp_enqueue_style('UKMkart_css', plugin_dir_url( __FILE__ ).'ukmkart.css');
}

function UKMkart() {
	if(!isset($_GET['action']))
		$_GET['action'] = 'info';

	switch($_GET['action']) {
		case 'info': 
			echo TWIG('layout.twig.html', array(), dirname(__FILE__) );
			break;
		case 'urg':
			require_once('controller_urg.inc.php');
			$infos['pagetitle'] = 'URG';
			echo TWIG('kartgen.twig.html', $infos, dirname( __FILE__ ));
			break;
		case 'ukm':
			$infos['pagetitle'] = 'Fylkeskontakter';
			require_once('controller_ukm.inc.php');
			echo TWIG('kartgen.twig.html', $infos, dirname( __FILE__ ));
			break;
	}
}
/*

$kontakt = new StdClass;
$kontakt->fylke = new StdClass;
$kontakt->navn = 'Astrid';
$kontakt->fylke->id = 20;
$kontakt->fylke->navn = 'Finnmark';
$kontakt->bilde = 'byasen.jpg';
$kontakter[] = $kontakt;


// GENERER SIRKEL-BILDER AV ALLE KONTAKTER
	foreach($kontakter as $kontakt) {
		create_circle($kontakt);
	}

// GENERER KART MED ALLE SIRKELBILDER
	// LOAD MAP TO GD
	$image_map = imagecreatefrompng($imconf->resource->map);
	$imconf->size->map->w = imagesx($image_map);
	$imconf->size->map->h = imagesy($image_map);
	
	// DEFINE COLORS
	$fontcolor = imagecolorallocate($image_map, 30,74,69);
	
	// PER CONTACT
	foreach($kontakter as $kontakt) {
		map_contact($kontakt);
	}	
	
	// WRITE IMAGE
	header('Content-type: image/png');
	imagepng($image_map);
	imagedestroy($image_contact);
	imagedestroy($image_map);
*/