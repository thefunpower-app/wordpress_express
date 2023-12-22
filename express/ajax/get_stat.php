<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 
$date = $_POST['date'];
if(!$date){
	$start_date = date("Y-m-d",time()-86400*14);
	$date       = get_dates($start_date,now());   
	$date = array_reverse($date);
}else{
	$date       = get_dates($date[0],$date[1]);   
	$date = array_reverse($date);
} 
$list = [];
$list_chart = [];
foreach($date as $k=>$v){
	$where = [
		'created_at[>=]' => date("Y-m-d 00:00:01",strtotime($v)),
		'created_at[<=]' => date("Y-m-d 23:59:59",strtotime($v)),
		'status[>]'=>0,
	]; 
	$count = db_get_count("express_order",$where)?:0; 
	if($count > 0){
		$sum    = db_get_sum("express_order","amount",$where)?:0;
		$data[] = $count;  
		$list[] = [
		    'date'=>$v,
			'count'=>$count,
			'sum'=>$sum,
		];
	}
}   
$ret = [];
//最近14天
$ret['top'] = $list; 

//本月图表  
$date  = get_dates(date("Y-m-01"),now());   
$date = array_reverse($date); 
$ret['charts'] = get_express_charts_data($date);

//上月图表
$arr = lib\Time::get('lastmonth');
$a = date("Y-m-d 00:00:01",$arr[0]);
$b = date("Y-m-d 23:59:59",$arr[1]);  
$date  = get_dates($a,$b); 
$date = array_reverse($date); 
$ret['charts_last'] = get_express_charts_data($date); 

//今年 
$arr = lib\Time::every_month(date('Y-01-01'),date('Y-m-d'));
$ret['year'] =  get_express_charts_month($arr); 

//今年 
$arr = lib\Time::every_month(date('Y-01-01'),date('Y-m-d'));
$ret['year'] =  get_express_charts_month($arr); 

//去年 
$last_year = strtotime("-1 year");
$arr = lib\Time::every_month(date('Y-01-01',$last_year),date('Y-m-d',$last_year));
$ret['year_last'] = $year_last = get_express_charts_month($arr); 
$ret['year_last_has'] = false;
if($year_last['count'] > 0)
    $ret['year_last_has'] = true;

$where = [];
$where['GROUP'] = 'type';
$where['status[>]'] = 1;
$arr = lib\Time::get('lastmonth');
$a = date("Y-m-d 00:00:01",$arr[0]);
$b = date("Y-m-d 23:59:59",$arr[1]); 
$where['created_at[>=]'] = $a;
$where['created_at[<=]'] = $b;
$all    = db_get("express_order",["type","amount"=>"SUM(amount)"],$where);
 
foreach($all as &$vv){
	$cls = "app\\express\lib\\Express".ucfirst($vv['type']);
	$vv['type'] = $cls::$title;
}
$ret['yunfei2'] = $all;

$arr = lib\Time::get('month');
$a = date("Y-m-d 00:00:01",$arr[0]);
$b = date("Y-m-d 23:59:59",$arr[1]); 
$where['created_at[>=]'] = $a;
$where['created_at[<=]'] = $b;
$all    = db_get("express_order",["type","amount"=>"SUM(amount)"],$where);
foreach($all as &$vv){
	$cls = "app\\express\lib\\Express".ucfirst($vv['type']);
	$vv['type'] = $cls::$title;
}
$ret['yunfei1'] = $all;

return json_success(['data'=>$ret]);
