<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 
use app\express\lib\Base;
$data = $_POST; 
$id = $data['id'];   
//快递公司
$type = $_POST['type'];
$class = '\\app\\express\\lib\\Express'.ucfirst($type);
if(!class_exists($class)){
  return json_error(['msg'=>'快递公司存在']); 
}
if(!$id){
  return json_error(['msg'=>'操作异常']);    
}  
unset($data['id']); 
//订单不取消，取消了会有问题的，再恢复是无法恢复的
$data = db_get_one('express_order','*',['id'=>$id]);  
$class::close($data['order_num']); 
db_update("express_order",[
    'status'=>-1,
    'updated_at'=>now(),
    'amount'=>0,
    'amount_list'=>[],
],['id'=>$id]);
send_pusher(['xa_reload_page'=>100],$channel='xa_express',$event='notice');
return json_success(['msg'=>'订单取消成功']);  
 

