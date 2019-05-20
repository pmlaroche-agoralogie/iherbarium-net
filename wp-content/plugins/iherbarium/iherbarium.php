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
    public $domaine_photo = "http://medias.iherbarium.fr";
    public $photo_dir = "medias/sources/";
    public $photo_dir_min = "medias/vignettes/";
    public $photo_dir_big = "medias/big/";
    
    public function __construct()
    {
        add_shortcode('iHerbarium', array($this, 'ihb_shortcode'));
        add_shortcode('iHerbariumCarte', array($this, 'ihb_carte_shortcode'));
        add_shortcode('iHerbariumListe', array($this, 'ihb_liste_shortcode'));
    }
    
    function activate() {
        global $wp_rewrite;
        $this->flush_rewrite_rules();
    }
    
    public function init_scripts_fileupload(){
        
        wp_register_style( 'bootstrap-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
        wp_register_style( 'jquery-fileupload-css', plugin_dir_url( __FILE__). 'css/jquery.fileupload.css');
        wp_register_style( 'iherbarium-css', plugin_dir_url( __FILE__). 'css/iherbarium.css');
        wp_enqueue_style( 'bootstrap-css' );
        wp_enqueue_style( 'jquery-fileupload-css' );
        wp_enqueue_style( 'iherbarium-css' );
        
        
        //wp_register_script( 'bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js', array('jquery'), '3.3.4', true );
        wp_register_script('jquery-iframe-tansport', plugin_dir_url( __FILE__ ) . 'js/jquery.iframe-transport.js',array(),false,true);
       
        wp_register_script('load-image-all', plugin_dir_url( __FILE__ ) . 'js/load-image.all.min.js',array(),false,true);
        
        wp_register_script('jquery-fileupload', plugin_dir_url( __FILE__ ) . 'js/jquery.fileupload.js',array(),false,true);
        wp_register_script('jquery-fileupload-process', plugin_dir_url( __FILE__ ) . 'js/jquery.fileupload-process.js',array(),false,true);
        wp_register_script('jquery-fileupload-image', plugin_dir_url( __FILE__ ) . 'js/jquery.fileupload-image.js',array(),false,true);
		wp_register_script('jquery-fileupload-video', plugin_dir_url( __FILE__ ) . 'js/jquery.fileupload-video.js',array(),false,true);
        wp_register_script('jquery-fileupload-validate', plugin_dir_url( __FILE__ ) . 'js/jquery.fileupload-validate.js',array(),false,true);
        
       // wp_register_script('jquery-fileupload-ui', plugin_dir_url( __FILE__ ) . 'js/jquery.fileupload-ui.js',array(),false,true);
       // wp_register_script('jquery-fileupload-jquery-ui', plugin_dir_url( __FILE__ ) . 'js/jquery.fileupload-jquery-ui.js',array(),false,true);
        wp_register_script('js-iherbarium', plugin_dir_url( __FILE__ ) . 'js/iherbarium.js',array(),false,true);
       
       
        wp_enqueue_script('jquery-ui-widget');
        
        //wp_enqueue_script( 'bootstrap-js');
       // wp_enqueue_script( 'jquery-iframe-tansport' );
        wp_enqueue_script('load-image-all');
        
        wp_enqueue_script('jquery-fileupload' );
        wp_enqueue_script('jquery-fileupload-process');
        wp_enqueue_script('jquery-fileupload-image');
		 wp_enqueue_script('jquery-fileupload-video');
        wp_enqueue_script('jquery-fileupload-validate');
       // wp_enqueue_script( 'jquery-fileupload-ui' );
    //    wp_enqueue_script( 'jquery-fileupload-jquery-ui' );
        
        wp_enqueue_script( 'js-iherbarium' );
        
    }
    
    public function ihb_shortcode()
    {
        return $this->getListeObsHTML();
    }
    
    public function ihb_carte_shortcode($atts)
    {
        $content = "";
        if (isset($atts['longitude']) && isset($atts['latitude']) )
        {
            $longitude = $atts['longitude'];
            $latitude = $atts['latitude'];
            if (isset($atts['radius']))
                $radius = $atts['radius'];
            else 
                $radius = 0.04;
            if (isset($atts['limit']))
                $limit = $atts['limit'];
            else
                $limit = 0;
           $content .= $this->getCarteHTML($longitude,$latitude,$radius,$limit);
        }
         
        return $content;
    }
    
    public function ihb_liste_shortcode($atts)
    {
        $content = "";
        if (isset($atts['longitude']) && isset($atts['latitude']) )
        {
            $longitude = $atts['longitude'];
            $latitude = $atts['latitude'];
            if (isset($atts['radius']))
                $radius = $atts['radius'];
            else
                $radius = 0.04;
            if (isset($atts['limit']))
                $limit = $atts['limit'];
            else
                $limit = 0;
                $content .= $this->getListeObsByZoneHTML($longitude,$latitude,$radius,$limit);
        }
        
        return $content;
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
            'utilisateur/(.+)/(.+)' => 'index.php?iduser='.$wp_rewrite->preg_index(1).'&offset='.$wp_rewrite->preg_index(2),
            'utilisateur/(.+)' => 'index.php?iduser='.$wp_rewrite->preg_index(1),
            'observation/new' => 'index.php?ihaction=newobs',
            'observation/thankyou' => 'index.php?ihaction=thankyouobs',
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
        $qvars[] = 'iduser';
        $qvars[] = 'offset';
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
        if ($wp_query->get('ihaction') == "getcarte")
        {
            $title = 'iHerbarium - Carte - latitude '.$wp_query->get('latitude').' / longitude '.$wp_query->get('longitude').' / rayon '.$wp_query->get('radius');
        }
        
        if ($wp_query->get('herbier')) {
            $title = 'iHerbarium - Etiquette Herbier - '.$wp_query->get('template').' - Observation  n° '.$wp_query->get('numero_observation');
        }
        
        if ($wp_query->get('ihaction') == "getphoto")
        {
            $this->getIdsFromPhoto($idPhoto,$idObs);
            $title = 'iHerbarium - Photo n° '.$idPhoto.' - Observation  n° '.$idObs;
        }
        
        if ($wp_query->get('iduser'))
        {
            $title = 'iHerbarium - Observations de l\'utilisateur '.$wp_query->get('iduser');
        }
        
        if ($wp_query->get('ihaction') == "newobs")
        {
            $title = 'iHerbarium - Soumettre une observation';
        }
        
        if ($wp_query->get('ihaction') == "thankyouobs")
        {
            $title = 'iHerbarium - Merci';
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
        return $wp_user->data->user_nicename;
    }
    
    function getDisplayNamebyID($id_typo){
        global $wp_query;
        global $wpdb;
        $sql = "SELECT id_wp FROM iherba__users_typo_wp WHERE id_typo = ".$id_typo;
        $results = $wpdb->get_results( $sql , ARRAY_A );
        $wp_user=get_user_by('ID',$results[0]['id_wp']);
        return $wp_user->data->display_name;
    }
    
    function getIDbyUUID($uuid){
        global $wp_query;
        global $wpdb;
        $sql = "SELECT id.id_typo
                        FROM iherba__users_typo_wp AS id
                        LEFT JOIN wp_users AS wp ON id.id_wp = wp.ID
                        WHERE wp.user_login = '".$uuid."'";
        $results = $wpdb->get_results( $sql , ARRAY_A );
        return $results[0]['id_typo'];
    }
    
    function setIDbyUUID($uuid){
        global $wpdb;
        $user = get_user_by('login',$uuid);
        $sql = "SELECT MAX(id_typo) AS 'max_id_typo' FROM iherba__users_typo_wp";
        $results = $wpdb->get_results( $sql , ARRAY_A );
        $sql = "INSERT INTO iherba__users_typo_wp (id_typo,id_wp) VALUES (".($results[0]['max_id_typo']+1).",".$user->ID.")";
        $results = $wpdb->query( $sql);
        if ($results)
            return ($results[0]['max_id_typo']+1);
        else 
            return false;
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
        if ($wp_query->get('ihaction') == "getcarte") 
        {
            $desc = 'iHerbarium - Carte - latitude '.$wp_query->get('latitude').' / longitude '.$wp_query->get('longitude').' / rayon '.$wp_query->get('radius');
        }
        
        if ($wp_query->get('herbier')) {
            $desc = 'iHerbarium - Etiquette Herbier - '.$wp_query->get('template').' - Observation  n° '.$wp_query->get('numero_observation');
        }
        if ($wp_query->get('ihaction') == "getphoto")
        {
            $this->getIdsFromPhoto($idPhoto,$idObs);
            $desc = 'iHerbarium - Photo n° '.$idPhoto.' - Observation  n° '.$idObs;
        }
        
        if ($wp_query->get('iduser'))
        {
            $desc = 'iHerbarium - Observations de l\'utilisateur '.$wp_query->get('iduser');
        }
        
        if ($wp_query->get('ihaction') == "newobs")
        {
            $desc = 'iHerbarium - Soumettre une observation';
        }
        
        if ($wp_query->get('ihaction') == "thankyouobs")
        {
            $desc = 'iHerbarium - Merci';
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

        if ($wp_query->get('test')) {
            include ('tpl/header.php');
            $this->pushoutput($wp_query->get('test'));
            include ('tpl/footer.php');
            exit;
        }

        if ($wp_query->get('listeobs') != "" ) {
            if (strpos($_SERVER['REQUEST_URI'],'observations/'))
            {
                $url = explode('observations/',$_SERVER['REQUEST_URI']);
                $this->setCurrentPage($url[0]);
            }

            include ('tpl/header.php');
            echo $this->getListeObsHTML(10,(int)$wp_query->get('listeobs'));
            include ('tpl/footer.php');
            exit;
        }
        if ($wp_query->get('idobs')) {
            $amyid = explode('-',$wp_query->get('idobs'));
            $idObs = (int)$amyid[sizeof($amyid)-1];
			
			if ($_REQUEST['nom_commun'] != '' || $_REQUEST['nom_scientifique'] != ''){
				$ok = $this->setNamesObservation($idObs,$_REQUEST['nom_commun'],$_REQUEST['nom_scientifique']);
			}
			            
            echo $this->getObsHTML($idObs);   
    
            exit;
        }
        if ($wp_query->get('ihaction') == "getphoto") 
        {
            $this->getIdsFromPhoto($idPhoto,$idObs);
            
            echo $this->getPhotoHTML($idPhoto,$idObs);    
            
            exit;
        }
        
        //CARTE
        if ($wp_query->get('ihaction') == "getcarte") 
        {
            echo $this->getPageCarteHTML($wp_query->get('longitude'),$wp_query->get('latitude'),$wp_query->get('radius'));
            exit;

        }
        if ($wp_query->get('herbier')) {
            echo $this->getEtiquetteHTML($wp_query->get('numero_observation'),$wp_query->get('template'));
            exit;
        }
        
        //USER
        if ($wp_query->get('iduser'))
        {
            if (strpos($_SERVER['REQUEST_URI'],'utilisateur/'))
            {
                $url = explode('utilisateur/',$_SERVER['REQUEST_URI']);
                $this->setCurrentPage($url[0]);
            }
            //print_r($wp_query->query_vars);
            //print_r($_SERVER);
            include ('tpl/header.php');
            echo $this->getListeObsHTML(10,(int)$wp_query->get('offset'),$wp_query->get('iduser'));
            include ('tpl/footer.php');
            exit;
        }
        
        //Submit newobs
        if ($wp_query->get('ihaction') == "newobs")
        {
            $uuid_obs = $this->getUUID();
            if (is_user_logged_in())
            {
            $user=wp_get_current_user();
            $user_id = $user->ID;
            //print_r($user);
            add_action('wp_enqueue_scripts',array($this, 'init_scripts_fileupload'));
          /*  echo '<pre>';
            print_r($_REQUEST);
            print_r($_FILES);
            echo '</pre>';*/
            include ('tpl/header.php');
            include ('tpl/submit-obs-form.php');
            include ('tpl/footer.php');
            }
            else 
            {
                include ('tpl/header.php');
                echo 'Vous devez être connecté pour soumetre une observation.';
                include ('tpl/footer.php');
            }
                
            exit;
        }
        
        //thank you newobs
        if ($wp_query->get('ihaction') == "thankyouobs")
        {
            
            global $wpdb;
            $sql = $wpdb->prepare("SELECT * FROM iherba_observations WHERE uuid_observation = %s",$_REQUEST['uuid_obs']);
            $results = $wpdb->get_results( $sql , ARRAY_A );
            

            $url = $results[0][idobs];
            
           include ('tpl/header.php');
           echo 'Merci pour votre soumission.<br> Vous pouvez la consulter  <a class="min-img" href="'.get_bloginfo('wpurl').'/observation/data/'.$url.'" ">ici</a>';
           include ('tpl/footer.php');

            
            exit;
        }
        
        //submit file newobs
        if ($wp_query->get('ihaction') == "submitobs")
        {
            $phpFileUploadErrors = array(
                0 => 'There is no error, the file uploaded with success',
                1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                3 => 'The uploaded file was only partially uploaded',
                4 => 'No file was uploaded',
                6 => 'Missing a temporary folder',
                7 => 'Failed to write file to disk.',
                8 => 'A PHP extension stopped the file upload.',
            );
            $aStatus = array('status' => 'success');

            foreach ($_FILES["files"]["error"] as $key => $error) 
            {
                if ($_FILES['error'][$key])
                {
                    $aStatus['status'] = 'error';
                    $aStatus['file'] = array('error' => $phpFileUploadErrors[$file['error'][0]]);
                }
                else 
                {



                    //enregister
                    if ($_REQUEST['uuid_obs'])
                    {
                        $user = get_user_by('id', $_REQUEST['id_user']);
                        $typo_id = $this->getIDbyUUID($user->data->user_login);
                        if (!$typo_id)
                        {
                            echo 'create';
                            $typo_id = $this->setIDbyUUID($user->data->user_login);
                            
                        }
                        
                        if (!$typo_id)
                        {
                            $aStatus['status'] = 'error';
                            $aStatus['file'] = array('error' => 'no typo_id');
                        }
                        else 
                        {
							$is_image = true;						
							if (strpos($_FILES["files"]["name"][$key],".mp4") !== false){ 
								$is_image = false;
							}


                            //save info
                            global $wpdb;
                            $sql = $wpdb->prepare("SELECT * FROM iherba_observations WHERE uuid_observation = %s",$_REQUEST['uuid_obs']);
                            $results = $wpdb->get_results( $sql , ARRAY_A );
                            if (!$results)
                            {

                                $longitude = 0;
                                $latitude = 0;
								$address = '';
								$dateprisevue = '';

								if ($is_image){
									if($exif=exif_read_data($_FILES["files"]["tmp_name"][$key], 'GPS', true))
									{ 
										$longitude = calcul_longitude_exif($exif);
										$latitude = calcul_latitude_exif($exif);
										$address = get_adress_from_loc($latitude,$longitude);
									}
									if($exif=exif_read_data($_FILES["files"]["tmp_name"][$key], 'EXIF', true))
									{
										$dateprisevue = date_prise_de_vue_exif($exif);
									} 
								}							                              							
                                $uuid_specimen = $this->getUUID();
                                $sql = $wpdb->prepare("INSERT INTO iherba_observations (id_user,uuid_observation,uuid_specimen ,commentaires,latitude,longitude,date_depot,original_timestamp,address) 
                                                    VALUES (%d,%s,%s,%s,%f,%f,%s,%s,%s)",$typo_id,$_REQUEST['uuid_obs'],$uuid_specimen,$_REQUEST['commentaires'],$latitude,$longitude,date('Y-m-d'),$dateprisevue,$address);
                                $wpdb->query($sql);
                                $sql = $wpdb->prepare("SELECT * FROM iherba_observations WHERE uuid_observation = %s",$_REQUEST['uuid_obs']);
                                $results = $wpdb->get_results( $sql , ARRAY_A );
                            }
                            
                            //save info photo
                            $longitude = 0;
                            $latitude = 0;
							if ($is_image){
								if($exif=exif_read_data($_FILES["files"]["tmp_name"][$key], 'GPS', true))
								{
									$longitude = calcul_longitude_exif($exif);
									$latitude = calcul_latitude_exif($exif);
								}
							}
                            $sql=$wpdb->prepare("INSERT INTO iherba_photos (id_obs,latitude_exif,longitude_exif,nom_photo_initial,all_exif_fields,date_depot,DateTimeOriginal)
                                        VALUES (%d,%f,%f,%s,%s,%s,%s)",$results[0][idobs],$latitude,$longitude,$_FILES['files']['name'][$key],json_encode($exif),date('Y-m-d'),date_prise_de_vue_exif($exif));
                            
                            $wpdb->query($sql);
                            $lastid = $wpdb->insert_id;
                            $photo_name = "photo_".$lastid."_observation_".$results[0][idobs].".".pathinfo($_FILES['files']['name'][$key], PATHINFO_EXTENSION);
                            
                            //move photo
                            $tmp_name = $_FILES["files"]["tmp_name"][$key];
                            // basename() peut empêcher les attaques de système de fichiers;
                            // la validation/assainissement supplémentaire du nom de fichier peut être approprié
                            $name = basename($_FILES["files"]["name"][$key]);

                            $upload_ok = move_uploaded_file($tmp_name, ABSPATH.$this->photo_dir.$photo_name);
                           
						    if ($is_image){
                            	redimensionner_image(ABSPATH.$this->photo_dir.$photo_name,200,ABSPATH.$this->photo_dir_min.$photo_name);
                            	redimensionner_image(ABSPATH.$this->photo_dir.$photo_name,1024,ABSPATH.$this->photo_dir_big.$photo_name);
                            }
							
                            $sql = "UPDATE iherba_photos SET nom_photo_final = '".$photo_name."' WHERE idphotos = ".$lastid;
                            $wpdb->query($sql);
                        }
                       
                        
                    }
                    else 
                    {
                        $aStatus['status'] = 'error';
                        $aStatus['file'] = array('error' => 'no uuid');
                    }
                }
            }
            
            echo json_encode($aStatus);
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
    
    function getIdsFromPhoto(&$idPhoto,&$idObs)
    {
        global $wp_query;
        if ($wp_query->get('idphoto') == 'old')
            $getIdPhoto = $wp_query->get('name');
            else
                $getIdPhoto = $wp_query->get('idphoto');
                
                
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
        
    }
    
    function getHeaderHTML()
    {
        ob_start(); 
        include ('tpl/header.php'); 
        $output = ob_get_contents(); 
        ob_end_clean(); 
        return $output; 
    }
    
    function getFooterHTML()
    {
        ob_start();
        include ('tpl/footer.php');
        $output = ob_get_contents();
        ob_end_clean();
        return $output; 
    }
    
    function getUUID()
    {
        global $wpdb;
        $sql = "SELECT UUID() AS uuid" ;
        $results = $wpdb->get_results( $sql , ARRAY_A );
        return $results[0]['uuid'];
    }
    
    function getObsArray($idObs)
    {
        global $wpdb;
        
        $sql = "SELECT * FROM iherba_observations WHERE idobs = ".$idObs;
        $results = $wpdb->get_results( $sql , ARRAY_A );
        return $results;
    }
    
    function getObsHTML($idObs)
    {
        global $wpdb;
           
        $results = $this->getObsArray($idObs);
        
        if (sizeof($results)!=1)
        {
            echo "Erreur dans la récupération de l'observation";
            die();
        }

        $content = $this->getHeaderHTML();
        
        $content .= '<div class="fiche">';
        $content .= '<div class="header"><h1>Observation numéro : '.$idObs.'</h1></div>';
        $content .= '<div class="contenu">';
        $content .= $this->getDeterminationHTML($idObs);

			$user = wp_get_current_user();
			if ($user->ID != 0 && $user->ID != ''){
				$content .= '<p class="btn_open" onClick="document.getElementById(\'saisie_noms\').style.display=\'block\';">Donnez un nom commun ou scientifique</p>';
				$content .= '<div id="saisie_noms">';	
					$content .= '<form id="form_saisie_noms" name="form_saisie_noms" method="post" action="">';
					$content .= '<span class="btn_close" onClick="document.getElementById(\'saisie_noms\').style.display=\'none\';"></span>';
					$content .= '<script type="text/javascript" src="/wp-content/plugins/iherbarium/completion/completion_taxonomy.js"></script>';
					$content .= '<p><input type="checkbox" name="limite_france" id="limite_france" checked="checked"/> Limité à la France</p>';
					$content .= '<p>Donnez un nom commun : <input name="nom_commun" type="text" id="nom_commun" size="40" value=""></p>';
					$content .= '<p>Donnez un nom scientifique : <input name="nom_scientifique" type="text" id="nom_scientifique" autocomplete="off" size="60" value="" disable="disable"></p>';					
					$content .= '<p style="text-align:right;"><input type="button" name="button" id="button" value="Valider cette détermination" onClick="submit();" /></p>';
					$content .= '<p><input type="hidden" name="idobs" id="idobs" value="'.$idObs.'" /></p>';
					$content .= '<p><input type="hidden" name="genre_obs" id="genre_obs" value="'.$results[0]['genre_obs'].'" /></p>';
					$content .= '</form>';	
				$content .= '</div>';
			}else{
				$content .= '<p><a href="/login/" class="btn_open">Connectez-vous si vous désirez donnez un nom commun ou scientifique</a></p>';
			}

        $content .= 'Commentaires : '.utf8_decode($results[0]['commentaires']).'<br><br>';
        $content .= 'Adresse de récolte : '.$results[0]['address'].'<br><br>';
        $content .= '<br>';
        $content .= 'Cette observation a été déposée le '.$results[0]['date_depot'].' par l\'utilisateur : 
<a href="'.get_bloginfo('wpurl').'/utilisateur/'.$this->getUUIDbyID($results[0]['id_user']).'/">'.$this->getDisplayNamebyID($results[0]['id_user']).'</a><br>';
       
		$content .= 'Voici les informations constituant cette observation : <br>';
		$sql = "SELECT * FROM iherba_photos WHERE id_obs = ".$idObs;
        $results_photo = $wpdb->get_results( $sql , ARRAY_A );
        foreach ($results_photo as $row)
        /*TODO: changer url..., canonical url*/
        {
			
			if (strpos($row['nom_photo_initial'],".mp4") !== false){ // si c'est une vidéo				
				$content .= '<a class="min-img" href="'.get_bloginfo('wpurl').'/observation/photo/large/'.$row['nom_photo_final'].'"><img style="margin:0;" src="/wp-content/plugins/iherbarium/img/icone_video.png" /></a>';
			}else{
				$content .= '
				  <a class="min-img" href="'.get_bloginfo('wpurl').'/observation/photo/large/'.$row['nom_photo_final'].'" 
					style="background-image:url(\''.$this->domaine_photo.'/medias/vignettes/'.$row['nom_photo_final'].'\')">
				  </a>';
			}
        }	  

        if (($results[0]['latitude']!=0 OR $results[0]['longitude']!=0) AND $results[0]['public']=='oui')
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
		
		$content .= $this->getFooterHTML();
		return $content;
    }
    
    function getListeObsHTML($limit = 10, $offset = 0, $user = '')
    {
        global $wpdb;
        global $wp;
        
        $where = '';
        
        if ($user != '')
        {
            $user_id = $this->getIDbyUUID($user);
            
            $where = ' AND id_user = '.$user_id;
        }
        
        $sql = "SELECT idobs FROM iherba_observations WHERE public='oui'".$where;
        $results = $wpdb->get_results( $sql , ARRAY_A );
        $total = sizeof($results);
        
        $sql = "SELECT * FROM iherba_observations WHERE public='oui'".$where." ORDER BY date_depot DESC, idobs DESC LIMIT ".$offset*$limit.",".$limit;
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
            $content .= '<div class="header"><h2>Déposé le : '.$row['date_depot'].',<br>par l\'utilisateur : <a href="'.get_bloginfo('wpurl').'/utilisateur/'.$this->getUUIDbyID($row['id_user']).'/">'.$this->getDisplayNamebyID($row['id_user']).'</a></h2></div>';
            $content .= '<div class="contenu">Cliquez sur une image pour voir le détail.<br>';
            
            
            $sql = "SELECT * FROM iherba_photos WHERE id_obs = ".$row['idobs']." LIMIT 0,3";
            $results_photo = $wpdb->get_results( $sql , ARRAY_A );
            foreach ($results_photo as $row_photo)
            {
				$url = ($row['url_rewriting_fr']!=''?$row['url_rewriting_fr'].'-'.$row['idobs']:$row['idobs']);
			
				if (strpos($row_photo['nom_photo_initial'],".mp4") !== false){ // si c'est une vidéo
					$content .= '<a class="min-img" href="'.get_bloginfo('wpurl').'/observation/data/'.$url.'"><img style="margin:0;" src="/wp-content/plugins/iherbarium/img/icone_video.png" /></a>';
				}else{	  
				              
					$content .= '
					  <a class="min-img" href="'.get_bloginfo('wpurl').'/observation/data/'.$url.'" 
						 style="background-image:url(\''.$this->domaine_photo.'/medias/vignettes/'.$row_photo['nom_photo_final'].'\')">
					  </a>';
				 }
            }
            
            $content .= '</div></div>';
        }
		$content .= '<p class="btn_nav">';
        if ($offset != 0)
        {
            if ($user != '')
                $content .= '<a href="'.get_bloginfo('wpurl').'/utilisateur/'.$user.'/'.($offset-1).'/" class="btn_prec" >Précédent</a>';
            else
                $content .= '<a href="'.get_bloginfo('wpurl').'/observations/'.($offset-1).'/" class="btn_prec">Précédent</a>';
        }
        if ( ($total/$limit) > $offset)
        {
            if ($offset != 0)
                $content .= "&nbsp;&nbsp;&nbsp;&nbsp;";
            if ($user != '')
                $content .= '<a href="'.get_bloginfo('wpurl').'/utilisateur/'.$user.'/'.($offset+1).'/" class="btn_suiv">Suivant</a>';
            else
                $content .= '<a href="'.get_bloginfo('wpurl').'/observations/'.($offset+1).'/" class="btn_suiv">Suivant</a>';
        }
        $content .= '</p>';
        return $content;
    }
    
    function getPhotoHTML($idPhoto,$idObs)
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
       
        $content = $this->getHeaderHTML();
		if (strpos($results[0]['nom_photo_initial'],".mp4") !== false){ // si c'est une vidéo
				$content .= $texte_licence.'				
				<br>'; // ici afficher le bloc video
				$content .= '<video controls width="250"><source src="/medias/sources/'.$results[0]['nom_photo_final'].'" type="video/mp4"></video>';
		}else{
				$content .= $texte_licence.'
				<br>
				<a href="'.$this->domaine_photo.'/medias/big/'.$results[0]['nom_photo_final'].'" border=0>
        		<img src="'.$this->domaine_photo.'/medias/big/'.$results[0]['nom_photo_final'].'" >
        	</a>';
		}
        $content .= $this->getFooterHTML();
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
        
        $idObs = intval($idObs);
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
        
            $authorRecolt = $this->getDisplayNamebyID($row['id_user']);
            $authorDeterminObs = $this->getDisplayNamebyID($row2['id_user']);
        if ( $row2['date'] != '')
            $authorDeterminObs .= " (".$row2['date'].") ";;
        
        $urlqrencode = get_bloginfo('wpurl')."/observation/data/".$idObs;
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
    
    function getPageCarteHTML($longitude,$latitude,$radius)
    {
        $content = $this->getHeaderHTML();
        $content .= "<h1>Carte latitude ".$latitude." / longitude ".$longitude." / rayon ".$radius."</h1>";
        $content .= $this->getCarteHTML($longitude,$latitude,$radius);
        $content .= $this->getFooterHTML();
        return $content;
    }
    
    function getWhereZoneSQL($longitude,$latitude,$radius)
    {
        $p_sql = "iherba_observations.latitude >".((float)$latitude-(float)$radius). "
                    AND iherba_observations.latitude < ".((float)$latitude+(float)$radius). "
                    AND iherba_observations.longitude > ".((float)$longitude-(float)$radius). "
                    AND iherba_observations.longitude < ".((float)$longitude+(float)$radius);
        return $p_sql;
    }
    
    function getFromInventorySQL()
    {
        $p_sql = "iherba_photos,iherba_observations ,iherba_determination ";
        return $p_sql;
    }
    
    function getWhereInventorySQL()
    {
        $p_sql = "iherba_observations.latitude !=0 
                    AND iherba_observations.idobs=iherba_photos.id_obs
                    AND iherba_observations.public='oui' 
                    AND iherba_determination.`tropicosfamilyid` != '' 
                    AND iherba_observations.idobs=iherba_determination.id_obs ";
        return $p_sql;
    }
    
    function getOrderInventorySQL()
    {
        $p_sql = "iherba_determination.famille,iherba_determination.genre";
        return $p_sql;
    }
    
    function getObsByZoneArray($longitude,$latitude,$radius,$limit)
    {
        global $wpdb;
        $where_zone = $this->getWhereZoneSQL($longitude, $latitude, $radius);
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
                    AND ".$where_zone."
                GROUP BY iherba_observations.idobs
                ORDER BY iherba_observations.idobs DESC";
        if ($limit)
            $sql .= " LIMIT 0,".$limit.";";
        $results = $wpdb->get_results( $sql, ARRAY_A );
        return $results;
    }
    
    function getObsInventoryByZoneArray($longitude,$latitude,$radius,$limit,$offset=0)
    {
        global $wpdb;

        $sql = "SELECT distinct iherba_observations.idobs,
                                            iherba_observations.longitude,
                                            iherba_observations.latitude,
                                            iherba_observations.commentaires,
                                            iherba_photos.nom_photo_final,
                                            iherba_observations.deposit_timestamp ,
                                            iherba_determination.nom_commun,
                                            iherba_determination.nom_scientifique,
                                            iherba_determination.famille,
                                            iherba_determination.genre
                FROM ".$this->getFromInventorySQL()."
                WHERE ".$this->getWhereInventorySQL()."
                    AND ".$this->getWhereZoneSQL($longitude, $latitude, $radius)." 
                GROUP BY iherba_determination.tropicosid
                ORDER BY ".$this->getOrderInventorySQL();
        if ($limit)
            $sql .= " LIMIT ".$offset.",".$limit.";";
        $results = $wpdb->get_results( $sql, ARRAY_A );
        return $results;
    }

    
    function getCarteHTML($longitude,$latitude,$radius,$limit)
    {
        $content = "";

        $results = $this->getObsByZoneArray($longitude, $latitude, $radius,$limit);
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
            $arrayMarker ="";
            foreach ($results as $donnees)
            {     
                $nc = '';
                $ns = '';
                
                $results_nom = $this->getDeterminationArray($donnees['idobs']);
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
                $arrayMarker .='['.$donnees['latitude'].','.$donnees['longitude'].'],';
            }
            $content .='
                map.fitBounds(['.$arrayMarker.']);';

            $content .= '
            </script>
            <br/>
            <br/>';
        }

        return $content;
    }
    
    function getListeObsByZoneHTML($longitude,$latitude,$radius,$limit)
    {
        $content = "";
        
        $results = $this->getObsInventoryByZoneArray($longitude, $latitude, $radius,$limit);
        if(sizeof($results)>0)
        {
            
            $nbFamily = 0;
            $nbGenre = 0;
            $nbSpecies = 0;
            $current = array();
            foreach ($results as $donnees)
            {   
                $image=$this->domaine_photo.'/medias/vignettes/'.$donnees['nom_photo_final'];
                if($donnees['famille']!=$current['famille'])
                {
                    $content .= '<div class="h3">Famille : '.$donnees['famille'].'</div>';
                    $nbFamily++;
                }
                if($donnees['genre']!=$current['genre'])
                {
                    $content .= '<div class="h4">Genre : '.$donnees['genre'].'</div>';
                    $nbGenre++;
                }
                
                    $content.='<a class="min-img" href="'.get_bloginfo('wpurl').'/observation/data/'.$donnees['idobs'].'"
                    style="background-image:url(\''.$this->domaine_photo.'/medias/vignettes/'.$donnees['nom_photo_final'].'\')">
                        </a>';
                $content.='
                            <a href="'.get_bloginfo('wpurl').'/observation/data/'.$donnees['idobs'].'">'.
                            ' '.$donnees['nom_commun']."/".$donnees['nom_scientifique']."</a>";
                        
                $current['famille'] = $donnees['famille'];
                $current['genre'] = $donnees['genre'];
                $content.= "<br>";
                $nbSpecies++;
            }
            
            $content = '<div class="inventory"><div class="h2">Inventaires des especes déterminées</div>   
                        <br/>Nombre d\'observations dans cette zone : '.sizeof($this->getObsByZoneArray($longitude, $latitude, $radius,$limit)).'
                        <br/>Nombre de famille différentes dans cette zone : '.$nbFamily.'
                        <br/>Nombre de genres différents dans cette zone : '.$nbGenre.'
                        <br/>Nombre d\'espèces différentes dans cette zone : '.$nbSpecies.$content;
            $content .="</div>";

        }
        return $content;
    }
	
	
	function setNamesObservation($idObs,$nom_commun,$nom_scientifique){
		global $wpdb;

		$user = wp_get_current_user();
	  	$typo_id = $this->getIDbyUUID($user->data->user_login);
		if (!$typo_id){
			$typo_id = $this->setIDbyUUID($user->data->user_login);
		}
		if (!$typo_id){
          	return 0;
		}	
		if ($nom_commun != ''){
			$proba = 80;
			$sql_insert = $wpdb->prepare("INSERT INTO iherba_determination (id_obs,nom_commun,id_user,date,creation_timestamp,probabilite) VALUES (%d,%s,%s,%s,%s,%d)",$idObs,stripslashes($nom_commun),$typo_id,date('Y-m-d'),date('Y-m-d H:m:s'),$proba);
            $wpdb->query($sql_insert);
		}else{
					
			if ($nom_scientifique != ''){
				$proba = 95;
				$tab_nom_scientifique = explode("/ ", $nom_scientifique);
				$fiche = $tab_nom_scientifique[1];
				  
				$sql = $wpdb->prepare("SELECT * FROM iherba_taxref12_es WHERE CD_NOM = %s",$fiche);
				$results = $wpdb->get_results( $sql , ARRAY_A );            
				if(sizeof($results)>0){
	
					foreach ($results as $ligne){
						$referentiel = 'taxref';
						$tropicosfamilyid = '';
						$sql_insert = $wpdb->prepare("INSERT INTO iherba_determination (id_obs,referentiel,tropicosid,nom_commun,nom_scientifique,genre,famille,tropicosgenusid,tropicosfamilyid,reftaxonomiqueplusid,scientificname_wo_authors,scientificname_html,id_user,date,creation_timestamp,probabilite) VALUES (%d,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%d)",$idObs,$referentiel,$ligne['CD_NOM'],$ligne['NOM_VERN'],$ligne['NOM_COMPLET'],$ligne['LB_NOM1'],$ligne['FAMILLE'],$ligne['CD_TAXSUP'],$tropicosfamilyid,$referentiel.':'.$ligne['CD_NOM'],$ligne['LB_NOM'],$ligne['NOM_COMPLET_HTML'],$typo_id,date('Y-m-d'),date('Y-m-d H:m:s'),$proba);
									
						$wpdb->query($sql_insert);
					}
				}
	
			}
		}
	
		return 1;
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
add_filter('wpseo_title', array($iHerbarium, 'getTitleiHerbarium'));
add_filter( 'wpseo_metadesc', array($iHerbarium, 'getDesciHerbarium'));
add_action( 'template_redirect', array($iHerbarium, 'template_redirect_intercept') );
