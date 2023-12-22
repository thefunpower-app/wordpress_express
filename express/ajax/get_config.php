<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 

$k = $_POST['key'];
$data = get_config($k)?:['obj'=>1];	 
return json_success(['data'=>$data]); 