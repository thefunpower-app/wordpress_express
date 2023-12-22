<?php 
/**
* 需要配置 RoutePushService 
*/
include __DIR__.'/../wp-load.php'; 
use app\express\lib\ExpressSf as Exp;
$data = file_get_contents("php://input"); 
file_put_contents(__DIR__.'/sf.txt','data:'.$data,FILE_APPEND);
$r   = json_decode($data, true); 
$body = $r['Body']['WaybillRoute']?:$r['orderState']; 
if($body){
    $in = [];
    $key = 'order_num';
    foreach ($body as $v){ 
        $order_num    = $v['orderid'];
        $wl_order_num = $v['orderNo']; 
        if($order_num){
            $in[] = $order_num;     
        }
        if($wl_order_num){
            $in[] = $wl_order_num;     
        }
    }   
    $all = db_get("express_order","*",[$key=>$in]);
    foreach ($all as $row){
        if($row){
            Exp::get_wuliu($row);  
            if($row['amount'] < 1){
                get_express_money($row['type'],$row);    
            }     
        } 
    }
        
}
 


