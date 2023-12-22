<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 
$where = [ 
	'status[!]'=>100, 
	'LIMIT'=>100,
];
$all = db_get("express_order","*",$where)?:[]; 
foreach($all as $v){
	$type = $v['type'];
	get_express_money($type,$v);
	$class = '\\app\\express\\lib\\Express'.ucfirst($type);
	if(class_exists($class)){
	   $class::get_wuliu($v);
	} 
} 
return json_success(['count'=>count($all)]);