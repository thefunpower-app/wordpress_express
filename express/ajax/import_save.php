<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 
ignore_user_abort(true); 
set_time_limit(0); 
$data = $_POST['data'];
if(!$data){
	return json_error(['msg'=>'操作异常']);
}
$err  = 0;
$list = [];
$total = count($data);
$a = db_get("express_customer_address","*",[]);
$a_list = [];
if($a){
	foreach($a as $v){
		$mobile = $v['mobile'];
		$a_list[] = $mobile;
	}
} 
foreach($data as $k=>$v){
	$com_title = $v['收件公司'];
	$contact   = $v['收件人'];
	$mobile    = $v['收件人手机'];
	$address   = $v['收件人地址'];
	if($a_list && is_array($a_list) && in_array($mobile,$a_list)){
		continue;
	}
	if(!$name || !$phone || !$address){
		unset($data[$k]);
	} 
	$res = get_baidu_nlp_address($address); 
	$province = ''; 
	if($res['province']){
		$province = $res['province'];
		$city     = $res['city'];
		$region   = $res['region'];
		$street   = $res['street'];
	} 
	if(!$province || !$city || !$region){
		$err+=1;
	}else{
		$new = [
			'province' => $province,
			'city'     => $city,
			'county'   => $region,
			'com_title' => $com_title,
			'contact'   => $contact,
			'mobile'    => $mobile,
			'address'   => $street, 
			'created_at'   => now(), 
			'updated_at'   => now(), 
		]; 
		$res = db_get_one("express_customer_address","*",[
			'contact'=>$new['contact'],
			'mobile' =>$new['mobile'], 
		]); 
		if(!$res){
			db_insert('express_customer_address',$new);
		}
	}  
	//500毫秒 
	//usleep(1000*500); 
} 
if($err > 0){
	return json_error(['msg'=>"共".$total.",其中".$err."条记录无法导入",'data'=>$list]);
} 
return json_success(['msg'=>'操作成功']);