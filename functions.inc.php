<?php
function scale_and_crop( $filename_in_original_folder ) {
	l('SCALE AND CROP');
	global $imconf;

	$filename = basename( $filename_in_original_folder );
	$fileext  = substr($filename_in_original_folder, strrpos( $filename_in_original_folder, '.')+1);
	
	$file_original = $imconf->folder->original . $filename;
	$file_crop   = $imconf->folder->cropped . str_replace('.'.$fileext, '.png', $filename);
	$file_scale   = $imconf->folder->scaled . str_replace('.'.$fileext, '.png', $filename);

	l('Read original at: ' . $file_original);
	l('Original is: '. $fileext);	
	switch($fileext) {
		case 'jpg':
		case 'jpeg':
			$image_original = @imagecreatefromjpeg($file_original);
			if(!$image_original) {
				l('GD says this is not JPG. Trying PNG', 'error');
				$image_original = imagecreatefrompng($file_original);
			}
			break;
		case 'png':
			$image_original = @imagecreatefrompng($file_original);
			if(!$image_original) {
				l('GD says this is not PNG. Trying JPG', 'error');
				$image_original = imagecreatefromjpeg($file_original);
			}
			break;
	}
	
	$width_original  = imagesx( $image_original );
	$height_original = imagesy( $image_original );
	
	if($width_original > $height_original) {
		l('Image is landscape');
		$ratio = $imconf->size->contact->large->h / $height_original;
		$width_scaled	= (int) ($ratio * $width_original);
		$height_scaled	= (int) ($imconf->size->contact->large->h);
		$offsetX = (int) (( $width_scaled - $imconf->size->contact->large->w ) / 2);
		$offsetY = 0;
	} else {
		l('Image is portrait');
		$ratio = $imconf->size->contact->large->w / $width_original;
		$height_scaled = (int) ($ratio * $height_original);
		$width_scaled = (int) ($imconf->size->contact->large->w);
		$offsetY = (int) (( $height_scaled - $imconf->size->contact->large->h ) / 2);
		$offsetX = 0;
	}

	l('Scale ratio: '. $ratio);	
	l('Scale image from: '. $width_original .'x'. $height_original .' to '. $width_scaled .'x'. $height_scaled);
	
	$image_scale = imagecreatetruecolor($width_scaled, $height_scaled);
	imagecopyresampled($image_scale, // target image
					   $image_original, // source image
					   0, // Destination X coord
					   0, // Destination Y coord
					   0, // Source X coord
					   0, // Source Y coord
					   $width_scaled, // Destination width
					   $height_scaled, // Destination height
					   $width_original,   // Source width
					   $height_original   // Source height
					   );
	l('Crop image into: '. $imconf->size->contact->large->w .'x'. $imconf->size->contact->large->h);
	l('Offset original by: '. $offsetX .'x'. $offsetY);
	$width_scaled_offset = (int) $width_scaled - ($offsetX*2);
	$height_scaled_offset= (int) $height_scaled -($offsetY*2);
	l('At offset, crop dimensions is '. $width_scaled_offset .'x'. $height_scaled_offset);
	$image_crop = imagecreatetruecolor($imconf->size->contact->large->w, $imconf->size->contact->large->h);
	imagecopyresampled($image_crop, // target image
					   $image_scale, // source image
					   0, // Destination X coord
					   0, // Destination Y coord
					   $offsetX, // Source X coord
					   $offsetY, // Source Y coord
					   $imconf->size->contact->large->w, // Destination width
					   $imconf->size->contact->large->h, // Destination height
					   $width_scaled_offset,   // Source width
					   $height_scaled_offset   // Source height
					   );
					   
	l('Store scaled image at: '. $file_scale);
	imagepng($image_scale, $file_scale);
	
	l('Store cropped image at: '. $file_crop);

	imagedestroy($image_scale);
	imagepng($image_crop, $file_crop);
	imagedestroy($image_crop);
	
	return $file_crop;
}

