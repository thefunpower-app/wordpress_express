<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php  
$data = g('data');
if($data){
    set_config("express_support",$data);
    return json_success(['msg'=>'排序成功']);
}