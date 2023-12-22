<?php 

function get_express_charts_month($month = []){  
    foreach ($month as $v){  
        $where = [
    		'created_at[>=]' => date("Y-m-d 00:00:01",strtotime($v[0])),
    		'created_at[<=]' => date("Y-m-d 23:59:59",strtotime($v[1])),
    		'status[>]'=>0,
    	];  
    	$count = db_get_count("express_order",$where)?:0; 
    	if($count > 0){
    		$sum    = db_get_sum("express_order","amount",$where)?:0;
    		$data[] = $count;  
    		$list_chart[date("Y-m",strtotime($v[0]))] = [ 
    			'count'=>$count,
    			'sum'=>$sum,
    		];
    	} 
    }
	
    foreach($list_chart as $k=>$v){
    	$chart_date[]  = $k;
    	$chart_count[] = $v['count'];
    	$chart_sum[]   = $v['sum'];
    } 
    return [
    	'date'=>$chart_date,
    	'count'=>$chart_count,
    	'sum'=>$chart_sum,
    ];

}


function get_express_charts_data($date){
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
    		$list_chart[$v] = [ 
    			'count'=>$count,
    			'sum'=>$sum,
    		];
    	}
    }   
    foreach($list_chart as $k=>$v){
    	$chart_date[]  = $k;
    	$chart_count[] = $v['count'];
    	$chart_sum[]   = $v['sum'];
    }
    if($chart_date){
    	$chart_date  = array_reverse($chart_date);
    }
    if($chart_count){
    	$chart_count = array_reverse($chart_count);
    }
    if($chart_sum){
    	$chart_sum   = array_reverse($chart_sum);
    }
    return [
    	'date'=>$chart_date,
    	'count'=>$chart_count,
    	'sum'=>$chart_sum,
    ];

}
/**
* 快递费用
*/
function get_express_money($type,$row){
	if(!$row['wl_order_num']){return;}
	$cls = '\app\\express\\lib\\Express'.ucfirst($type); 
	$res = $cls::get_money($row['wl_order_num'],'',$row);  
	return true;
}
/**
* 子单号 
*/
function get_express_sub($type,$row){ 
	$type = trim($type); 
	$cls = "\app\\express\\lib\\Express".ucfirst($type); 
	$sub_num = (int)$_POST['sub_num'];
	if($sub_num < 1 || $sub_num > 99){
		return json_error(['msg'=>'追加子单数量过大']);
	}
	$res = $cls::sub($row['order_num'],$sub_num,$row['id']); 
	return true;
}




function express_status_list(){ 
	return [
		1=>['title'=>'待取件','color'=>''],
		20=>['title'=>'已揽收','color'=>'lightcoral'],
		30=>['title'=>'已收件','color'=>'darkturquoise'],
		35=>['title'=>'运输中','color'=>'fuchsia'], 
		40=>['title'=>'派送途中','color'=>'hotpink'],
		100=>['title'=>'已签收','color'=>'green'], 
	]; 
}

function express_status_list_el_select(){
	$list = express_status_list();
	$new = [];
	$title = db_get_count("express_order",['status[>]'=>0]);
	$new[] = ['label'=>'全部（'.$title."）",'value'=>0];
	foreach($list as $k=>$v){ 
		$where = ['status'=>$k]; 
		$count = db_get_count("express_order",$where);
		$title = $v['title'];
		if($count > 0){
			$title = $title."（".$count."）";
		}
		$new[] = ['label'=>$title,'value'=>$k];
	}
	$new[] = ['label'=>'已取消','value'=>-1];
	return $new;
}