//function create_circle($kontakt, $file_input) {
function create_circle( $filename_in_original_folder ) {
	global $imconf;
	
	// SCALE AND CROP SQUARE FIRST
	$file_scaled = scale_and_crop( $filename_in_original_folder );
	$filename = basename( $file_scaled );
	
	$file_circle	= $imconf->folder->circle . $filename;

	l('CREATE CIRCLE');
	l('Read scaled image from: ' . $file_scaled);
	$image_scaled = imagecreatefrompng($file_scaled);
	$width_scaled = imagesx($image_scaled);
	$height_scaled = imagesy($image_scaled);
	
	l('Create circle image: '. $imconf->size->contact->large->w .'x'. $imconf->size->contact->large->h);
	$image_circle = imagecreatetruecolor($imconf->size->contact->large->w, $imconf->size->contact->large->h);
	imagealphablending($image_circle, true);
	imagecopyresampled($image_circle, // target image
					   $image_scaled, // source image
					   0, // Destination X coord
					   0, // Destination Y coord
					   0, // Source X coord
					   0, // Source Y coord
					   $imconf->size->contact->large->w, // Destination width
					   $imconf->size->contact->large->h, // Destination height
					   $width_scaled,   // Source width
					   $height_scaled   // Source height
					   );
					   
	// Create mask				   
	$mask = imagecreatetruecolor($imconf->size->contact->large->w, $imconf->size->contact->large->h);
	// Create transparent color
	$transparent = imagecolorallocatealpha($mask, 0, 0, 0, 127);
	$bordercolor = imagecolorallocate($mask, 30,74,69);
	// Mask the mask ?
	imagecolortransparent($mask, $transparent);

	imagefilledellipse($mask, // Image resource (mask)
					   $imconf->size->contact->large->w/2, // x-coordinate of the center
					   $imconf->size->contact->large->h/2, // y-coordinate of the center
					   $imconf->size->contact->large->w -4, // The ellipse width
					   $imconf->size->contact->large->h -4, // The ellipse height
					   $bordercolor // The fill color ( A color identifier created with imagecolorallocate().)
					   );
	
	imagefilledellipse($mask, // Image resource (mask)
					   $imconf->size->contact->large->w/2, // x-coordinate of the center
					   $imconf->size->contact->large->h/2, // y-coordinate of the center
					   $imconf->size->contact->large->w -20, // The ellipse width
					   $imconf->size->contact->large->h -20, // The ellipse height
					   $transparent // The fill color ( A color identifier created with imagecolorallocate().)
					   );
					   
//	$red = imagecolorallocatealpha($mask, 0,0,0, 127);
	imagecopymerge($image_circle, // Destination image
				  $mask, // Source image
				  0, // Destination X coord
				  0, // Destination Y coord
				  0, // Source X coord
				  0, // Source Y coord
				  $imconf->size->contact->large->w, // New width
				  $imconf->size->contact->large->h, // New hight
				  100 // Some merge param..
				  ); 
				  
//	imagecolortransparent($image_circle, $red);
	imagefill($image_circle, 0,0, $transparent);

	imagealphablending($image_circle, false); 
	imagesavealpha($image_circle, true);


	l('Store circle image at: '. $file_circle);	
	imagepng($image_circle, $file_circle);
	imagedestroy($image_circle);
	imagedestroy($mask);
	
	return $file_circle;
}

function map_contact($image_map, $kontakt, $fontcolor) {
	global $imconf;
	
	$coords = map_coordinates($kontakt->fylke->koord_navn, $imconf->size->contact->inmap->w, $imconf->size->contact->inmap->h);
	$coords->name = (object) array('x' => (int) ($coords->x + ($imconf->size->contact->inmap->w / 2)),
								   'y' => (int) ($coords->y + $imconf->size->contact->inmap->h + 5));
	$coords->fylke = (object) array('x' => (int) $coords->name->x,
									'y' => (int) $coords->name->y + 3);

	l('Mapping '. $kontakt->fylke->navn .' @ '. $coords->fylke->x .'x'. $coords->fylke->y);
	$file_contact = $kontakt->map_image;
	
	l('Loading image at: '. $file_contact);
	$image_contact = imagecreatefrompng($file_contact);
	$width_contact = imagesx($image_contact);
	$height_contact= imagesy($image_contact);
	
	l('Scaling image from '. $width_contact .'x'. $height_contact .' to '. $imconf->size->contact->inmap->w .'x'. $imconf->size->contact->inmap->h);
	
	imagecopyresampled($image_map, // target image
					   $image_contact, // source image
					   $coords->x, // Destination X coord
					   $coords->y, // Destination Y coord
					   0, // Source X coord
					   0, // Source Y coord
					   $imconf->size->contact->inmap->w, // Destination width
					   $imconf->size->contact->inmap->h, // Destination height
					   $width_contact,   // Source width
					   $height_contact   // Source height
					   );
					   
	$height = map_text($image_map, $kontakt->navn, 30, $fontcolor, $imconf->font, $coords->name);
	$coords->fylke->y += $height;
	map_text($image_map, $kontakt->fylke->navn, 30, $fontcolor, $imconf->font_bold, $coords->fylke);
	imagedestroy( $image_contact );
}

