<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 
$body = $_POST['data'];
if(!$body){
	return json_error(['msg'=>'缺少body参数']);
} 
$body = str_replace("\n","",$body);
$body = str_replace("<br>","",$body);

$res = get_baidu_nlp_address($body);
if($res['province']){
	return json_success(['data'=>$res]);
}else {
	return json_error(['msg'=>'解析失败']);
}

