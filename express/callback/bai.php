<?php  
include __DIR__.'/../wp-load.php'; 

$data = file_get_contents("php://input"); 
parse_str($data,$r); 
$r = json_decode($r['bizData'],true);
file_put_contents(PATH.'/data/bai.txt',json_encode($r),FILE_APPEND."\n");


if($r['serviceType'] == 'KY_ORDER_ATTACHMENT_PUSH'){
	//派件回单
	file_put_contents(PATH.'/data/bai.txt',"派件回单:".$data['fileUrl'],FILE_APPEND."\n");
}else if($r['serviceType'] == 'KY_ORDER_TRACE_PUSH'){
    $logisticID = $r['logisticID'];
}
 
$output = [
	'errorCode'=>"",
	'errorDescription'=>"",
	'result'=>TRUE,
];

echo json_encode($output);
