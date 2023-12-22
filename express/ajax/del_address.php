<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 
$data = $_POST;
$id = $data['id'];
$data = db_allow("express_customer_address",$data);
unset($data['id'],$data['created_at'],$data['updated_at']);
if($id){
	db_del('express_customer_address',['id'=>$id]);
	return json_success(['msg'=>'删除成功']);
}else{
	
}