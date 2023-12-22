<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 

$post = $_POST;
unset($post['action']);
if(!$post){
	return json_error(['msg'=>'参数异常']);
}

 
foreach($post as $k=>$v){
	if($k){
		set_config($k,$v);	
	}	
}

return json_success(['msg'=>'保存配置成功']);
