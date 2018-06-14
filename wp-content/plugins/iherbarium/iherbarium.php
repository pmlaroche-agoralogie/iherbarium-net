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
        );
        $newRules = $newRule + $rules;
        return $newRules;
    }
    
    function add_query_vars($qvars) {
        $qvars[] = 'test';
        $qvars[] = 'idobs';
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
            $myid = (int)$amyid[sizeof($amyid)-1];
            
            $results = $wpdb->get_results( "SELECT * FROM iherba_observations WHERE idobs = ".$myid, ARRAY_A );
            include ('tpl/header.php');
            $this->pushoutput($results);
            include ('tpl/footer.php');
            exit;
        }
    }
    
    function pushoutput($message) {
        $this->output($message);
    }
    
    function output( $output ) {
        header( 'Cache-Control: no-cache, must-revalidate' );
        header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
        
        // Commented to display in browser.
        // header( 'Content-type: application/json' );
        
        echo json_encode( $output );
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