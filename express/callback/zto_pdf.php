<?php 
/**
* 中通回调
*  
*/
include __DIR__.'/../wp-load.php'; 
$data = file_get_contents("php://input"); 
$r    = json_decode($data, true); 
$result     = base64_decode($r['result']);
$waybillNo   = $r['billCode'];
$dataDigest = $r['dataDigest']; 
if($result&&$waybillNo&&strlen($result)>50){
	$one = db_get_one("express_order","*",['wl_order_num'=>$waybillNo]);
	$order_id  = $one['id'];
	if(!$order_id){
		return;
	}
	$pdf_url = '/uploads/zto_pdf/'.$waybillNo.'-'.mt_rand(0,1000).'.pdf';
	$pdf_file = WWW_PATH.$pdf_url;
	$dir  = get_dir($pdf_file); 
	create_dir_if_not_exists([$dir]);   
    file_put_contents($pdf_file,$result); 
    db_update("express_order",['pdf_url'=>$pdf_url],['id'=>$order_id],true);
    $new_update = [];
    $new_update[$waybillNo] = $pdf_url;   
	$express_num = $one['express_num'];
	$name = '';
	foreach($express_num as &$_express_num_v){
		$_express_num_v['pdf_url'] = $new_update[$_express_num_v['waybillNo']];
		$name .= $_express_num_v['waybillNo'];
	}  
	$up = [
		'express_num'=>$express_num,
	];  
	db_update("express_order",$up,['id'=>$order_id]);   
	echo json_encode(["result"=> "success","message"=> "success","status"=> true,"statusCode"=>"200"]); 
}else {
    echo json_encode(["result"=> "success","message"=> "success","status"=> true,"statusCode"=>"200"]); 
}



