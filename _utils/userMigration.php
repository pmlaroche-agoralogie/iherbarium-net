<?php
include('../wp-load.php');

$sql_notin = "SELECT id_typo FROM iherba__users_typo_wp";

$sql = "SELECT uid,password,username FROM fe_users WHERE uid NOT IN (".$sql_notin.") LIMIT 0,1000";
echo $sql."<br>\n";
flush();
$results = $wpdb->get_results( $sql , ARRAY_A );

if (sizeof($results) >0 )
    echo "Continue<br>\n";
else 
    echo "Stop<br>\n";
flush();

foreach($results as $row)
{
    // begin transaction
    $wpdb->query('START TRANSACTION');
    
    $username = uuidSecure();
    $password = $row['password'];
    $email = $row['username'];
    echo "Create user $username, $password, $email<br>\n";
    flush();
    $user_id_wp = wp_create_user( $username, $password, $email );
    if (is_wp_error( $user_id_wp ) )
        $wpdb->query('ROLLBACK');
    else
    {
        $sql = "INSERT INTO iherba__users_typo_wp (id_wp,id_typo) VALUES (".$user_id_wp.",".$row['uid'].")";
        echo $sql."<br>\n";
        flush();
        $result = $wpdb->query( $sql );
        if ($result)
            $wpdb->query('COMMIT');
        else 
            $wpdb->query('ROLLBACK');
    }
    
}


function uuidSecure() {
    
    $pr_bits = null;
    $fp = @fopen('/dev/urandom','rb');
    if ($fp !== false) {
        $pr_bits .= @fread($fp, 16);
        @fclose($fp);
    } else {
        // If /dev/urandom isn't available (eg: in non-unix systems), use mt_rand().
        $pr_bits = "";
        for($cnt=0; $cnt < 16; $cnt++){
            $pr_bits .= chr(mt_rand(0, 255));
        }
    }
    
    $time_low = bin2hex(substr($pr_bits,0, 4));
    $time_mid = bin2hex(substr($pr_bits,4, 2));
    $time_hi_and_version = bin2hex(substr($pr_bits,6, 2));
    $clock_seq_hi_and_reserved = bin2hex(substr($pr_bits,8, 2));
    $node = bin2hex(substr($pr_bits,10, 6));
    
    /**
     * Set the four most significant bits (bits 12 through 15) of the
     * time_hi_and_version field to the 4-bit version number from
     * Section 4.1.3.
     * @see http://tools.ietf.org/html/rfc4122#section-4.1.3
     */
    $time_hi_and_version = hexdec($time_hi_and_version);
    $time_hi_and_version = $time_hi_and_version >> 4;
    $time_hi_and_version = $time_hi_and_version | 0x4000;
    
    /**
     * Set the two most significant bits (bits 6 and 7) of the
     * clock_seq_hi_and_reserved to zero and one, respectively.
     */
    $clock_seq_hi_and_reserved = hexdec($clock_seq_hi_and_reserved);
    $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
    $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;
    
    return sprintf('%08s-%04s-%04x-%04x-%012s',
        $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $node);
}