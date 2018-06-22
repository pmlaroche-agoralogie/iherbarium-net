<?php
/*
 Plugin Name: iHerbarium
 Plugin URI: http://www.iherbarium.net/
 Description: iHerbarium
 Version: 1.0
 Author: Agoralogie
 Author URI: http://www.agoralogie.fr
 Text Domain: iherbarium
 */

class iHerbarium {
    public $domaine_photo = "http://migration.iherbarium.fr";
    
    public function __construct()
    {
        add_shortcode('iHerbarium', array($this, 'ihb_shortcode'));
    }
    
    function activate() {
        global $wp_rewrite;
        $this->flush_rewrite_rules();
    }
    
    public function ihb_shortcode()
    {
        return 'toto';
    }
    
    function flush_rewrite_rules() {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }
    
    // Took out the $wp_rewrite->rules replacement so the rewrite rules filter could handle this.
    function create_rewrite_rules($rules) {
        global $wp_rewrite;
        $newRule = array('test/(.+)' => 'index.php?test='.$wp_rewrite->preg_index(1),
            'observation/data/(.+)' => 'index.php?idobs='.$wp_rewrite->preg_index(1),
            'observation/photo(.+)' => 'index.php?idphoto='.$wp_rewrite->preg_index(1),
            'choix-dune-etiquette/herbier-support/(.+)' => 'index.php?herbier=true',
        );
        $newRules = $newRule + $rules;
        return $newRules;
    }
    
    function add_query_vars($qvars) {
        $qvars[] = 'test';
        $qvars[] = 'idobs';
        $qvars[] = 'idphoto';
        $qvars[] = 'herbier';
        return $qvars;
    }
    function template_redirect_intercept() {
        global $wp_query;
        global $wpdb;
        
        print_r($_GET);

        if ($wp_query->get('test')) {
            include ('tpl/header.php');
            $this->pushoutput($wp_query->get('test'));
            include ('tpl/footer.php');
            exit;
        }
        if ($wp_query->get('idobs')) {
            
            $amyid = explode('-',$wp_query->get('idobs'));
            $idObs = (int)$amyid[sizeof($amyid)-1];
            
            echo $this->getObsHtml($idObs);   
    
            exit;
        }
        if ($wp_query->get('idphoto')) {
            echo 'large';
        }
        if (strpos($_SERVER['REQUEST_URI'],'/scripts/large.php') !== false)
        {
            
            $amyid=explode('.',$_GET['name']);
            $amyid=explode('_',$amyid[0]);
            if (strpos($wp_query->get('name'),'photo') !== false)
            {
                $idPhoto = (int)$amyid[1];
                $idObs = (int)$amyid[3];
            }
            else 
            {          
                $idPhoto = (int)$amyid[0];
                $idObs = (int)$amyid[1];
            }
            
            echo $this->getPhotoHtml($idPhoto,$idObs);    
            
            exit;
        }
        if ($wp_query->get('herbier')) {
            echo 'herbier';
        }
    }
    
    function pushoutput($message) {
        $this->output($message);
    }
    
    function output( $output ) {
        //header( 'Cache-Control: no-cache, must-revalidate' );
        //header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
        
        // Commented to display in browser.
        // header( 'Content-type: application/json' );
        
        echo json_encode( $output );
    }
    
    function getHeaderHtml()
    {
        ob_start(); 
        include ('tpl/header.php'); 
        $output = ob_get_contents(); 
        ob_end_clean(); 
        return $output; 
    }
    
    function getFooterHtml()
    {
        ob_start();
        include ('tpl/footer.php');
        $output = ob_get_contents();
        ob_end_clean();
        return $output; 
    }
    
