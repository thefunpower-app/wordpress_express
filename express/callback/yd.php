<?php 
/**
* 韵达的回调只是散单的，没什么用
* 散单创建的时候没有返回运单号，而散单回调时会返回订单号及运单号
* 
*/
include __DIR__.'/../wp-load.php'; 
use app\express\lib\ExpressYd as Exp;
$data = file_get_contents("php://input"); 
file_put_contents(__DIR__.'/yd.txt','data:'.$data,FILE_APPEND);


$arr = [
    "result" => true,
    "code"=> "0000",
    "message"=> "请求成功",
    "data"=>[
        "orderid" => "14437042180846542"
    ]
];
echo json_encode($arr,JSON_UNESCAPED_UNICODE);
