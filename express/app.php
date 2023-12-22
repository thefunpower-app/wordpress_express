<?php  
global $_wp_express_upload_path;
$_wp_express_upload_path = '/uploads/';
 
//当前登录用户的 ID
function get_express_logined_user_id(){
	return get_current_user_id();
}


function express_order_num(){
	return order_num();
}  

//当前域名URL
function get_express_home_url(){
	return home_url();
}
include __DIR__.'/helper.php';