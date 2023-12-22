<?php 
define("EXPRESS_VERSION",'1.0.0');  
define('EXPRESS__DIR', __DIR__ ); 
if(!defined('XA-CORE')){
	if(!defined('PATH')){
		define("PATH",__DIR__.'/../../..'); 	
	}
	date_default_timezone_set('Asia/Shanghai');
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);  
	// autoloader
	global $g_autoloader;
	if(!$g_autoloader){
		$g_autoloader = include __DIR__.'/vendor/autoload.php';	
	}
	$g_autoloader->addPsr4("app\\express\lib\\",__DIR__.'/lib/');    
	// redis 
	global $g_redis_config;
	$host = $g_redis_config['host']?:'127.0.0.1';
	$port = $g_redis_config['port']?:6379;
	$auth = $g_redis_config['auth']?:'';
	predis($host,$port,$auth);
	$redis = predis();  
	/**
	* 加载语言包
	*/
	$lang = 'zh-cn';
	lib\Validate::lang($lang);
}else{
    if($g_autoloader){
    	$g_autoloader->addPsr4("app\\express\lib\\",__DIR__.'/lib/');   	
    }	
}

  
include __DIR__.'/app.php';
include __DIR__.'/wordpress.php'; 	
  
global $g_express_url;
$g_express_url = '/wp-content/plugins/core';
 

//加载必要的JS
function express_js(){
	global $g_express_url;
	$page = $_GET['page'];
	if(strpos($page,'express')!==false){
		echo '<script type="text/javascript" src="'.$g_express_url.'/node_modules/jquery/dist/jquery.js"></script>
		<script type="text/javascript" src="'.$g_express_url.'/node_modules/vue/dist/vue.min.js"></script>
		<script type="text/javascript" src="'.$g_express_url.'/node_modules/element-ui/lib/index.js"></script>
		<script type="text/javascript" src="'.$g_express_url.'/node_modules/layui/dist/layui.js"></script>
		<script type="text/javascript" src="'.$g_express_url.'/node_modules/js-cookie/dist/js.cookie.js"></script>
		<link rel="stylesheet" type="text/css" href="'.$g_express_url.'/node_modules/element-ui/lib/theme-chalk/index.css"> 
		<script type="text/javascript" src="'.$g_express_url.'/node_modules/moment/min/moment.min.js"></script> 
		<script type="text/javascript" src="'.$g_express_url.'/node_modules/daterangepicker/daterangepicker.js"></script>  
		<script type="text/javascript" src="'.$g_express_url.'/node_modules/daterangepicker/moment.min.js"></script>  
		<script type="text/javascript" src="'.$g_express_url.'/node_modules/xlsx/dist/xlsx.full.min.js"></script>   
		<script type="text/javascript" src="'.$g_express_url.'/node_modules/jqueryui/jquery-ui.min.js"></script>  
		<script type="text/javascript" src="'.$g_express_url.'/node_modules/echarts/dist/echarts.min.js"></script>   
		<script type="text/javascript" src="/wp-content/plugins/express/js/reconnecting-websocket.min.js"></script>  
		<link rel="stylesheet" type="text/css" href="'.$g_express_url.'/node_modules/daterangepicker/daterangepicker.css" />
		<link rel="stylesheet" type="text/css" href="'.$g_express_url.'/node_modules/purecss/build/pure.css" /> 

		';
	}
	
}  
