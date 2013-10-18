<?php
@$imconf->size->contact->large->w = 400;
@$imconf->size->contact->large->h = 400;
@$imconf->size->contact->inmap->w = 65;
@$imconf->size->contact->inmap->h = 65;
@$imconf->size->map->w = 0;
@$imconf->size->map->h = 0;

@$imconf->folder->base = '/home/ukmno/public_html/map/';
@$imconf->folder->circle = $imconf->folder->base . 'circle/';
@$imconf->folder->resources = '/home/ukmno/public_html/map/';

@$imconf->font = $imconf->folder->resources .'verdana.ttf';

@$imconf->resource->map = $imconf->folder->map.'kart.png';


///////////////////////////////
$coords = new StdClass;
$coords->finnmark 		= (object) array('x' => 590, 'y' => 132);
$coords->troms 			= (object) array('x' => 590, 'y' => 200);
$coords->nordland 		= (object) array('x' => 590, 'y' => 250);
$coords->nordtrondelag 	= (object) array('x' => 590, 'y' => 300);
$coords->sortrondelag	= (object) array('x' => 590, 'y' => 350);
$coords->moreogromsdal 	= (object) array('x' => 590, 'y' => 400);
$coords->sognogfjordane = (object) array('x' => 590, 'y' => 450);
$coords->hordaland 		= (object) array('x' => 590, 'y' => 500);
$coords->rogaland 		= (object) array('x' => 590, 'y' => 550);
$coords->vestagder 		= (object) array('x' => 590, 'y' => 600);
$coords->austagder		= (object) array('x' => 590, 'y' => 650);
$coords->telemark 		= (object) array('x' => 390, 'y' => 200);
$coords->vestfold 		= (object) array('x' => 390, 'y' => 250);
$coords->buskerud 		= (object) array('x' => 390, 'y' => 300);
$coords->oslo 			= (object) array('x' => 390, 'y' => 350);
$coords->ostfold		= (object) array('x' => 390, 'y' => 400);
$coords->akershus		= (object) array('x' => 390, 'y' => 450);
$coords->hedmark 		= (object) array('x' => 390, 'y' => 500);
$coords->oppland 		= (object) array('x' => 390, 'y' => 550);

///////////////////////////////