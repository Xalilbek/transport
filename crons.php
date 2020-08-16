<?php
error_reporting( false );
ini_set( 'display_errors' , false );

set_time_limit( 0 );
ini_set( 'memory_limit' , '999999999M' );

$cronUrls = array (
    
);

foreach ( $cronUrls as $url ) 
{
    $ch = curl_init();
    curl_setopt( $ch , CURLOPT_URL , $url );
    curl_setopt( $ch , CURLOPT_HEADER , false );
    curl_setopt( $ch , CURLOPT_NOBODY , true );
    curl_setopt( $ch , CURLOPT_RETURNTRANSFER , false );
    curl_setopt( $ch , CURLOPT_TIMEOUT , 2 );
    curl_exec( $ch );
    curl_close( $ch );
}

echo date( "Y-m-d H:i:s" ) . "\n";
