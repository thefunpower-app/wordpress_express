<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php  
$data = $_POST; 
$id = $data['data']['id']; 

if(!$id){
	return json_error();
}
db_update("express_order",['has_printer'=>1],['id'=>$id]); 
return json_success();