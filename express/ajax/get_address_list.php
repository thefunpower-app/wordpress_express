<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 
$where = ['ORDER'=>['updated_at'=>'DESC']];
$wq = $_POST['wq'];
if($wq){
	$wq = trim($wq);
	$where['OR'] = [
		'contact[~]' => $wq,
		'mobile[~]' => $wq,
		'address[~]' => $wq,
		'com_title[~]' => $wq,
	];
}  
$all = db_pager("express_customer_address","*",$where);
$in = [
	'北京市','天津市','上海市','重庆市',
];
foreach($all['data'] as &$v){
	if(in_array($v['city'],$in)){
		$v['city'] = '市辖区';
		if(strpos($v['province'],'市')===false){
			$v['province'] = $v['province'].'市';
		}
	}
	$v['arr'] = [$v['province'],$v['city'],$v['county']];
} 
return json($all); 