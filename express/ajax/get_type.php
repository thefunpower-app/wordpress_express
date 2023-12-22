<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 

/**
快件产品类别表
顺丰特快 顺丰标快 等
https://open.sf-express.com/developSupport/734349?activeIndex=324604
*/

$key = $_POST['key'];
if(!$key){
	return json_error(['msg'=>'参数异常']);
}

$class = '\\app\\express\\lib\\Express'.ucfirst($key);
if(!class_exists($class)){
	return json_error(['msg'=>'快递公司存在']);	
}
if(!method_exists($class,'get_express_type') || !method_exists($class,'get_pay_method')){
	return json_error(['msg'=>'快递类配置数据异常']);	
}
$data['express_type'] = $class::get_express_type();
$data['pay_method'] = $class::get_pay_method();


$default_pay_method   =  "express_".$key.'_default_pay_method';
$default_express_type =  "express_".$key.'_default_express_type_id';
$data['default_pay_method']   = get_config(strtolower($default_pay_method));
$data['default_express_type'] = get_config(strtolower($default_express_type));

return json_success([
	'data'=>$data,
	'class'=>$class,
	'default_pay_method'=>$default_pay_method,
	'default_express_type'=>$default_express_type,
	'is_sub'=>$class::$support_add_sub,
	'is_sign_back'=>$class::$support_is_sign_back,
]);