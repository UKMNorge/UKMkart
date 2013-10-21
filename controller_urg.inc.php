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

$kontakter = array();

while( $r = mysql_fetch_assoc( $res ) ) {
	
	// CREATE A CONTACT OBJECT FOR MAP
	$object = new kontakt( $r['id'] );
	$place = new monstring( $r['pl_id'] );
	$kontakt = new StdClass;
	$kontakt->fylke = new StdClass;
	
	$kontakt->navn = $object->get('firstname');
	$kontakt->fylke->id = $place->get('pl_fylke');
	$kontakt->fylke->navn = $place->get('pl_name');
	$kontakt->bilde = $object->get('image');
	$kontakt->epost = $object->get('email');
	$kontakt->mobil = $object->get('tlf');
	$kontakter[] = $kontakt;
	
/*
	// NEW IMAGE NAME
	$lastdot = strrpos( $kontakt->bilde, '.');
	$ext = substr($kontakt->bilde, $lastdot+1);
	$orig_name = $imconf->folder->original . $place->g('url') . '.'. $ext;
	
	// COPY IMAGE TO WORK DIR	
	$ch = curl_init($kontakt->bilde);
	$fp = fopen($orig_name , 'wb');
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
*/
}

var_dump($kontakter);

var_dump($sql);
$infos = array('kontakter' => $kontakter);