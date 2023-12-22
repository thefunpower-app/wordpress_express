<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 

$post = $_POST;
unset($post['action']);
$k = $post['key'];
if(!$post || !$k){
	return json_error(['msg'=>'参数异常']);
}
$c = get_config("express_support"); 
if($c ){
	$i = array_search($k,$c);
	unset($c[$i]);
	if(!$c){
		$c = [];
	}
	set_config("express_support",$c); 
}
return json_success(['msg'=>'操作成功']);