function map_text($image, $text, $fontsize, $fontcolor, $font, $coords) {
	global $imconf;
	
	l('Writing '. $text);
	$textbox = imagettfbbox($fontsize, // Font size
							0, // Angle
							$imconf->font, // Font file
							$text // Text
							);
	$text_width = $textbox[2];
	// SEE http://stackoverflow.com/questions/6737419/php-imagettftext-baseline-workaround
	$ascent = abs($textbox[7]);
	$descent = abs($textbox[1]);
	$text_heigth = $ascent + $descent;

	$text_centerpoint = (int) $text_width / 2;	

	l('Textbox size is '. $text_width .'x'. $text_heigth);
	l('Text centerpoint is '. $text_centerpoint .' and should center around @ '. ($coords->x - $text_centerpoint) .'x'. ($coords->y + $text_heigth));
	imagettftext($image, // Target image
				 $fontsize, // Font size
				 0, // Angle
				 $coords->x - $text_centerpoint, // Destination X
				 $coords->y + $text_heigth, // Destination Y
				 $fontcolor, // Color
				 $font, // Font path
				 $text // TEXT
				 );
	return $text_heigth;
}

function map_coordinates($fylke, $width, $height) {
	$fylke = str_replace('-','',strtolower($fylke));
	l('Find coordinates for '. $fylke);
	
	$coords = new StdClass;
	$coords->finnmark 		= (object) array('x' => 2350,	'y' => 630);
	$coords->troms 			= (object) array('x' => 2050,	'y' => 740);
	$coords->nordland 		= (object) array('x' => 1785,	'y' => 940);
	$coords->nordtrondelag 	= (object) array('x' => 1590,	'y' => 1280);
	$coords->sortrondelag	= (object) array('x' => 795,	'y' => 1070);
	$coords->moreogromsdal 	= (object) array('x' => 460,	'y' => 1250);
	$coords->sognogfjordane = (object) array('x' => 220,	'y' => 1570);
	$coords->hordaland 		= (object) array('x' => 220,	'y' => 1915);
	$coords->rogaland 		= (object) array('x' => 220,	'y' => 2250);
	$coords->vestagder 		= (object) array('x' => 470,	'y' => 2580);
	$coords->austagder		= (object) array('x' => 730,	'y' => 2580);
	$coords->telemark 		= (object) array('x' => 990,	'y' => 2580);
	$coords->vestfold 		= (object) array('x' => 1250,	'y' => 2580);
	$coords->buskerud 		= (object) array('x' => 1510,	'y' => 2580);
	$coords->oslo 			= (object) array('x' => 1780,	'y' => 2580);
	$coords->ostfold		= (object) array('x' => 2050,	'y' => 2580);
	$coords->akershus		= (object) array('x' => 1550, 	'y' => 2010);
	$coords->hedmark 		= (object) array('x' => 1880,	'y' => 1880);
	$coords->oppland 		= (object) array('x' => 1550,	'y' => 1590);

	if(isset($coords->$fylke)) {
		l('Found coordinates!');
		$coords->$fylke->x = $coords->$fylke->x - (int) ($width/2);
		$coords->$fylke->y = $coords->$fylke->y - (int) ($height/2);
	}
	else {
		l('Oops! Could not find coordinates for '. $fylke, 'error');
		$coords->$fylke = (object) array('x'=>0,'y'=>0);
	}
	return $coords->$fylke;
}