function get_express_order_row(&$v){
	$amount_list = $v['amount_list']; 
	$amount_tip  = "";
	if($amount_list && is_array($amount_list)){
		foreach($amount_list as $k=>$vv){
			$amount_tip .= $k." ".$vv." ";
		}
	} 
	if(!$amount_tip){
		$amount_tip = "等待更新价格";
	}
	$v['amount_tip'] = $amount_tip; 
	$pdf_url = $v['pdf_url'];
	if($pdf_url){
		$v['pdf_url'] = get_express_home_url().$pdf_url;
	}
	$express_status_list = express_status_list();
	$get = $express_status_list[$v['status']];
	$v['status_txt']   = $get['title'];
	$v['status_color'] = $get['color']; 
	if($v['status'] < 0){
		$v['status_txt']   = '已取消';
		$v['status_color'] = 'red'; 
	}

	$v['user_name'] = '';
	if($v['user_id']){
		$v['user_name'] = get_the_author_meta('user_login', $v['user_id']); 
	}
	$v['created_short'] = date("Y-m-d H",strtotime($v['created_at']));
	$express_num = $v['express_num'];

	if($express_num && is_array($express_num)){
		foreach($express_num as &$vv){
			if(is_array($vv) && $vv['pdf_url']){
				$vv['pdf_url'] = get_express_home_url().$vv['pdf_url'];
			} 
		}
		$v['express_num'] = $express_num;
	}
	$customer_address = $v['customer_address'];
	if($customer_address){
		$customer_address['address_full'] = $customer_address['province'].$customer_address['city'].$customer_address['county'].$customer_address['address'];
		$v['customer_address'] = $customer_address;
	}
	$support_add_sub = false;
	$type = $v['type'];
	$cls = "\\app\\express\\lib\\Express".ucfirst($type);
	if(class_exists($cls)){
		$support_add_sub = $cls::$support_add_sub;
	}
	if($support_add_sub){
		if($v['status'] > 1){
			$support_add_sub = false;
		}
	}
	$v['support_add_sub'] = $support_add_sub;

	$arr = express_support_kd();
	$v['type_text'] = $arr[$v['type']];
	$monthly = $v['monthly'];
	$monthly_text = ''; 
	// 1:寄方付 2:收方付 3:第三方付
	if($v['pay_method'] == 1){
		if($monthly){
			$monthly_text = " 月结号：".$monthly;	
		}		
	}
	$v['monthly_text'] = $monthly_text;
	$arr = [1=>'寄付',2=>'收付']; 
	$v['payment_text_short'] = $arr[$v['pay_method']];
	$v['payment_text'] = $arr[$v['pay_method']].$monthly_text;
	$v['is_lock'] = false;
	if(substr($v['order_num'],0,4) == 'LOCK'){
		$v['is_lock'] = true;
	} 
} 

function express_support_kd(){
	static $list;
	if($list){
		return $list;
	}
	$all = express_support_kd_full();
	$c = get_config("express_support"); 
	$list = [];
	if($c){
		foreach($c as $v){
			$list[$v] = $all[$v];
		}
	}
	return $list;
}

function express_support_default(){
	$all = express_support_kd();
	$default = '';
	if($all){
		foreach($all as $k=>$v){
			if(!$default){
				$default = $k;
				break;
			}
		} 
	} 
	return $default;
}


function express_support_kd_full($flag = false){ 
	$find = __DIR__.'/lib/Express';
	$all = glob($find.'*.php');
	$list = []; 
	foreach($all as $v){ 
		$name = substr($v,strrpos($v,'/')+1); 
		$name = str_replace(".php","",$name);
		$name = str_replace("Express","",$name); 
		$name_lower = strtolower($name);
		$cls = "\\app\\express\\lib\\Express".$name; 
		$title = $cls::$title;
		$list[$name_lower] = $title; 
	}
	$support = get_config("express_support");
	if($support){
		$new_list = [];
		foreach($support as $v){
			if($list[$v]){
				$new_list[$v] = $list[$v];
			} 
		}
		if($flag){
			foreach($list as $k=>$v){
				if(!$new_list[$k]){
					$new_list[$k] = $v;
				}
			}
		}
		$list = $new_list;
	} 
	return $list;  
}


function express_get_zhixia_city(){
	return [
		'北京市','上海市','天津市','重庆市','海南省'
	];
}

function express_get_new_address($str){
	return $str;
	$arr = express_get_zhixia_city();
	foreach($arr as $v){
		$str = str_replace($v,'',$str);
	}
	return $str;
}