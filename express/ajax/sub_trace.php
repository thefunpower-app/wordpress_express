<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 


sleep(10);
$need_sub_arr = [
	'zto',

];
$all = db_get("express_order","*",[
	'type'=>$need_sub_arr,
	'is_trace'=>0,
	'LIMIT'=>1000
]);
$i = 0;
foreach($all as $v){
	$type = $v['type'];
	$cls = "\\app\\express\\lib\\Express".ucfirst($type);
	$wl_order_num = $v['wl_order_num'];
	$phone = $v['mobile'];
	$is_trace = $cls::sub_trace($wl_order_num,$phone);
	db_update("express_order",['is_trace'=>$is_trace],['id'=>$v['id']]);
	if($is_trace){
		$i++;
	}
}
if($i>0){
	return json_success(['msg'=>$i.'条物流信息，订阅成功']);	
}else{
	return json_error(['msg'=>'']);
}




