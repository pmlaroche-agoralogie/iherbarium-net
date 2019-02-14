<?php
function convertSexa1coord($var, $pos)
{ // D?cimal vers sexag?simal
    
    if ($pos == 'lat')
    {
        if ($var > 0)$card = 'N';
        else $card = 'S';
    }
    if ($pos == 'long')
    {
        if ($var > 0)$card = 'E';
        else $card = 'O';
    }
    
    $var = abs($var);
    $deg = intval($var);
    $min = ($var - $deg)*60;
    $sec = ($min - intval($min))*60;
    return str_pad($deg, 2, '0', STR_PAD_LEFT).'&deg;'.intval($min)."'".number_format($sec, 2).'"'.$card;
}

function convertSexa2coord($lat, $long)
{
    return convertSexa1coord($lat,'lat'). " , ".convertSexa1coord($long,'long');
}

/*Fonction qui calcule la latitude contenue dans l'image envoyée */
function calcul_latitude_exif($exif){
    $lat1=$exif["GPS"]["GPSLatitude"][0];
    $lat1decoupee=explode("/",$lat1);
    $lat2=$exif["GPS"]["GPSLatitude"][1];
    $lat2decoupee=explode("/",$lat2);
    $lat2final=($lat2decoupee[0]/$lat2decoupee[1])/60;
    $latitude=$lat1decoupee[0]+$lat2final;
    
    if ($exif["GPS"]["GPSLatitudeRef"] =="S")$latitude = -$latitude;
    return $latitude;
}

/*fonction permettant d'obtenir la longitude contenue dans l'image */
function calcul_longitude_exif($exif){
    $long1=$exif["GPS"]["GPSLongitude"][0];
    $long1decoupee=explode("/",$long1);
    $long2=$exif["GPS"]["GPSLongitude"][1];
    $long2decoupee=explode("/",$long2);
    $long2final=($long2decoupee[0]/$long2decoupee[1])/60;
    $longitude=$long1decoupee[0]+$long2final;
    
    if ($exif["GPS"]["GPSLongitudeRef"] =="W")$longitude = -$longitude;
    return $longitude;
}

/* Fonction qui permet de redimensionner l'image que l'utilisateur nous a envoyé */
function redimensionner_image($image_source,$taillemax,$image_destination){
    $dim=getimagesize($image_source);  //la variable dim contiendra la taille de l'image passée en paramètre
    $largeur=$dim[0];
    $hauteur=$dim[1];
    
    //calcul des nouvelles dimensions de l'image
    if($largeur>$hauteur){
        $new_hauteur=$hauteur*(($taillemax/$largeur));
        $new_largeur=$taillemax;
    }
    else {
        $new_largeur=$largeur*(($taillemax)/$hauteur);
        $new_hauteur=$taillemax;
    }
    
    // Redimensionnement
    $image_p = imagecreatetruecolor($new_largeur, $new_hauteur);
    $image_cree = imagecreatefromjpeg($image_source);
    imagecopyresampled($image_p, $image_cree, 0, 0, 0, 0, $new_largeur, $new_hauteur, $largeur, $hauteur);
    
    // on place l'image redimensionnée dans le répertoire repertoire_vignettes
    imagejpeg($image_p,$image_destination, 100);
}