    function getObsHtml($idObs)
    {
        global $wpdb;
        
        $sql = "SELECT * FROM iherba_observations WHERE idobs = ".$idObs;
        $results = $wpdb->get_results( $sql , ARRAY_A );
        
        if (sizeof($results)!=1)
        {
            echo "Erreur dans la récupération de l'observation";
            die();
        }

        $content = $this->getHeaderHtml();
        $content .= $this->getDeterminationHTML($idObs);
        $content .= 'Commentaires : '.$results[0]['commentaires'].'<br><br>';
        $content .= 'Adresse de récolte : '.$results[0]['address'].'<br><br>';
        $content .= '<br>';
        $content .= 'Cette observation a été déposée le : '.$results[0]['date_depot'].'<br>';
        $content .= 'Voici les images constituant cette observation : <br>';
		
		$sql = "SELECT * FROM iherba_photos WHERE id_obs = ".$idObs;
        $results_photo = $wpdb->get_results( $sql , ARRAY_A );
        foreach ($results_photo as $row)
        /*TODO: changer url..., canonical url*/
        {
            $content .= '
              <a href="'.get_bloginfo('wpurl').'/scripts/large.php?name='.$row['nom_photo_final'].'">
              	<img src="'.$this->domaine_photo.'/medias/vignettes/'.$row['nom_photo_final'].'">
              </a>';
        }	  

		$content .= '<br><br>Cette observation a été localisée à la latitude '.round($results[0]['latitude'], 4).' 
                        et la longitude '.round($results[0]['longitude'],4).'<br><br>';
        
		$content .= '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
        <script type="text/javascript">
        var longitude ='.$results[0]['longitude'].';
        var latitude='.$results[0]['latitude'].';
        var geocoder;
        var map;
        
        function initialize() {
        	geocoder = new google.maps.Geocoder();
        	var myLatlng = new google.maps.LatLng(latitude,longitude);
        	var myOptions = {
        	    zoom: 15,
        	    center: myLatlng,
        	    mapTypeId: google.maps.MapTypeId.HYBRID
        	}
        	map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
        	
        	var marker = new google.maps.Marker({
        	    position: myLatlng, 
        	    map: map,
        	    
        	});
        }
        </script>
        <script type="text/javascript">
        window.onload = function() {
           initialize();
        }
        </script>
        
        <div id="map_canvas" style="width:500px; height:400px"></div>
        <br/>
        <br/>';
		
		$content.= " UUID de l'observation: ".$results[0]["uuid_observation"]."<br><br>";
        
		
		if($results[0]["latitude"]!=0 && $results[0]["longitude"]!=0){
		    $content.= "<br><strong>Si vous avez récolté cette plante ou une partie de celle-ci, vous avez la possibilité ci-dessous d'imprimer facilement des étiquettes pour votre herbier</strong><br>";
		}
		
		$content .= "Obtenir une page à imprimer avec une étiquette";
		$content .= '<a href="'.get_bloginfo('wpurl').'/choix-dune-etiquette/herbier-support/?numero_observation='.$idObs.'&amp;check=456789&amp;template=compact">Compact</a>';
        /*


      
         
         
    
   
	$content.=get_string_language_sql("ws_go_page_with_qrcode",$mylanguage);
	$paramlien = array(numero_observation  => $numero_observation,check=>456789,template=>'compact');
	$content.= "&nbsp;&nbsp;&nbsp;&nbsp;".$this->pi_linkToPage(get_string_language_sql("ws_go_page_with_qrcode_compact",$mylanguage),47,'',$paramlien);
	$paramlien = array(numero_observation  => $numero_observation,check=>456789,template=>'classic');
	$content.= "&nbsp;&nbsp;&nbsp;&nbsp;".$this->pi_linkToPage(get_string_language_sql("ws_go_page_with_qrcode_classic",$mylanguage),47,'',$paramlien);
	$paramlien = array(numero_observation  => $numero_observation,check=>456789,template=>'complete');
	$content.= "&nbsp;&nbsp;&nbsp;&nbsp;".$this->pi_linkToPage(get_string_language_sql("ws_go_page_with_qrcode_complete",$mylanguage),47,'',$paramlien);
	
	$paramlien = array();
	$content.= "<br>".$this->pi_linkToPage(get_string_language_sql("ws_go_page_choose_label",$mylanguage),98,'',$paramlien);
	$content.= "<br>".get_string_language_sql("ws_uuid_specimen",$mylanguage)  ." : ".$lobervation["uuid_specimen"]."<br/><br/>\n";
    
    }
         
         */
		
		$content .= $this->getFooterHtml();
		return $content;
    }
    
    function getPhotoHtml($idPhoto,$idObs)
    {
        global $wpdb;
        global $wp_query;
        
        
        $sql="SELECT * 
                FROM iherba_photos,iherba_observations 
                WHERE idphotos= '$idPhoto' and idobs=".$idObs.";";
        $results = $wpdb->get_results( $sql, ARRAY_A );
        
       
        if (sizeof($results)!=1)
        {
            echo "Erreur dans la récupération de l'image";
            die();
        }

        $texte_licence .= '';
        $texte_licence .= 'This picture is associated to <a href='.get_bloginfo('wpurl').'/observation/data/'.$results[0]['id_obs'].'> this observation</a><br>';
       
        $content = $this->getHeaderHtml();
        $content .= $texte_licence.'
        <br>
        <a href="'.$this->domaine_photo.'/medias/big/'.$_GET['name'].'" border=0>
        		<img src="'.$this->domaine_photo.'/medias/big/'.$_GET['name'].'" >
        	</a>';
        $content .= $this->getFooterHtml();
        return $content;
    }
    
