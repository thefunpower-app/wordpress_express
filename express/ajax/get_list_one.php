<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 

$id = $_POST['id'];
$res = db_get_one("express_order",'*',['id'=>$id]);  
$res['is_sign_back'] = (string)$res['is_sign_back'];
return json_success(['data'=>$res]);