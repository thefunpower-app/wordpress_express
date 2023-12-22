<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 
$data = $_POST;
$id = $data['id'];
$arr = $data['arr'];
$data['province'] = $arr[0];
$data['city'] = $arr[1];
$data['county'] = $arr[2];
$data = db_allow("express_customer_address",$data);
unset($data['id'],$data['created_at'],$data['updated_at']);
if($id){
	$data['updated_at'] = now();
	checker_expresss_customer_exists($data,$id);
	db_update('express_customer_address',$data,['id'=>$id]);
	return json_success(['msg'=>'修改成功']);
}else{ 
	$data['created_at'] = now();
	$data['updated_at'] = now(); 
	checker_expresss_customer_exists($data);
	db_insert('express_customer_address',$data);
	return json_success(['msg'=>'添加成功']);
}

function checker_expresss_customer_exists($data,$id=''){
	$res = db_get_one("express_customer_address","*",[
		'contact'=>$data['contact'],
		'address'=>$data['address'],
		'mobile'=>$data['mobile'],
	]);
	if($res){
		if(!$id){
			return json_error(['msg'=>'收件人信息已存在']);
		}
		if($id && $res['id'] != $id){
			return json_error(['msg'=>'收件人信息已存在']);
		}
	}
	
}