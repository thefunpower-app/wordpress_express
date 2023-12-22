<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 

$res = db_get_one("express_fahuo_address","*",['ORDER'=>['updated_at'=>'DESC']]);
if($res){
	$res['arr'] = [$res['province'],$res['city'],$res['county']];
	return json_success(['data'=>$res]);
}else {
	return json_error();
}