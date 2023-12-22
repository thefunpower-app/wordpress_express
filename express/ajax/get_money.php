<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 
$support_price = [
	'sf',
];
$where = [ 
	'pay_method'=>1,
	'type'=> $support_price,
	'LIMIT'=>10,
];
$date = $_POST['date'];
if($date){ 
	if($date[0]){
		$where['created_at[>=]'] = trim($date[0].' 00:00:01');	
	}
	if($date[1]){
		$where['created_at[<=]'] = trim($date[1].' 23:59:59');	
	} 
} 
$wq = $_POST['wq'];
if($wq){
	$or = [];
	$or['order_num[~]'] = $wq; 
	$or['wl_order_num[~]'] = $wq; 
	$or['com_title[~]'] = $wq;
	$or['contact[~]'] = $wq;
	$or['mobile[~]'] = $wq;
	$or['address[~]'] = $wq;
	$where['OR'] = $or;
}

$all = db_get("express_order","*",$where);
 
if($all){
	foreach($all as $v){
		$type = $v['type'];
		if($v['amount'] < 1){
			get_express_money($type,$v);	
		}		
		$class = '\\app\\express\\lib\\Express'.ucfirst($type);
		if(class_exists($class)){
		   $class::get_wuliu($v);
		} 
	}
}

return json_success();
