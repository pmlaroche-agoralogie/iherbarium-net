<?php
function convertSexa1coord($var, $pos)
{ // D?cimal vers sexag?simal
    
    if ($pos == 'lat')
    {
        if ($var > 0)$card = 'N';
        else $card = 'S';
    }
    if ($pos == 'long')
    {
        if ($var > 0)$card = 'E';
        else $card = 'O';
    }
    
    $var = abs($var);
    $deg = intval($var);
    $min = ($var - $deg)*60;
    $sec = ($min - intval($min))*60;
    return str_pad($deg, 2, '0', STR_PAD_LEFT).'&deg;'.intval($min)."'".number_format($sec, 2).'"'.$card;
}

function convertSexa2coord($lat, $long)
{
    return convertSexa1coord($lat,'lat'). " , ".convertSexa1coord($long,'long');
}