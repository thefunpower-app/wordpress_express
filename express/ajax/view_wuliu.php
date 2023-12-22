<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 
use app\express\lib\Base;
$data = $_POST; 
$id = $data['id'];

$type = $_POST['type'];
$class = '\\app\\express\\lib\\Express'.ucfirst($type);
if(!class_exists($class)){
  return json_error(['msg'=>'快递公司存在']); 
}  
$order_num = $_POST['order_num'];
if($order_num && substr($order_num,0,4) == 'LOCK'){
  $res = db_get_one("express_order",'*',['id'=>$id]);
  $data = $res['wuliu_info'];
  $active_tab = $data[0]['mailNo'];
  return json_success(['data'=>$data,'order_num'=>$order_num,'active_tab'=>$active_tab]);
}
$data = $class::get_wuliu($data); 
$active_tab = '';
if($data){
  $active_tab = $data[0]['mailNo'];
}
return json_success(['data'=>$data,'order_num'=>$order_num,'active_tab'=>$active_tab]);
