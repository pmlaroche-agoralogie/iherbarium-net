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

include_once(plugin_dir_path( __FILE__ ).'utils.php');

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
        return $this->getListeObsHtml();
    }
    
    function flush_rewrite_rules() {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }
    
    // Took out the $wp_rewrite->rules replacement so the rewrite rules filter could handle this.
    function create_rewrite_rules($rules) {
        global $wp_rewrite;
        $newRule = array('test/(.+)' => 'index.php?test='.$wp_rewrite->preg_index(1),
            '(.*)observations/(.+)' => 'index.php?listeobs='.$wp_rewrite->preg_index(2),
            'observation/data/(.+)' => 'index.php?idobs='.$wp_rewrite->preg_index(1),
            'observation/photo/large/(.*)' => 'index.php?ihaction=getphoto&size=large&idphoto='.$wp_rewrite->preg_index(1),
            'scripts/large.php(.*)' => 'index.php?ihaction=getphoto&size=large&idphoto=old',
            'choix-dune-etiquette/herbier-support(.*)' => 'index.php?herbier=1',
            'carte/longitude/(.+)/latitude/(.+)/radius/(.+)' => 'index.php?ihaction=getcarte&longitude='.$wp_rewrite->preg_index(1).'&latitude='.$wp_rewrite->preg_index(2).'&radius='.$wp_rewrite->preg_index(3),
        );
        $newRules = $newRule + $rules;
        return $newRules;
    }
    
    function add_query_vars($qvars) {
        $qvars[] = 'test';
        $qvars[] = 'ihaction';
        $qvars[] = 'idobs';
        $qvars[] = 'idphoto';
        $qvars[] = 'name';
        $qvars[] = 'size';
        $qvars[] = 'herbier';
        $qvars[] = 'listeobs';
        $qvars[] = 'numero_observation';
        $qvars[] = 'template';
        $qvars[] = 'longitude';
        $qvars[] = 'latitude';
        $qvars[] = 'radius';
        return $qvars;
    }
    
    function getTitleiHerbarium() {
        global $wp_query;
        $title = '';
        if ($wp_query->get('idobs')) {
            $title = 'iHerbarium - Observation n° '.$wp_query->get('idobs');
        }
        if ($wp_query->get('listeobs') != "" || strpos($wp_query->post->post_content,'[iHerbarium]')) {
            $page_obs = (int)$wp_query->get('listeobs') + 1;
            $title = 'iHerbarium - Observations - Page '.$page_obs;
        }
        
        if ($title == ''){
            //recup original
            /** TODO: besoin de $post?*/
            $title=get_the_title();
        }
        return $title;
    }
    
    function getUUIDbyID($id_typo){
        global $wp_query;
        global $wpdb;
        $sql = "SELECT id_wp FROM iherba__users_typo_wp WHERE id_typo = ".$id_typo;
        $results = $wpdb->get_results( $sql , ARRAY_A );
        $wp_user=get_user_by('ID',$results[0]['id_wp']);
        return $wp_user->data->display_name;
    }
    
    function getDesciHerbarium() {
        global $wp_query;
        $desc = '';
        if ($wp_query->get('idobs')) {
            $desc = 'iHerbarium - Observation n° '.$wp_query->get('idobs');
        }
        if ($wp_query->get('listeobs') != "" || strpos($wp_query->post->post_content,'[iHerbarium]')) {
            $page_obs = (int)$wp_query->get('listeobs') + 1;
            $desc = 'iHerbarium - Observations - Page '.$page_obs;
        }
        
        /*if ($desc == ''){
            //recup original
            // TODO: besoin de $post?
            $desc=get_the_title();
        }*/
        return $desc;
    }
    
    function setCurrentPage($url){
        global $wp_query;

        $postid = url_to_postid($url);
        $post = get_post($postid);
    
        $query_vars = $wp_query->query;
        if ($post->post_type == 'page')
            $query_vars['pagename'] = $post->post_name;
        if ($post->post_type == 'post')
            $query_vars['name'] = $post->post_name;

        $wp_query->query($query_vars);      
    }
    
    
    
    function template_redirect_intercept() {
        global $wp_query;
        global $wpdb;
        
        //var_dump($wp_query->query_vars);
        //var_dump($wp_query);
        

        if ($wp_query->get('test')) {
            include ('tpl/header.php');
            $this->pushoutput($wp_query->get('test'));
            include ('tpl/footer.php');
            exit;
        }

        if ($wp_query->get('listeobs') != "" || (strpos($wp_query->post->post_content,'[iHerbarium]') && sizeof($wp_query->posts) <2) ) {
            if (strpos($_SERVER['REQUEST_URI'],'observations/'))
            {
                $url = explode('observations/',$_SERVER['REQUEST_URI']);
                $this->setCurrentPage($url[0]);
            }
            //print_r($wp_query->query_vars);
            //print_r($_SERVER);
            include ('tpl/header.php');
            echo $this->getListeObsHtml(10,(int)$wp_query->get('listeobs'));
            include ('tpl/footer.php');
            exit;
        }
        if ($wp_query->get('idobs')) {
            
            /*add_filter('wp_title', 'iHerbarium - Observation n° ', 100);*/
            $amyid = explode('-',$wp_query->get('idobs'));
            $idObs = (int)$amyid[sizeof($amyid)-1];
            
            echo $this->getObsHtml($idObs);   
    
            exit;
        }
        if ($wp_query->get('ihaction') == "getphoto") 
        //if (strpos($_SERVER['REQUEST_URI'],'/scripts/large.php') !== false)
        {
            if ($wp_query->get('idphoto') == 'old')
                $getIdPhoto = $wp_query->get('name');
            else 
                $getIdPhoto = $wp_query->get('idphoto');
            
                echo $getIdPhoto;
                echo $wp_query->get('idPhoto');
            
           // $amyid=explode('-',$getIdPhoto);
            $amyid=preg_replace('/[.-][^.-]{1,4}/','',$getIdPhoto);
            $amyid=explode('_',$amyid);
            if (strpos($getIdPhoto,'photo') !== false)
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
        
        //CARTE
        if ($wp_query->get('ihaction') == "getcarte") 
        {
            echo $this->getCarteHTML($wp_query->get('longitude'),$wp_query->get('latitude'),$wp_query->get('radius'));
            exit;

        }
        if ($wp_query->get('herbier')) {
            echo $this->getEtiquetteHTML($wp_query->get('numero_observation'),$wp_query->get('template'));
            exit;
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
    
    function getObsArray($idObs)
    {
        global $wpdb;
        
        $sql = "SELECT * FROM iherba_observations WHERE idobs = ".$idObs;
        $results = $wpdb->get_results( $sql , ARRAY_A );
        return $results;
    }
    
    function getObsHtml($idObs)
    {
        global $wpdb;
           
        $results = $this->getObsArray($idObs);
        
        if (sizeof($results)!=1)
        {
            echo "Erreur dans la récupération de l'observation";
            die();
        }

        $content = $this->getHeaderHtml();
        
        $content .= '<div class="fiche">';
        $content .= '<div class="header"><h1>Observation numéro : '.$idObs.'</h1></div>';
        $content .= '<div class="contenu">';
        $content .= $this->getDeterminationHTML($idObs);
        
        $content .= 'Commentaires : '.utf8_decode($results[0]['commentaires']).'<br><br>';
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
              <a class="min-img" href="'.get_bloginfo('wpurl').'/observation/photo/large/'.$row['nom_photo_final'].'" 
                style="background-image:url(\''.$this->domaine_photo.'/medias/vignettes/'.$row['nom_photo_final'].'\')">
              </a>';
        }	  

        if ($results[0]['latitude']!=0 OR $results[0]['longitude']!=0)
        {
        		$content .= '<br><br>Cette observation a été localisée à la latitude '.round($results[0]['latitude'], 4).' 
                                et la longitude '.round($results[0]['longitude'],4).'<br><br>';
        
     		$content .= '
            <div id="mapDiv" style=" height: 500px"></div>
            <script>
                // position we will use later
                var lat = '.$results[0]['latitude'].';
                var lon = '.$results[0]['longitude'].';
         
                // initialize map
                map = L.map("mapDiv").setView([lat, lon], 13);
         
                // set map tiles source
                L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
                    attribution: "&copy; <a href=\"https://www.openstreetmap.org/\">OpenStreetMap</a> contributors",
                    maxZoom: 18,
                }).addTo(map);
         
                // add marker to the map
                marker = L.marker([lat, lon]).addTo(map);
         
                // add popup to the marker
                //marker.bindPopup("<b>ACME CO.</b><br />This st. 48<br />New York").openPopup();
            </script>
            <br/>
            <br/>';
     		$radius = '0.04';
     		$content .= '<a href="'.get_bloginfo('wpurl').'/carte/longitude/'.round($results[0]['longitude'],4).'/latitude/'.round($results[0]['latitude'], 4).'/radius/'.$radius.'/">Carte</a>';
     		$content .= "<br><br>";
        }
		
		//details photos
		$content.= $this->getDetailsObsHTML($idObs);
		
		$content.= " UUID de l'observation: ".$results[0]["uuid_observation"]."<br><br>";
        
		
		if($results[0]["latitude"]!=0 && $results[0]["longitude"]!=0){
		    $content.= "<br><strong>Si vous avez récolté cette plante ou une partie de celle-ci, vous avez la possibilité ci-dessous d'imprimer facilement des étiquettes pour votre herbier</strong><br>";
		}
		
		$content .= "Obtenir une page à imprimer avec une étiquette";
		$content .= '  <a href="'.get_bloginfo('wpurl').'/choix-dune-etiquette/herbier-support/?numero_observation='.$idObs.'&amp;check=456789&amp;template=compact">Compact</a>';
		$content .= '  <a href="'.get_bloginfo('wpurl').'/choix-dune-etiquette/herbier-support/?numero_observation='.$idObs.'&amp;check=456789&amp;template=classic">Classique</a>';
		$content .= '  <a href="'.get_bloginfo('wpurl').'/choix-dune-etiquette/herbier-support/?numero_observation='.$idObs.'&amp;check=456789&amp;template=complete">Page support</a>';
		$content .= '</div>';
		$content .= '</div>';
		
		$content .= $this->getFooterHtml();
		return $content;
    }
    
    function getListeObsHtml($limit = 10, $offset = 0)
    {
        global $wpdb;
        global $wp;
        
        $sql = "SELECT idobs FROM iherba_observations";
        $results = $wpdb->get_results( $sql , ARRAY_A );
        $total = sizeof($results);
        
        $sql = "SELECT * FROM iherba_observations ORDER BY date_depot DESC, idobs DESC LIMIT ".$offset*$limit.",".$limit;
        $results = $wpdb->get_results( $sql , ARRAY_A );
        
        if (sizeof($results)==0)
        {
            echo "Erreur dans la récupération des observations";
            die();
        }
        $content = "";
        foreach ($results as $row)
        {
            //TODO: fonction get user affichage
            $content .= '<div class="fiche_liste">';
            $content .= '<div class="header"><h2>Déposé le : '.$row['date_depot'].',<br>par l\'utilisateur : '.$this->getUUIDbyID($row['id_user']).'</h2></div>';
            $content .= '<div class="contenu">Cliquez sur une image pour voir le détail.<br>';
            
            
            $sql = "SELECT * FROM iherba_photos WHERE id_obs = ".$row['idobs']." LIMIT 0,3";
            $results_photo = $wpdb->get_results( $sql , ARRAY_A );
            foreach ($results_photo as $row_photo)
            {
                $url = ($row['url_rewriting_fr']!=''?$row['url_rewriting_fr'].'-'.$row['idobs']:$row['idobs']);
                $content .= '
                  <a class="min-img" href="'.get_bloginfo('wpurl').'/observation/data/'.$url.'" 
                     style="background-image:url(\''.$this->domaine_photo.'/medias/vignettes/'.$row_photo['nom_photo_final'].'\')">
                  </a>';
            }
            
            $content .= '</div></div>';
        }
        if ($offset != 0)
            $content .= '<a href="'.get_bloginfo('wpurl').'/observations/'.($offset-1).'/">Précédent</a>';
        if ( ($total%$limit) > $offset+1)
        {
            //$content .= '<a href="'.get_bloginfo('wpurl').'/observations/'.($offset+1).'/">Suivant</a>';
            $content .= "&nbsp;&nbsp;&nbsp;&nbsp;";
            $content .= '<a href="'.home_url( $wp->request ).'/observations/'.($offset+1).'/">Suivant</a>';
        }
            
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
        <a href="'.$this->domaine_photo.'/medias/big/'.$results[0]['nom_photo_final'].'" border=0>
        		<img src="'.$this->domaine_photo.'/medias/big/'.$results[0]['nom_photo_final'].'" >
        	</a>';
        $content .= $this->getFooterHtml();
        return $content;
    }
    
    function getDeterminationArray($idObs)
    {
        global $wpdb;
        
        //if($texteseul==0)$champscomment = 'web_comment'; else $champscomment = 'email_comment';
        // pour envoi mail à créateur observation?
        $champscomment = 'web_comment';
        //if($texteseul==0){$finchamps ="_forweb"; $finligne = "<br/>";} else {$finchamps ="_formail";$finligne = " \n";}
        $finligne = "<br/>";
        
        $sql = "SELECT iherba_determination.id,iherba_determination.id_user , tropicosid, tropicosgenusid, tropicosfamilyid,
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
        return $results;
    }
    
    function getDeterminationHTML($idObs)
    {
        global $wpdb;
        global $nom_scientifique;
        $nom_scientifique = '';
        
        $finligne = "<br>";
        $results = $this->getDeterminationArray($idObs);
        
        $content = "";
        foreach ($results as $row)
        {
            
            list( $jour,$mois, $annee,) = explode("-", $row["date"]);
            $content.= $jour."-".$mois."-".$annee;
            
            if($row["nom_commun"]!=""){
                $content.= " nom commun : " .$row["nom_commun"]. " ";
            }
            if($row["nom_scientifique"]!=""){
                $content.= " nom scientifique : " .$row["nom_scientifique"]. " ";
                $nom_scientifique = "nom scientifique : " .$row["nom_scientifique"]. " ";
            }
            if($row["genre"]!=""){
                $content.= ", " .$row["genre"]. " ";
                $nom_scientifique .= ", " .$row["genre"]. " ";
            }
            if($row["famille"]!=""){
                $content.= ", " .$row["famille"]. " ";
                $nom_scientifique .=  ", " .$row["famille"]. " ";
            }
            $content.= $finligne;
            
            if($row["precision_level"]!=0)
            {
                //$content.=' <img src="/interface/target_'.$row["precision_level"].'.gif" width=24 title="'.$row["precisioncomment"].'"> ';
                $content.=$row["precisioncomment"].$finligne;
            }
            if($row["certitude_level"]!=0)
            {/*get_string_language_sql('aboutcertitude'.$finchamps,$mylanguage)*/
               // $content.=' <img src="/interface/certitude_'.$row["certitude_level"].'.gif"  title="'.$row["certitude_comment"].'"> ';
                $content.=$row["certitude_comment"].$finligne;
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
                
           /* $sql = "SELECT * 
                        FROM iherba_determination_reaction 
                        WHERE id_determination = $idDetermin 
                            AND disabled = 0 ";
            $results2 = $wpdb->get_results( $sql, ARRAY_A );
            if (sizeof($results2)>0)
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
            }*/
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
        
       /* if($texteseul!=2)
        {
            $content.= $finligne;
            $content.= $finligne;
        }*/
        
       /* $content.= $finligne;
        $content.= $finligne;*/
        
        return $content;
        
    }
    
    function getDetailsObsHTML($idObs)
    {
        global $wpdb;
        global $nom_scientifique;
        
        $content = "";
        $langue= "fr";
        
        $sql="SELECT DISTINCT iherba_roi.id,iherba_tags.tag,iherba_roi_answers_pattern.id AS lineid 
                                    FROM iherba_roi_answers_pattern,iherba_roi,iherba_photos,iherba_tags,iherba_roi_tag
                                    WHERE iherba_photos.id_obs=".$idObs." 
                                        AND iherba_photos.idphotos=iherba_roi.id_photo 
                                        AND iherba_roi.id=iherba_roi_answers_pattern.id_roi
                                        AND iherba_tags.id_tag = iherba_roi_tag.id_tag
                                        AND iherba_roi_tag.id_roi = iherba_roi.id 
                                        GROUP BY iherba_roi.id";
        
        $results = $wpdb->get_results( $sql, ARRAY_A );
        
        $liste_roi= array();
        $liste_roi_tag= array();
        if(sizeof($results)>0)
        {
            foreach($results as $ligne)
            {
                $liste_roi[] = $ligne['id'];
                $liste_roi_tag[] = $ligne['tag'];
            }
        }
        
        foreach($liste_roi as $key => $value)
        {
            $content .= '<img src="'.$this->domaine_photo.'/medias/roi_vignettes/roi_'.$value.'.jpg" alt="'.$liste_roi_tag[$key] .'  : '.$nom_scientifique.'" >';
            $sql="SELECT iherba_roi_answers_pattern.id_roi,
	                                        iherba_roi_answers_pattern.id_question,
                                            iherba_roi_answers_pattern.id_answer_most_common,
                                            iherba_roi_answers_pattern.prob_most_common,	
                                            iherba_roi_answers_pattern.id_just_less_common,	
                                            iherba_roi_answers_pattern.prob_just_less,
                                            iherba_question.choice_explicitation_one , 
                                            iherba_question.choice_explicitation_two_seldom , 
                                            iherba_question.choice_explicitation_two_often , 
                                            iherba_question.choice_detail, 
                                            iherba_roi_answers_pattern.id AS lineid 
                                    FROM iherba_roi_answers_pattern,iherba_roi,iherba_photos,iherba_question
                                    WHERE iherba_roi.id = ".$value." 
                                        AND iherba_photos.id_obs=".$idObs." 
                                        AND iherba_photos.idphotos=iherba_roi.id_photo 
                                        AND iherba_roi.id=iherba_roi_answers_pattern.id_roi 
                                        AND iherba_question.id_langue='".$langue."'
                                        AND iherba_roi_answers_pattern.id_question = iherba_question.id_question  ";
            //echo $sql;
                //$content .= "<!-- $requete_lignes_pattern -->";
                $results = $wpdb->get_results( $sql, ARRAY_A );
                if(sizeof($results)>0)
                {
                    $content .= "<br>Cette region d'image a été qualifiée comme suit : <br>";
                    foreach ($results as $ligne)
                    {
                        if($ligne['choice_detail']=="")return "<!-- warning no text for question ".$ligne['id_question']." -->";
                        {
                            $reponsespossibles = explode("!",$ligne['choice_detail']);
                        }
                        
                        if($ligne[prob_most_common]>90)
                        {
                            $textexplication = $ligne['choice_explicitation_one'];
                        }
                        else
                        {
                            if($ligne[prob_most_common]>80)
                                $textexplication = $ligne['choice_explicitation_two_seldom'];
                                else
                                    $textexplication = $ligne['choice_explicitation_two_often'];
                        }
                            
                        $textexplication = str_replace("#1",$reponsespossibles[$ligne['id_answer_most_common']],$textexplication);
                        $textexplication = str_replace("#2",$reponsespossibles[$ligne['id_just_less_common']],$textexplication);
                        
                        $content .= $textexplication."<br/>";
                        //$content .= build_response($ligne,$cibleaction,$show_delete_button)."<br>";
                    }
                }
        }
        
        return $content."<br/>";
    }
    
    function getEtiquetteHTML($idObs,$size)
    {
        global $wpdb;
        
        $results = $this->getObsArray($idObs);
        $row = $results[0];//print_r($row);
        
        $results2 = $this->getDeterminationArray($idObs);
        $row2 = $results2[0];//print_r($row2);
        
        $nom_commun=$row2["nom_commun"];
        $nom_scientifique=$row2["nom_scientifique"];
        
        $nameObs= $nom_scientifique ;
        if($nom_commun !='')$nameObs .= "(".$nom_commun. ") ";
        if ($nameObs == '')
            $nameObs = '--';
        
        $authorRecolt = $this->getUUIDbyID($row['id_user']);
        $authorDeterminObs = $this->getUUIDbyID($row2['id_user']);
        if ( $row2['date'] != '')
            $authorDeterminObs .= " (".$row2['date'].") ";;
        
        $urlqrencode = get_bloginfo('wpurl')."/observation/data/".$lobervation['uuid_specimen'];
        $urlgoogle =  'http://chart.apis.google.com/chart?chs=420x420&cht=qr&chld=H&chl='.urlencode($urlqrencode);
        $position=convertSexa2coord($row["latitude"],$row["longitude"]);
        
        if ($size == 'complete')
        {
            $imgs = '';
            $sql = "SELECT * FROM iherba_photos WHERE id_obs = ".$idObs;
            $results_photo = $wpdb->get_results( $sql , ARRAY_A );
            foreach ($results_photo as $row_photo)
            {
                $imgs .= '<br>
                  <div class="min-img" style="background-image:url(\''.$this->domaine_photo.'/medias/vignettes/'.$row_photo['nom_photo_final'].'\')">
                  </div>';
            }	  
        }
        
        $output = "";
        switch ($size)
        {
            case 'compact':
                ob_start();
                include ('tpl/etiquette-compact.php');
                $output = ob_get_contents();
                ob_end_clean();
                break;
            case 'classic':
                ob_start();
                include ('tpl/etiquette-classic.php');
                $output = ob_get_contents();
                ob_end_clean();
                break;
            default :
                //complete
                ob_start();
                include ('tpl/etiquette-complete.php');
                $output = ob_get_contents();
                ob_end_clean();
                ;
        }
        return $output; 
    }
    
    function getCarteHTML($longitude,$latitude,$radius)
    {
        global $wpdb;
        $content = "";
        
        $content = $this->getHeaderHtml();
        
        $content .= "<h1>Carte latitude ".$latitude." / longitude ".$longitude." / rayon ".$radius."</h1>";
        
        $sql = "SELECT iherba_observations.idobs,
                    iherba_observations.longitude,
                    iherba_observations.latitude,
                    iherba_observations.commentaires,
                    iherba_photos.nom_photo_final,
                    iherba_observations.deposit_timestamp, 
                    iherba_observations.url_rewriting_fr, 
                    iherba_observations.url_rewriting_en 
                FROM iherba_photos,iherba_observations 
                WHERE iherba_observations.latitude !=0 
                    AND iherba_observations.idobs=iherba_photos.id_obs 
                    AND iherba_observations.public='oui'
                    AND iherba_observations.latitude >".($latitude-$radius). "
                    AND iherba_observations.latitude < ".($latitude+$radius). "
                    AND iherba_observations.longitude > ".($longitude-$radius). "
                    AND iherba_observations.longitude < ".($longitude+$radius). "
                GROUP BY iherba_observations.idobs 
                ORDER BY iherba_observations.idobs DESC 
                LIMIT 0,250;";
        
        $results = $wpdb->get_results( $sql, ARRAY_A );
        if(sizeof($results)>0)
        {
            $content .= '
            <div id="mapDiv" style=" height: 900px"></div>
            <script>
                // position we will use later
                var lat = '.$results[0]['latitude'].';
                var lon = '.$results[0]['longitude'].';
                    
                // initialize map
                map = L.map("mapDiv").setView(['.$latitude.', '.$longitude.'], 13);
                    
                // set map tiles source
                L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
                    attribution: "&copy; <a href=\"https://www.openstreetmap.org/\">OpenStreetMap</a> contributors",
                    maxZoom: 18,
                }).addTo(map);
                    
                // add markers to the map';
                
            foreach ($results as $donnees)
            {
                
                $nc = '';
                $ns = '';
                
                $sql = "SELECT nom_commun,nom_scientifique FROM iherba_determination WHERE id_obs = ".$donnees['idobs']." ORDER BY date ASC";
                $results_nom=$wpdb->get_results( $sql, ARRAY_A );
                if(sizeof($results_nom)>0)
                {
                    foreach ($results_nom as $donnees_nom)
                    {
                        $nc = $donnees_nom['nom_commun'];
                        $ns = $donnees_nom['nom_scientifique'];
                    }
                }

                $description = '<img src=\"'.$this->domaine_photo.'/medias/vignettes/'.$donnees['nom_photo_final'].'\" style=\"width:100px;border-radius:5px;\" /><br />';
                $description .= '<p><a target=\"_blank\" href=\"'.get_bloginfo('wpurl').'/observation/data/'.$donnees['idobs'].'\">Observation numéro :  '.$donnees['idobs'].'</a><br />';
                if ($nc != ''){
                    $description .= '<strong>'.$nc.'</strong><br />';
                }
                if ($ns != ''){
                    $description .= '<strong>'.$ns.'</strong><br />';
                }
                $description .= 'Transmise le : '.$donnees['deposit_timestamp'] .'<br />';
                $description .= 'Note : '.str_replace("\n"," ",str_replace('"'," ",str_replace("\r"," ",str_replace("'"," ",utf8_decode($donnees['commentaires']))))).'</p>';
                
                
                
                $content .= '
                    marker = L.marker(['.$donnees['latitude'].','.$donnees['longitude'].']).addTo(map).bindPopup("'.$description.'");';
            }
            $content .= '
            </script>
            <br/>
            <br/>';
        }
        $content .= $this->getFooterHtml();
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
add_filter('wp_title', array($iHerbarium, 'getTitleiHerbarium'));
add_filter( 'wpseo_metadesc', array($iHerbarium, 'getDesciHerbarium'));
add_action( 'template_redirect', array($iHerbarium, 'template_redirect_intercept') );