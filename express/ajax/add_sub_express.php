<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 

$id = $_POST['id'];
$sub_num = $_POST['sub_num'];

if($id && $sub_num){
	$res = db_get_one("express_order","*",['id'=>$id]);
	$type = $res['type'];
	get_express_sub($type,$res); 
	return json_success(['msg'=>'操作成功']);
}else {
	return json_error(['msg'=>'操作异常']);
}