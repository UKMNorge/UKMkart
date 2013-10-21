<?php
require_once('UKM/kontakt.class.php');

$sql = new SQL("SELECT `con`.`id`,
					   `pl`.`pl_fylke`,
					   `pl`.`pl_name`
				FROM `smartukm_contacts` AS `con`
				LEFT JOIN `smartukm_rel_pl_ab` AS `rel` ON (`rel`.`ab_id` = `con`.`id`)
				LEFT JOIN `smartukm_place` AS `pl` ON (`pl`.`pl_id` = `rel`.`pl_id`)
				WHERE `system_locked` = 'true'
				AND `email` LIKE '%@urg.ukm.no%'
				AND `season` = '#season'",
			array('season' => get_option('season')));
				
$res = $sql->run();

$kontakter = array();

while( $r = mysql_fetch_assoc( $res ) ) {
	$object = new kontakt( $r['id'] );
	
	$kontakt = new StdClass;
	$kontakt->fylke = new StdClass;
	
	$kontakt->navn = $object->get('firstname');
	$kontakt->fylke->id = $r['pl_fylke'];
	$kontakt->fylke->navn = utf8_encode($r['pl_name']);
	$kontakt->bilde = $object->get('image');
	$kontakt->epost = $object->get('email');
	$kontakt->mobil = $object->get('tlf');
	$kontakter[] = $kontakt;
}

$infos = array('kontakter' => $kontakter);