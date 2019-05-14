<?php

$connect_db = mysqli_connect('localhost','usrwphistnat','osh7z9Pxjs','bswphistnat');


if(!isset($_GET['debut']))die();
$partie = $_GET['debut'];
$partie = mysqli_real_escape_string($connect_db,$partie);
$genre_obs = $_GET['genre_obs'];
$genre_obs = mysqli_real_escape_string($connect_db,$genre_obs);
$limite_france = $_GET['limite_france'];
$limite_france = mysqli_real_escape_string($connect_db,$limite_france);

if(strlen($partie)>3){
	header('Content-Type: text/xml;charset=utf-8');
	echo(utf8_encode("<?xml version='1.0' encoding='UTF-8' ?><options>"));

	$partie = utf8_decode($partie);

	switch ($genre_obs) {
    case '1':
        $regne = 'Plantae';
        break;
    case '3':
        $regne = 'Fungi';
        break;
    case '7':
        $regne = 'Animalia';
        break;
	}
	
	$clause_where_en_plus = '';
	if ($limite_france==1){
		$clause_where_en_plus = " AND FR = 'P'";
	}
	
	$sql = "SELECT DISTINCT NOM_COMPLET,CD_NOM FROM  `iherba_taxref12_es` WHERE `REGNE` = '".$regne."' AND (`LB_NOM1` LIKE '$partie%' OR `LB_NOM2` LIKE '$partie%')".$clause_where_en_plus." ORDER BY NOM_COMPLET";

  	$possibilities = mysqli_query($connect_db,$sql);
  	$nbTrouve = mysqli_num_rows($possibilities);

  	if ($nbTrouve > 0) {   
    	while ($fiche = mysqli_fetch_assoc($possibilities)) {
			echo('<option class="liste_completion">'.str_replace('&','-',$fiche['NOM_COMPLET']). " / ".$fiche['CD_NOM'].'</option>'); 
		}
  	}

  	echo("</options>");
  	die();
}


?>