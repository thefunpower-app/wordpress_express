<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 

$id = get_current_user_id();
$key = "express_auto_printer_".$id; 
$d = get_config($key); 
json_success(['data'=>$d]);