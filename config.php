<?php
@$imconf->size->contact->large->w = 400;
@$imconf->size->contact->large->h = 400;
@$imconf->size->contact->inmap->w = 225;
@$imconf->size->contact->inmap->h = 225;
@$imconf->size->map->w = 0;
@$imconf->size->map->h = 0;

@$imconf->folder->base 		= plugin_dir_path( __FILE__ );
@$imconf->folder->temp		= $imconf->folder->base .'temp/';
@$imconf->folder->original 	= $imconf->folder->temp . 'original/';
@$imconf->folder->cropped 	= $imconf->folder->temp . 'cropped/';
@$imconf->folder->circle 	= $imconf->folder->temp . 'circle/';
@$imconf->folder->scaled 	= $imconf->folder->temp . 'scaled/';
@$imconf->folder->resources = $imconf->folder->base .'resources/';
@$imconf->folder->maps 		= $imconf->folder->base .'map/';

@$imconf->url->maps = plugin_dir_url( __FILE__ ) .'map/';

@$imconf->font = $imconf->folder->resources .'verdana.ttf';
@$imconf->font_bold = $imconf->folder->resources .'verdanab.ttf';

@$imconf->resource->map = $imconf->folder->resources . 'norgeskart.png';