    function getDeterminationHTML($idObs)
    {
        global $wpdb;
        
        //if($texteseul==0)$champscomment = 'web_comment'; else $champscomment = 'email_comment';
        // pour envoi mail à créateur observation?
        $champscomment = 'web_comment';
        //if($texteseul==0){$finchamps ="_forweb"; $finligne = "<br/>";} else {$finchamps ="_formail";$finligne = " \n";}
        $finligne = "<br/>";
        
        $sql = "SELECT iherba_determination.id , tropicosid, tropicosgenusid, tropicosfamilyid, 
                        nom_commun,nom_scientifique,date, famille,genre ,id_cases,tag_for_translation, 
                        iherba_determination_cases.$champscomment ,iherba_certitude_level.value as certitude_level, 
                        iherba_certitude_level.comment AS certitude_comment, iherba_determination.comment,
                        iherba_precision_level.value AS precision_level,
                        iherba_precision_level.$champscomment AS precisioncomment 
                FROM iherba_determination, iherba_determination_cases,iherba_certitude_level, iherba_precision_level 
                WHERE  iherba_determination_cases.language = 'fr' 
                    AND iherba_determination_cases.id_cases = iherba_determination.comment_case 
                    AND iherba_determination.probabilite != 0 
                    AND iherba_determination.precision_level = iherba_precision_level.value 
                    AND iherba_determination.certitude_level = iherba_certitude_level.value 
                    AND iherba_determination.id_obs=$idObs 
                ORDER BY creation_timestamp DESC";
        
        $results = $wpdb->get_results( $sql, ARRAY_A );
        
        $content = "";
        foreach ($results as $row)
        {
            $nom_commun=$row["nom_commun"];
            $nom_scientifique=$row["nom_scientifique"];
            $date=$row["date"];
            
            list( $jour,$mois, $annee,) = explode("-", $date);
            $content.= $jour."-".$mois."-".$annee;
            
            if($nom_commun!=""){
                $content.= " nom commun : " .$nom_commun . " ";
            }
            
            
            if($row["precision_level"]!=0)
            {
                $content.=' <img src="/interface/target_'.$row["precision_level"].'.gif" width=24 title="'.$row["precisioncomment"].'"> ';
            }
            if($row["certitude_level"]!=0)
            {/*get_string_language_sql('aboutcertitude'.$finchamps,$mylanguage)*/
                $content.=' <img src="/interface/certitude_'.$row["certitude_level"].'.gif"  title="'.$row["certitude_comment"].'"> ';
            }
        
            $idDetermin = $row["id"];
            /*//$paramlien = array(numero_observation  => $numero_observation,numero_det  => $numero_id_determination, sens => "minus", etape => 'comment',check=>456789);
            //$lien_minus=$cetobjet->pi_linkToPage(
            $content.='<img alt="je ne suis pas d\'accord avec ce nom" title="je ne suis pas d\'accord avec ce nom" src="/interface/minus16.png">';
            //,87,'',$paramlien);
            //$paramlien = array(numero_observation  => $numero_observation,numero_det  => $numero_id_determination, sens => "plus", etape => 'comment', check=>456789);
            //$lien_plus=$cetobjet->pi_linkToPage(
            $content.='<img src="/interface/plus16.png" alt="je suis d\'accord avec ce nom" title="je suis d\'accord avec ce nom">';
            //,87,'',$paramlien);
                
            //$content.='&nbsp;'.$lien_minus.$lien_plus;*/
            
            $content.= $finligne;
                
            $sql = "SELECT * 
                        FROM iherba_determination_reaction 
                        WHERE id_determination = $idDetermin 
                            AND disabled = 0 ";
            $results2 = $wpdb->get_results( $sql, ARRAY_A );
            if( mysql_num_rows($results2)>0)
            {
                $content.= " ( ";
                $first_iteration = 1;
                foreach ($results2 as $row2) 
                {
                    if($row2['reactioncase']!="")
                    {
                        if($first_iteration==0)$content.= ",";
                        $content.=  "&nbsp;".$display_reaction[$row2['reactioncase']];
                        $onecomment = desamorcer($row2['comment']);
                        if(strlen($onecomment)>35)$onecomment="";
                        if($onecomment!="")
                            $content .= " :" . $onecomment;
                            $content .= "&nbsp;";
                            $first_iteration = 0;
                    }
                }
                $content.= " ) ";
            }
        }
        
        if($row["id_cases"]!=0){
            $content.= $finligne;
            $content.= "Note :";
            
           /*TODO: extraire*/
           // $content.= get_string_language_sql('expertise_legend_case_'.$row["tag_for_translation"].$finchamps,$mylanguage);
        }
        if($row["comment"]!=""){
            $content.= $finligne;
            $content.= "Note :";
            $content.=$row_determination["comment"];
        }
        
        if($texteseul!=2)
        {
            $content.= $finligne;
            $content.= $finligne;
        }
        
        $content.= $finligne;
        $content.= $finligne;
        
        return $content;
        
    }
}

$iHerbarium = new iHerbarium();
register_activation_hook( __file__, array($iHerbarium, 'activate') );

// Using a filter instead of an action to create the rewrite rules.
// Write rules -> Add query vars -> Recalculate rewrite rules
add_filter('rewrite_rules_array', array($iHerbarium, 'create_rewrite_rules'));
add_filter('query_vars',array($iHerbarium, 'add_query_vars'));

// Recalculates rewrite rules during admin init to save resourcees.
// Could probably run it once as long as it isn't going to change or check the
// $wp_rewrite rules to see if it's active.
add_filter('admin_init', array($iHerbarium, 'flush_rewrite_rules'));
add_action( 'template_redirect', array($iHerbarium, 'template_redirect_intercept') );