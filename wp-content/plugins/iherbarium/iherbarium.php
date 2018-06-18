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
        );
        $newRules = $newRule + $rules;
        return $newRules;
    }
    
    function add_query_vars($qvars) {
        $qvars[] = 'test';
        $qvars[] = 'idobs';
        $qvars[] = 'idphoto';
        return $qvars;
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
        
        
        include ('tpl/header.php');
        ?>
        Commentaires : <?php echo $results[0]['commentaires']?><br><br>
		Adresse de récolte : <?php echo $results[0]['address']?><br><br>
		<br>
		Cette observation a été déposée le : <?php echo $results[0]['date_depot']?><br>
		Voici les images constituant cette observation : <br>
		<?php 
		  $sql = "SELECT * FROM iherba_photos WHERE id_obs = ".$idObs;
		  $results_photo = $wpdb->get_results( $sql , ARRAY_A );
		  foreach ($results_photo as $row)
		  /*TODO: changer url..., canonical url*/
		  {
		      ?>
		      <a href="<?php echo get_bloginfo('wpurl')?>/scripts/large.php?name=<?php echo $row['nom_photo_final'] ?>">
		      	<img src="<?php echo $this->domaine_photo?>/medias/vignettes/<?php echo $row['nom_photo_final'] ?>">
		      </a>
		      <?php 
		  }	  
		?>
		<br/><br/>Cette observation a été localisée à la latitude <?php echo round($results[0]['latitude'], 4)?> et la longitude <?php echo round($results[0]['longitude'],4)?><br/><br/>
        
        <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
        <script type="text/javascript">
        var longitude =<?php echo $results[0]['longitude']?>;
        var latitude=<?php echo $results[0]['latitude']?>;
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
        <br/>
        
        
        <?php include ('tpl/footer.php');
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
       
        include ('tpl/header.php');
        ?>
        <?php echo $texte_licence?>
        <br>
        <a href="<?php echo $this->domaine_photo?>/medias/big/<?php echo $_GET['name']?>" border=0>
        		<img src="<?php echo $this->domaine_photo?>/medias/big/<?php echo $_GET['name']?>" >
        	</a>
        <?php 
        include ('tpl/footer.php');
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