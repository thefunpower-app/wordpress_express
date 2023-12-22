<?php 

$file = __DIR__ . '/../../../wp-blog-header.php';
if($file){
	define( 'WP_USE_THEMES', false ); 
	require $file;
}

