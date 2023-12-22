<?php 
/**
* 极兔回调
* https://open.jtexpress.com.cn/#/apiDoc/orderserve/statusFeedback 
*/
include __DIR__.'/../wp-load.php'; 
$data = file_get_contents("php://input"); 
$r    = json_decode($data, true); 

file_put_contents(PATH.'/data/jt.txt',json_encode($r));


