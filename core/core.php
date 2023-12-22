<?php  
/**
 * @package xa-core
 * @version 1.0.0
 */
/*
Plugin Name: xa-core
Plugin URI: http://wordpress.org/plugins/xa-core/
Description:  xa-core
Author: Sun Kang
Version: 1.0.0
Author URI: http://wordpress.org/
*/  
define("XA-CORE",TRUE);
if(!defined('PATH')){ 
	define("PATH",substr(ABSPATH,0,-1)); 	
}
if(!defined('WWW_PATH')){ 
	define("WWW_PATH",PATH); 	
} 
date_default_timezone_set('Asia/Shanghai');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);   
// autoloader
global $g_autoloader;
if(!$g_autoloader){ 
	$g_autoloader = include __DIR__.'/vendor/autoload.php';	
}  

g_init_wordpress(); 
// redis 
global $g_redis_config;
$host = $g_redis_config['host']?:'127.0.0.1';
$port = $g_redis_config['port']?:6379;
$auth = $g_redis_config['auth']?:'';
predis($host,$port,$auth);
$redis = predis(); 

global $config;
$config['redis'] = [
  'host'=>$host,
  'port'=>$port,
  'auth'=>$auth, 
];

$config['sony_flake'] = [ 
  'from_date'=>'2022-10-27',
];
thefunpower\sonyflake\id::set($config);

function order_num(){
    return thefunpower\sonyflake\id::create($center_id=0,$work_id=1);
}

/**
* 加载语言包
*/
$lang = 'zh-cn';
lib\Validate::lang($lang);

add_action( 'init', "core_install");   
 
/**
* 安装SQL
*/
function core_install(){ 
	$lock = PATH.'/wp-content/core.lock';
	if(file_exists($lock)){
		return;
	}
	file_put_contents($lock,date("Y-m-d H:i:s"));
	$sql = "
		CREATE TABLE IF NOT EXISTS `config` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `title` varchar(255) NOT NULL,
		  `body`  text NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;  
	"; 
	db_query($sql); 
}

/**
* 省市区  
*/  
function get_city_area(){ 
    $file = __DIR__.'/data/city.json'; 
    ob_start();
    include $file;
    $d = ob_get_contents();
    ob_end_clean();  
    $d = json_decode($d,true); 
    return $d;
}
   
$_POST = xss_clean($_POST); 

function host(){
	return $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'];
}