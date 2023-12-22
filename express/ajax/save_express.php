<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 
use app\express\lib\Base;
$data = $_POST; 
$id = $data['id']; 
$vali  = think_validate([
    'name'   => '物品名称',
    'type'   => '快递公司',
    'pay_method'      => '付款方式',
    'express_type_id' => '产品类型',
],$data,[
    'required' => [
        ['name'],
        ['type'],
        ['pay_method'],
        ['express_type_id'],
    ], 
]);
if($vali){
    json($vali);
} 
$name = $data['name'];
if($name){
    cookie("express_rem_goods_name",$name,time()+86400*365*10);
}
$customer_address = $data['customer_address'];
$fahuo_address    = $data['fahuo_address'];
$vali    = think_validate([
    'contact'  => '收件人',
    'mobile'   => '联系电话',
    'address'  => '收件地址', 
],$customer_address,[
    'required' => [
        ['contact'],
        ['mobile'],
        ['address'], 
    ], 
]);
if($vali){
    json($vali);
} 

$vali    = think_validate([
    'contact'  => '寄件人',
    'mobile'   => '联系电话',
    'address'  => '寄件地址', 
],$fahuo_address,[
    'required' => [
        ['contact'],
        ['mobile'],
        ['address'], 
    ], 
]);
if($vali){
    json($vali);
}  
$data['order_num']        = express_order_num();  
$customer_address['province'] = $customer_address['arr'][0];
$customer_address['city']     = $customer_address['arr'][1];
$customer_address['county']   = $customer_address['arr'][2]; 
$data['customer_address']     = $customer_address; 

$fahuo_address['province'] = $fahuo_address['arr'][0];
$fahuo_address['city']     = $fahuo_address['arr'][1];
$fahuo_address['county']   = $fahuo_address['arr'][2];
$data['fahuo_address']     = $fahuo_address; 

$data['fahuo_address_id']       = Base::get_user_address_id($fahuo_address); 
$data['customer_address_id']    = Base::get_customer_address_id($customer_address);  

$pay_method = $data['pay_method'];
//快递公司
$type = $_POST['type'];
$class = '\\app\\express\\lib\\Express'.ucfirst($type);
if(!class_exists($class)){
  return json_error(['msg'=>'快递公司存在']); 
}
$class::before();
$data['monthly'] =  ''; 
$monthly_key = "express_".strtolower($type)."_customer_key";
$data['monthly'] = get_config("express_".$type.'_yuejie')?:get_config($monthly_key);  
$data['user_id'] = get_express_logined_user_id();
$input = db_allow("express_order",$data);
if(!$id){
    $input['created_at'] = now();    
}

$input['mobile']    = $customer_address['province'].$customer_address['city'].$customer_address['county'].$customer_address['mobile'];
$input['address']   = $customer_address['address'];
$input['contact']   = $customer_address['contact'];
$input['com_title'] = $customer_address['com_title'];
$input['num'] = $input['num']?:1;
$input['updated_at'] = now();
$y_order_num = $input['y_order_num'];
if($y_order_num){
    $input['wl_order_num'] = $y_order_num;
} 

/*
不记住上次
$default_pay_method   =  "express_".$type.'_default_pay_method';
$default_express_type =  "express_".$type.'_default_express_type_id';
set_config('express_default_pay_method',$_POST['type']);
set_config(strtolower($default_pay_method),$_POST['pay_method']);
set_config(strtolower($default_express_type),$_POST['express_type_id']);

*/
if($input['num'] > 1){
    $input['sub_num'] = $input['num'] - 1;
}

if($id){
    unset($data['id']);
    if($input['num'] == 1){
        $input['sub_num'] = 0;
    }
    db_update("express_order",$input,['id'=>$id]);
    $class::create_pdf($id);
    $data = db_get_one("express_order","*",['id'=>$id]);
    get_express_order_row($data); 
    return json_success(['msg'=>'修改成功','data'=>$data]); 
}else{
    $input['status'] = 1;
    $id = db_insert("express_order",$input); 
    $class::create_pdf($id);
    $data = db_get_one("express_order","*",['id'=>$id]);
    get_express_order_row($data); 
    return json_success(['msg'=>'操作成功','data'=>$data]);    
}

 

