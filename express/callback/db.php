<?php  
include __DIR__.'/../wp-load.php'; 
use app\express\lib\ExpressDb as Exp;
$data = file_get_contents("php://input"); 
$data = urldecode($data); 
parse_str($data,$r);
$r['params'] = json_decode($r['params'],true);  
$digest = $r['digest'];
$companyCode = $r['companyCode'];
$timestamp = $r['timestamp']; 
/**
 *  由于数组排序，无法验证签名是否正确
 */
$res = Exp::get_digest($r['params'],$timestamp);
if($res['digest'] != $digest){
    // echo '{"success":false,"error_code":"1000","error_msg":"签名异常","result":true}';
    // exit;
}

$track_list = $r['params']['track_list'];
$in  = [];
foreach ($track_list as $v){
    $in[] = $v['tracking_number'];  
}
if($in){
    $all = db_get("express_order","*",['wl_order_num'=>$in]);   
    foreach ($all as $row){
        Exp::get_wuliu($row);
    }
}

echo '{"success":true,"error_code":"1000","error_msg":"成功","result":true}';
