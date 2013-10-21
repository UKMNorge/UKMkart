<?php
require_once('UKM/kontakt.class.php');
require_once('UKM/monstring.class.php');

$sql = new SQL("SELECT `con`.`id`,
					   `pl`.`pl_id`
				FROM `smartukm_contacts` AS `con`
				LEFT JOIN `smartukm_rel_pl_ab` AS `rel` ON (`rel`.`ab_id` = `con`.`id`)
				LEFT JOIN `smartukm_place` AS `pl` ON (`pl`.`pl_id` = `rel`.`pl_id`)
				WHERE `system_locked` = 'true'
				AND `email` LIKE '%@urg.ukm.no%'
				AND `season` = '#season'
				ORDER BY `pl`.`pl_name` ASC",
			array('season' => get_option('season')));
				
$res = $sql->run();

global $imconf;
$kontakter = array();
$MAPNAME = 'URG';

while( $r = mysql_fetch_assoc( $res ) ) {
	
	// CREATE A CONTACT OBJECT FOR MAP
	$object = new kontakt( $r['id'] );
	$place = new monstring( $r['pl_id'] );
	$kontakt = new StdClass;
	$kontakt->fylke = new StdClass;
	
	$kontakt->navn = $object->get('firstname');
	$kontakt->fylke->id = $place->get('pl_fylke');
	$kontakt->fylke->navn = $place->get('pl_name');
	$kontakt->epost = $object->get('email');
	$kontakt->mobil = $object->get('tlf');
	
	// NEW IMAGE NAME
	$kontakt->bilde = $object->get('image');
	$kontakt->bilde_navn = $place->g('url');
	
	$kontakter[] = $kontakt;

	// READ EXTERNAL FILE, STORE IN WORKING DIR WITH CORRECT NAME!
	
	$extension = substr($kontakt->bilde, strrpos( $kontakt->bilde, '.')+1);
	$filename = $kontakt->bilde_navn .'.'. $extension;
	$filewrite = $imconf->folder->original . $filename;
	
	lg($kontakt->fylke->navn);
	l('NAME: ' .$kontakt->navn .' (FylkeID: '. $kontakt->fylke->id .')');
	l('Read image URL: '. $kontakt->bilde);
	l('Filename: '. $filename);
	l('Store image at: '. $filewrite);
	
	$ch = curl_init($kontakt->bilde);
	$fp = fopen($filewrite , 'wb');
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
	
	$kontakt->map_image = create_circle( $filename );

	l('CIRCLE IMAGE CREATED', 'success');
}

lg('MAP THE MAP');
	// LOAD MAP TO GD
	l('MAP IS LOCATED AT: '. $imconf->resource->map);
	$image_map = imagecreatefrompng($imconf->resource->map);
	$imconf->size->map->w = imagesx($image_map);
	$imconf->size->map->h = imagesy($image_map);
	
	l('MAP DIMENSIONS: '. $imconf->size->map->w .'x'. $imconf->size->map->h);
	// DEFINE COLORS
	$fontcolor = imagecolorallocate($image_map, 30,74,69);
	
	// PER CONTACT
	foreach($kontakter as $kontakt) {
		map_contact($image_map, $kontakt);
	}	
	
	// WRITE IMAGE
	imagepng($image_map, $imconf->folder->maps . $MAPNAME .'.png');
	imagedestroy($image_map);


global $UKMkart_GD_LOG;
$infos = array('kontakter' => $kontakter, 'log' => $UKMkart_GD_LOG);