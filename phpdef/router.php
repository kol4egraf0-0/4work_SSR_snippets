<?php
$url = $_SERVER['REQUEST_URI'];
if( isset( $_GET['url'] ) ) {
    $url = $_GET['url'];
	if (substr($url,  -1) == '/') {
    	$url = substr_replace($url, '.html', -1, 1);
	}
	if (substr($url, -5) != '.html') {
    	$url = $url . '.html';
	}
	if( is_file( $url ) ) {
		// Avoid direct access to secure files
		define( '__SECURE_ACCESS__', 1 );
		require_once ( $url );
	} else {
		require_once ( '404.html' );
	}
	exit;
} 
require_once ( $url );
//echo $url;
?>