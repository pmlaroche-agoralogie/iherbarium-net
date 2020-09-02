 <?php
include_once('../wp-load.php');
global $wpdb;

$GLOBALS['normalizeChars'] = array(
    'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 
    'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 
    'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 
    'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 
    'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 
    'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 
    'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f'
);

function cleanForShortURL($toClean) {
    $toClean     =     str_replace('&', '-and-', $toClean);
    $toClean     =    trim(preg_replace('/[^\w\d_ -]/si', '', $toClean));//remove all illegal chars
    $toClean     =     str_replace(' ', '-', $toClean);
    $toClean     =     str_replace('--', '-', $toClean);
    
    return strtr($toClean, $GLOBALS['normalizeChars']);
}
$update =1;

	$sql_determ="
        SELECT idobs ,  iherba_determination.nom_scientifique,iherba_determination.nom_commun,tropicosid,tropicosgenusid,tropicosfamilyid
            FROM iherba_observations
            INNER JOIN iherba_determination ON iherba_observations.idobs =  iherba_determination.id_obs
	 ORDER BY nom_scientifique ";
	$result_determ= $wpdb->get_results( $sql_determ , ARRAY_A );
	if (sizeof($result_determ)==0)
	{
    	echo "Erreur dans la récupération des observations";
    	die();
	}
	foreach ($result_determ as $row_quest){
				if(($update==1)&&($row_quest['nom_scientifique'] !=''))
				 {
					$sqlmajurl = "UPDATE  `iherba_observations` SET  `url_rewriting_fr` =  '".cleanForShortURL($row_quest['nom_scientifique'])."' WHERE  `iherba_observations`.`idobs` =".$row_quest['idobs'];
					$wpdb->query($sqlmajurl);
					$sqlmajurl = "UPDATE  `iherba_observations` SET  `url_rewriting_en` =  '".cleanForShortURL($row_quest['nom_scientifique'])."' WHERE  `iherba_observations`.`idobs` =".$row_quest['idobs'];
					$wpdb->query($sqlmajurl);		
				 }
	}
	echo "URL Rewriting bien synchronises";
?>
