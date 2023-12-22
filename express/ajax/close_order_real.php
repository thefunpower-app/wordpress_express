<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 
/**
* 真实取消订单
*/
$time = 3600*8;

$all = db_get("express_order","*",[
	'status'        => -1,
	'updated_at[<]' => date('Y-m-d H:i:s',time()-$time)
]);
 
if($all){
	foreach($all as $v){
		$type = $v['type'];
		$class = '\\app\\express\\lib\\Express'.ucfirst($type);
		if(class_exists($class)){
		  	$class::close($v['order_num']); 
		}
		db_update("express_order",['status'=>-2,'real_close_at'=>now()],['id'=>$v['id']]);
	}
}

return json_success(); 