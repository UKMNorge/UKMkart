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
	$kontakt->epost = $object->get('email');
	$kontakt->mobil = $object->get('tlf');
	
	// NEW IMAGE NAME
	$kontakt->bilde = $object->get('image');
	$kontakt->bilde_navn = $place->g('url').'.jpg';
	$kontakt->bilde_ext = substr($kontakt->bilde, strrpos( $kontakt->bilde, '.')+1);
	
	$kontakter[] = $kontakt;
		
	// FIX CONTACT IMAGE
	$file_original = $imconf->folder->original . $kontakt->bilde_navn .'.'. $kontakt->bilde_ext;

	$file_square = create_square($kontakt, $file_original);
	create_circle($kontakt, $file_square);
}

$infos = array('kontakter' => $kontakter);