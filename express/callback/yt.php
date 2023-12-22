<?php 
/**
* 圆通回调
* https://open.yto.net.cn/interfaceDocument/menu251/submenu256 
*/ 
include __DIR__.'/../wp-load.php'; 
use app\express\lib\ExpressYt as Exp;
$data = file_get_contents("php://input");   
parse_str($data,$r);
$xml = $r['logistics_interface'];
if(!$xml){
	return;
}
$arr = xml2array($xml); 
$txLogisticID = $order_num = $arr['txLogisticID'];
$wl_order_num = $arr['mailNo'];
$id = $arr['logisticProviderID'];
if($id != 'YTO'){
	return;
} 

$where = [
	'order_num'=>$order_num
];
$row = db_get_one("express_order","*",$where);
if(!$row){
	return;
}
Exp::get_wuliu($row); 

$return = [ 
		'logisticProviderID'=>'YTO',
		'txLogisticID'=>$txLogisticID,
		'success'=>'true', 
]; 
echo  array2xml($return,'Response');
