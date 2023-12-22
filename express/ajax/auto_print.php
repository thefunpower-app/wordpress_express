<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 

$id = get_current_user_id();

$type = $_POST['type'];
$printer = $_POST['printer'];
$key = "express_auto_printer_".$id;
if($type == 'confirm'){
	set_config($key,$printer);
}else{
	set_config($key,'');
}
json_success(['msg'=>'操作成功']);