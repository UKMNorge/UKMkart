<?php
function create_square($kontakt, $file_original) {
	global $imconf;

	$file_square   = $imconf->folder->square   . $kontakt->bilde_navn .'.jpg';

	// COPY IMAGE TO TEMP DIR	
	$ch = curl_init($kontakt->bilde);
	$fp = fopen($file_original , 'wb');
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);


	switch($kontakt->bilde_ext) {
		case 'jpg':
		case 'jpeg':
			$image_original = imagecreatefromjpeg($file_original);
			break;
		case 'png':
			$image_original = imagecreatefrompng($file_original);
			break;
	}
	
	$width_original  = imagesx( $image_original );
	$height_original = imagesy( $image_original );
	
	if($width_original > $height_original) {
		$ratio = $imconf->size->contact->large->h / $height_original;
		$width_scaled	= $ratio * $width_original;
		$height_scaled	= $imconf->size->contact->large->h;
		$offsetX = ( $width_scaled - $imconf->size->contact->large->w ) / 2;
		$offsetY = 0;
	} else {
		$ratio = $imconf->size->contact->large->w / $width_original;
		$height_scaled = $ratio * $height_original;
		$width_scaled = $imconf->size->contact->large->w;
		$offsetY = ( $height_scaled - $imconf->size->contact->large->h ) / 2;
		$offsetX = 0;
	}
	
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
	
	
	$image_square = imagecreatetruecolor($imconf->size->contact->large->w, $imconf->size->contact->large->h);
	imagecopyresampled($image_square, // target image
					   $image_scale, // source image
					   0, // Destination X coord
					   0, // Destination Y coord
					   $offsetX, // Source X coord
					   $offsetY, // Source Y coord
					   $imconf->size->contact->large->w, // Destination width
					   $imconf->size->contact->large->h, // Destination height
					   $width_scaled,   // Source width
					   $height_scaled   // Source height
					   );
					   
	imagedestroy($image_scale);
	imagejpeg($image_square, $file_square);
	imagedestroy($image_square);
	
	$kontakt->bilde_kvadrat = $file_square;
}

function create_circle($kontakt, $file_input) {
	global $imconf;
	
	$file_circle	= $imconf->folder->circle . str_replace('.jpg','.png', $kontakt->bilde);
	
	$image_input = imagecreatefromjpeg($file_input);
	$input_width = imagesx($image_input);
	$input_height = imagesy($image_input);
	
	
	$image_circle = imagecreatetruecolor($imconf->size->contact->large->w, $imconf->size->contact->large->h);
	imagealphablending($image_circle, true);
	imagecopyresampled($image_circle, // target image
					   $image_input, // source image
					   0, // Destination X coord
					   0, // Destination Y coord
					   0, // Source X coord
					   0, // Source Y coord
					   $imconf->size->contact->large->w, // Destination width
					   $imconf->size->contact->large->h, // Destination height
					   $input_width,   // Source width
					   $input_height   // Source height
					   );
					   
	// Create mask				   
	$mask = imagecreatetruecolor($imconf->size->contact->large->w, $imconf->size->contact->large->h);
	// Create transparent color
	$transparent = imagecolorallocate($mask, 255, 0, 0);
	// Mask the mask ?
	imagecolortransparent($mask, $transparent);
	
	imagefilledellipse($mask, // Image resource (mask)
					   $imconf->size->contact->large->w/2, // x-coordinate of the center
					   $imconf->size->contact->large->h/2, // y-coordinate of the center
					   $imconf->size->contact->large->w -4, // The ellipse width
					   $imconf->size->contact->large->h -4, // The ellipse height
					   $transparent // The fill color ( A color identifier created with imagecolorallocate().)
					   );
					   
	$red = imagecolorallocate($mask, 0,0,0);
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
				  
	imagecolortransparent($image_circle, $red);
	imagefill($image_circle, 0,0, $red);
	
	
	imagepng($image_circle, $file_circle);
	imagedestroy($image_circle);
	imagedestroy($mask);
}

function map_contact($kontakt) {
	global $imconf, $image_map;
	
	$coords = map_coordinates($kontakt->fylke->navn);
	$coords->name = (object) array('x' => $coords->x + ($imconf->size->contact->inmap->w / 2),
								   'y' => $coords->y + $imconf->size->contact->inmap->h + 10);
	$coords->fylke = (object) array('x' => $coords->name->x,
									'y' => $coords->name->y + 11);

	$file_contact = $imconf->folder->circle. str_replace('.jpg','.png', $kontakt->bilde);
	
	$image_contact = imagecreatefrompng($file_contact);
	$width_contact = imagesx($image_contact);
	$height_contact= imagesy($image_contact);
	
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
					   
	map_text($image_map, $kontakt->navn, 9, $fontcolor, $coords->name);
	map_text($image_map, $kontakt->fylke->navn, 9, $fontcolor, $coords->fylke);
}

function map_text($image, $text, $fontsize, $fontcolor, $coords) {


	global $imconf;
	
	$textbox = imagettfbbox($fontsize, // Font size
							0, // Angle
							$imconf->font, // Font file
							$text // Text
							);
	$text_width = $textbox[2];
	$text_centerpoint = $text_width / 2;	

	imagettftext($image, // Target image
				 $fontsize, // Font size
				 0, // Angle
				 $coords->x - $text_centerpoint, // Destination X
				 $coords->y, // Destination Y
				 $fontcolor, // Color
				 $imconf->font, // Font path
				 $text // TEXT
				 );
}

function map_coordinates($fylke) {
	global $coords;
	$fylke = strtolower($fylke);
	return $coords->$fylke;
}