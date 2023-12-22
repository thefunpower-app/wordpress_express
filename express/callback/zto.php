<?php 
/**
* 中通回调 
*/
include __DIR__.'/../wp-load.php'; 
use app\express\lib\ExpressZto as Exp;
$data = file_get_contents("php://input");  
file_put_contents(__DIR__.'/zto.txt',now().$data,FILE_APPEND);
parse_str($data,$r);
if($r['msg_type'] == 'Traces'){
    $d = json_decode($r['data'],true); 
    $billCode = $d['billCode'];
    $row = db_get_one("express_order","*",['wl_order_num'=>$billCode]); 
    Exp::get_wuliu($row);
}  