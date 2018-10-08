<?php
include('../wp-load.php');

echo "START ".date('Y m D H:i:s')."<br>\n";

$sql = "SELECT idobs FROM iherba_observations WHERE public='oui'";
$results = $wpdb->get_results( $sql , ARRAY_A );
if (sizeof($results)==0)
{
    echo "Erreur dans la récupération des observations";
    die();
}
$fp = fopen('../sitemap.xml', 'w');
fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
foreach ($results as $row)
{
    $url = ($row['url_rewriting_fr']!=''?$row['url_rewriting_fr'].'-'.$row['idobs']:$row['idobs']);
    fwrite($fp, '
    <url>
        <loc>'.get_bloginfo('wpurl').'/observation/data/'.$url.'</loc>
        <changefreq>monthly</changefreq>
    </url>');
}
fwrite($fp, '
</urlset>');
fclose($fp);

echo "END ".date('Y m D H:i:s')."<br>\n";

?>