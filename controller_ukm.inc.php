<?php
require_once('UKM/kontakt.class.php');
require_once('UKM/monstring.class.php');

$mapUrl = gen_map('fylkeskontaktene', 'ukm.no');

global $UKMkart_GD_LOG;
$infos = array('kontakter' => $kontakter, 'log' => $UKMkart_GD_LOG, 'kart' => $mapUrl);