<?php

$SERVEUR= 'localhost';
$BASE = 'bswphistnat';
$USER = 'usrwphistnat';
$MDP = 'osh7z9Pxjs';

$connect_db = mysqli_connect($SERVEUR,$USER,$MDP,$BASE);
	
	
	
	
function get_adress_from_loc($latitude,$longitude){	
	
	$town = '';
	
	$data = array(
	  'lat'       => $latitude,
	  'lon'    => $longitude,
	  'format'     => 'json',
	  'addressdetails' => 1,
	);
	
	
	$url = 'https://nominatim.openstreetmap.org/reverse?'.http_build_query($data);
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	$geopos = curl_exec($ch);
	curl_close($ch);
var_dump($geopos);
	if ($geopos != FALSE){
		$json_data = json_decode($geopos, true);
var_dump($json_data);
		if (isset($json_data['address'])){
			$array_address = $json_data['address'];
		
			$useful_adress = array();
		  
			if(isset($array_address['village']))$useful_adress[]=$array_address['village'];
			if(isset($array_address['suburb']))if(!in_array($array_address['suburb'],$useful_adress))$useful_adress[]=$array_address['suburb'];
			if(isset($array_address['city']))if(!in_array($array_address['city'],$useful_adress))$useful_adress[]=$array_address['city'];
			if(isset($array_address['county']))if(!in_array($array_address['county'],$useful_adress))$useful_adress[]=$array_address['county'];
			
			if (count($useful_adress) > 0){
				$town = implode(' - ',$useful_adress);
				$town = $town." [OSM]";
			}
		}
	}else{
		echo "<br>Localisation non trouvée";
	}
	
	return $town;
}


// on récupère les fiches qui ont une latitude et longitude mais qui n'ont pas d'adresse
$requetesql = "SELECT idobs,latitude,longitude FROM iherba_observations WHERE idobs=7925";
$resultat = mysqli_query($connect_db,$requetesql);	


$nb = 0;
			
while ($rangee = mysqli_fetch_assoc($resultat)) {

	$address = get_adress_from_loc($rangee['latitude'],$rangee['longitude']);
	
	echo "<br>Adresse trouvée : ".$address;
	/*if ($address != ''){
		// on enregistre l'adresse dans le champ correspondant
		$requetesql_update = "UPDATE iherba_observations set address = '".mysqli_real_escape_string($connect_db,$address)."' WHERE idobs = ".$rangee['idobs'];
		echo "<br>".$requetesql_update;
		$resultat_update = mysqli_query($connect_db,$requetesql_update);
		$nb++;
	}*/

	
}


?>