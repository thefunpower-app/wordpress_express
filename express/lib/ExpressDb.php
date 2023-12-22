<?php 
namespace app\express\lib;
use ExpressTemplate\Db; 
use helper_v3\Pdf; 
/*
需要的接口权限

【新】下单服务接口
撤销订单
查询订单
新标准轨迹查询
修改订单


注：沙箱环境需要下子母件订单必须"客户编码 customerCode" 传值 219401 或者219402；

https://dpopen.deppon.com/#/wantAccess/myApiConfig
*/
class ExpressDb extends Base{ 
	public static $debug = false;
	public static $support_is_sign_back = false;
	public static $title = '德邦';
	//支持子单
	public static $support_add_sub = true;
	public static $id;
	public static $order_info;
	/* 
	* 付款方式
	0、发货人付款（现付）（大客户模式不支持寄付） 1、收货人付款（到付） 2、发货人付款（月结）
	*/
	public static function get_pay_method(){ 
	  return [
	  		1=>'寄方付',
		    2=>'收方付', 
	  		//-1=>'现付', 
	  ];
	} 
	/**
	* 产品类别 
	* https://dpopen.deppon.com/#/apiDocs/apiDetail/CREATE_ORDER_NOTIFY
	*/
	public static function get_express_type(){
	  return [
	  		'PACKAGE'=>'标准快递', 
	  		'TZKJC'=>'特快专递', 
	  		/*'RCP'=>'大件快递360', 
	  		'NZBRH'=>'重包入户', 
	  		'ZBTH'=>'重包特惠', 
	  		'WXJTH'=>'微小件特惠',  */ 
	  ];
	} 

	/**
	* 月结
	*/
	public static $logisticID;

	/**
	* 订单运费查寻 
	*/
	public static function get_money($wl_order_num,$phone = '',$row = []){ 
	   $res = self::get_one_where(['wl_order_num'=>$wl_order_num]); 
	   if(!$res){
	   	 	return;
	   }
	   $id = $res['id'];
	   $par = self::query($res); 
	   $amount = $par['totalPrice'];
	   $amount_list = [];
	   $list = [
		   	'insurancePrice'=>'保价费', 
		   	'transportPrice'=>'运输费用',
		   	'vistReceivePrice'=>'上门接货费',
		   	'deliveryPrice'=>'送货费用',
		   	'backSignBillPrice'=>'签收回单费',
		   	'packageServicePrice'=>'包装服务费',
		   	'smsNotifyPrice'=>'短信通知费用',
		   	'otherPrice'=>'其他费用',
		   	'fuelSurchargePrice'=>'燃油附加费',
	   ]; 
	   foreach($list as $k=>$v){
	   		$price = $par[$k];
	   		if($price > 0){
	   			$amount_list[$v] = bcmul($price,1,2);	
	   		}
	   }
	   if($amount > 0){
		   	self::update($id,[
	 			'amount'     => $amount,
	 			'amount_list'=> $amount_list, 
	 		]);  
	   } 
	}
	public static  function close_by($order_num,$wl_order_num){ 
	   $arr = [ 
			'logisticCompanyID'=>'DEPPON',
			'logisticID'=>self::get_sign().$order_num,
			'mailNo'=>$wl_order_num,
			'cancelTime'=>now(),
			'remark'=>"主动撤销", 
	   ];  
	   $res = self::api('cancelOrder',$arr);   
	   return $res;
	}
	/**
	* 取消面单 
	*/
	public static  function close($order_num){ 
	   $res = self::get_one(['order_num'=>$order_num]); 
	   $wl_order_num = $res['wl_order_num'];   
	   $arr = [ 
			'logisticCompanyID'=>'DEPPON',
			'logisticID'=>self::get_sign().$order_num,
			'mailNo'=>$wl_order_num,
			'cancelTime'=>now(),
			'remark'=>"主动撤销", 
	   ];    
	   self::api('cancelOrder',$arr);  
	}
	/**
	* 查询订单
	*/
	public static function query($id_or_row){
		if(is_array($id_or_row)){
			$res = $id_or_row;
		}else {
			$res = self::get_one($id_or_row);    	
		}	 
		$wl_order_num = $res['wl_order_num'];
		sleep(1);
		//无子母单权限的
		$logisticID = self::get_sign().$res['order_num'];
		$arr = [ 
			'logisticCompanyID'=>'DEPPON', 
			'logisticID'=>$logisticID, 
	   ];         
	   $res = self::api('query',$arr,true);    
	   if($res['resultCode'] != '1000'){ 
	   	   //有子母单权限
		   $arr = [ 
				'logisticCompanyID'=>'DEPPON', 
				'logisticID'=>$logisticID."_".$wl_order_num, 
		   ];      
		   $res = self::api('query',$arr,true); 
	   }   
	   if($res['resultCode'] != '1000'){ 
	   	 return json_error(['msg'=>$res['reason']]); 
	   }  
	   $responseParam = $res['responseParam']; 
	   return $responseParam;
	}
	/**
	* 查看物流轨迹
	*/
	public static  function get_wuliu($row){  
	   if($row['status'] < 1 || $row['status'] == 100){
			return $row['wuliu_info'];
	   } 
	   self::get_money($row['wl_order_num']); 
	   $wl_order_num = '';
	   $express_num = $row['express_num'];
	   $flag = false;
	   $wuliu_info = [];
	   foreach($express_num as $_express){ 
	   	   $mail_no = $_express['waybillNo'];
		   $arr = [  
				'mailNo'=>$mail_no, 
		   ];  
		   $res = self::api('trace',$arr);
		   $par = $res['responseParam'];
		   $routes = [];
		   if($par){ 
	   			$trace_list = $par['trace_list'];
	   			$tracking_number = $par['tracking_number']; 
	   			$status_arr = [];
		        $flag = false; 
		        $now_step = "";
	   			foreach($trace_list as $v){ 
		            $route = [
						'acceptAddress'=> $v['status'],
						'acceptTime'   => $v['time'],
						'remark'       => $v['description'],
					];
					//根据物流内容返回状态值 
					$get_step = self::parse_wuliu_str($route['remark']);
		        	if($get_step > $now_step){
		        		$now_step = $get_step;
		        	} 
		        	if($now_step == 100){
		        		$flag = true;
		        	}
		        	$status_arr[] = $now_step;  
					$routes[] = $route;
	   			} 
	   		} 
	   		if($routes){
	   			$wuliu_info[$mail_no] = $routes;
	   		} 
	   }
	   if(!$wuliu_info){
	   	  return;
	   }
	   $data = [];
	   foreach($wuliu_info as $k=>$v){
	   		$data[] = [
	   			'mailNo'=>$k,
	   			'routes'=>$v,
	   		];
	   } 
	   if($flag){
        	foreach($status_arr as $v){
        		if($v != 100){
        			return $data;
        		}
        	} 
        	self::update($row['id'],['wuliu_info'=>$data,'status'=>100]); 
        }else if($now_step && $row['status'] < $now_step){  
    		self::update($row['id'],['wuliu_info'=>$data,'status'=>$now_step]); 
    	} 
	    return $data;  
	}

	public static function get_sign(){
		return self::get_secret();
	}
 	
	public static function create_pdf($id){
		static::$id = $id; 
		$res = self::get_one($id);   
		$wl_order_num = $res['wl_order_num'];
		$y_order_num  = $res['y_order_num']; 
		$num = $res['num']?:1;
		$order_num = $res['order_num']; 
		$new_order_num = express_order_num();
		self::update($res['id'],['order_num'=>$new_order_num]); 
		$customer = $res['customer_address'];
		$fahuo    = $res['fahuo_address'];  
		$res['order_num'] = $new_order_num;     
		$pay_method = $res['pay_method'];
		if($pay_method == 1){
			$pay_method = 2;
		}else{
		    $pay_method = 1;
		}
		$remark = '';
		$custom_key = self::get_custom_key();
		$totalNumber = $res['num'];
		$logisticID = self::get_sign().$new_order_num;
		self::$logisticID = $logisticID;
		//创建订单 
		$arr = [
			'logisticID' => $logisticID,//订单ID
			'custOrderNo'=>$new_order_num,
			'khddh'=>$new_order_num,
			'companyCode'=>self::get_company_key(),//公司编码
			'customerCode'=>$custom_key,//客户编码/月结账号
			//运输方式 
			'transportType'=>$res['express_type_id'],
			//1、散单上门取件模式（单量小，发货地址不固定,需系统通知接货员上门取件,由接货员打单,适用门店调拨，退换货等场景;整车订单也选此模式）； 2、大客户热敏电子面单模式（单量大,发货地址固定,不需要系统通知接货员上门取件,由客户打印热敏面单贴在货上，适用电商等固定仓库批量出货场景）; 3、快递筛单下单模式（单量大,发货地址固定,由客户打印热敏面单贴在货上,只支持快递产品,不可达区域会直接下单失败）
			'orderType'=>2,
			'remark'=>$remark,
			'receiver'=>[ 
				"address"=>$customer['address'],
				"city"=>$customer['city'],
				"company"=>$customer['company'],
				"name"=>$customer['contact'], 
				"county"=>$customer['county'],
				"mobile"=>$customer['mobile'],
				"province"=>$customer['province'], 
			],
			'sender'=>[ 
				"address"=>$fahuo['address'],
				"city"=>$fahuo['city'], 
				"name"=>$fahuo['contact'], 
				"county"=>$fahuo['county'],
				"mobile"=>$fahuo['mobile'],
				"province"=>$fahuo['province'], 
			],
			'packageInfo'=>[
				'cargoName'  =>$res['name'],
				'totalNumber'=>$totalNumber,
				'totalWeight'=>0,
				//1、自提； 2、送货进仓； 3、送货（不含上楼）； 4、送货上楼； 5、大件上楼 ;7、送货安装；8、送货入户
				//此处必须是 3或者4否则无法取消
				'deliveryType'=>4,
			],
			'gmtCommit'=>now(),
			//0、发货人付款（现付）（大客户模式不支持寄付） 1、收货人付款（到付） 2、发货人付款（月结）
			'payType'=>$pay_method,
			//是否需要自动订阅轨迹 1：是（为是时要对接轨迹推送接口） 2：否 默认否
			'needTraceInfo'=>1,
		]; 
		//预埋德邦单号场景使用；子母件场景多个运单号使用英文【,】拼接
		$y_order_num = $res['y_order_num'];
		if($y_order_num){
			$arr['mailNo'] = $y_order_num;
		}  
		//散单处理
		if(!$custom_key){
			$arr['orderType'] = 1;
			unset($arr['customerCode']);
		}  
		if($res['pdf_url']){
			$res = self::api('updateOrder',$arr); 
		}else{
			$res = self::api('addOrder',$arr); 
			//比如：DPK360000000000,DPK380000000001,DPK380000000002,生产环境一般DPK36开头为母单号且排在第一位，零担一个订单无论多少件仅会返回一个运单号
			$no = $res['mailNo'];
			//仅快递电子面单且为子母件场景下才有值 
			$main_no = $res['parentMailNo'];
			$express_num = [];
			$wuliu_info = [];
			if($main_no){
				$arr = explode(",",$no); 
				$i = array_search($main_no,$arr); 
				unset($arr[$i]);
				$arr = array_values($arr); 
				array_unshift($arr,$main_no); 
				$wl_order_num = $main_no;
				foreach($arr as $v){
					$express_num[] = ['waybillNo'=>$v];
					$wuliu_info[]  = ['mailNo'=>$v];
				}
				$sub_num = $num - 1;
			} else {
				$wl_order_num = $no;
				$express_num = [
					[
						'waybillNo'=>$wl_order_num, 
					]
				];
				$wuliu_info = [
					[ 
						'mailNo'=>$wl_order_num
					]
				];
			} 
			$up = [
				'wl_order_num'=> $wl_order_num, 
				'express_num' => $express_num,
				'wuliu_info'  => $wuliu_info,
			];   
			if($sub_num > 0 ){
				$up['sub_num'] = $sub_num;
			}
			self::update(self::$id,$up); 
		}  
		self::_miandan($id,$wl_order_num,$res);
		return ;
	}

	/**
	* 创建面单
	*/
	public static function _miandan($order_id,$wl_order_num,$order_info){   
		$res = self::get_one($order_id);
		$order_num = $res['order_num'];
		$express_num = $res['express_num'];  
		$pdf_url = '/uploads/db_pdf/'.$wl_order_num.'-'.mt_rand(0,1000).'.pdf';
		$save_path = PATH.$pdf_url;
		$dir  = get_dir($save_path);
		create_dir_if_not_exists([$dir]); 
		$par = self::query($res); 
		$tpl  = new Db; 
		//收 图片的URL地址
		$tpl->revice_img_url = self::$revice_img_url;
		$tpl->sender_img_url = self::$sender_img_url;
		//底部左侧二维码的URL地址
		$tpl->qr_url         =  self::get_qr_url('db'); ; 
		//签收单: 原件返因<br>返单要求:签名、蓝章、身份证号、身份证复印件、仓库收货因执单
		$send_type = [
			0=>'自提',
			1=>'送货（不含上楼）',
			2=>'机场自提',
			3=>'送货上楼',
		];
		$desc1 = "包装:".$par['packageService']."<br>送货方式:".($send_type[$par['deliveryType']]?:$par['deliveryType']);
		//保价金额: 0.00<br>用户单号:96516454145

		$order_num_str = substr($order_num,0,18)."<br>".substr($order_num,18); 
		$desc2 = "保价金额:".$par['insuranceValue']."<br>用户单号:".$order_num_str;  
		//自送三方安装
		$tip1 = "德邦快递";
		//'代收:1200.00<br>到付: 20.00'
		$tip2 = "";
		$pay_type_arr = [
			0=>'现付',
			'CH'=>'现付',
			1=>'到付',
			'FC'=>'到付',
			2=>'月结',
			'CT'=>'月结', 
		]; 
		$tip2 .= $pay_type_arr[$par['payType']];
		$bag_addr_1 = "";
		$bag_addr_2 = "";  
		$info = [
		    'time'=>now(),
		    'bill_code'=>$wl_order_num,//运单号
		    //子单
		    //'sub_bill_code'=>312136713707566,//子单号，首次与bill_code值相同
		    //'sub_title'=>'1/2',//子单/总数
		    // 
		    'title'=>$par['packageService'],    
		    'mark'=> $order_info['arrivedOrgSimpleName'],//大头笔
		    'bag_addr_1'=>$bag_addr_1, //集包地
		    'bag_addr_2'=>$bag_addr_2,
		    'name'=>$par['cargoName'], //品名内容
		    'desc'=>$par['remark'], //备注
		    'type'=>$par['payType'],//
		    //收货人
		    'receiver'=>[
		        'name'=>$par['receiver']['name'],
		        'phone'=>$par['receiver']['mobile'],
		        'address'=>$par['receiver']['province'].$par['receiver']['country'].$par['receiver']['address'],
		    ],
		    'sender'=>[
		        'name'=>$par['sender']['name'],
		        'phone'=>$par['sender']['mobile'],
		        'address'=>$par['sender']['province'].$par['sender']['country'].$par['sender']['address'],
		    ],
		    'save_path'=> $save_path,
		    //'return_content'=>true, 
		    'to'=>$par['sender']['province'].$par['sender']['country'],
		    'notice'=>$par['packageService'],
		    'desc1'=>$desc1,
		    'desc2'=>$desc2,
		    'tip1'=>$tip1,
		    'tip2'=>$tip2,
		];  
		if($express_num && count($express_num) > 1){
			$total = count($express_num);
			$i = 1;
			$up = [];
			$merger_pdf = [];
			foreach($express_num as $k=>$v){
				$pdf_url = '/uploads/db_pdf/'.$wl_order_num."-".$v['waybillNo'].mt_rand(0,99999).'.pdf';
				$save_path = PATH.$pdf_url;
				if($wl_order_num == $v['waybillNo']){
					$up['pdf_url'] = $pdf_url;
				}
				$merger_pdf[] = $save_path;
				$info['sub_bill_code'] = $v['waybillNo'];
				$info['sub_title'] = $i.'/'.$total;
				$info['save_path'] = $save_path; 
				$tpl->output($info); 
				$express_num[$k]['pdf_url'] = $pdf_url;
				$i++;
			}
			$up['express_num'] = $express_num;
			if(count($merger_pdf) > 1){ 
	            $f_pdf_url = '/uploads/db_pdf/'.$wl_order_num.'-merger-'.mt_rand(0,1000).'.pdf';
	    		$save_path = PATH.$f_pdf_url; 
	            Pdf::merger($merger_pdf,$save_path);
	            $up['pdf_url'] = $f_pdf_url;
		    }
			self::update($order_id,$up);
		}else{ 
			$tpl->output($info); 
			$express_num[0]['pdf_url'] = $pdf_url;
			$up = [
				'pdf_url'=>$pdf_url,
				'express_num'=>$express_num,
			];
			self::update($order_id,$up); 
		}
		
	}
	/**
	* 线上正式
	*/
	public static $urls_pro = [    
		'addOrder'=>'http://gwapi.deppon.com/dop-interface-async/standard-order/createOrderNotify.action',
		'updateOrder'=>'http://gwapi.deppon.com/dop-interface-async/standard-order/updateOrder.action',
		'query'=>'http://dpapi.deppon.com/dop-interface-sync/standard-query/queryOrder.action',
		'cancelOrder'=>'http://gwapi.deppon.com/dop-interface-async/standard-order/cancelOrder.action',
		'trace'=>'http://dpapi.deppon.com/dop-interface-sync/standard-query/newTraceQuery.action', 
	];

	public static $urls = [    
		'addOrder'=>'/dop-standard-ewborder/createOrderNotify.action',
		'query'=>'/standard-order/queryOrder.action',
		'updateOrder'=>'/standard-order/updateOrder.action',
		'cancelOrder'=>'/standard-order/cancelOrder.action', 
		'trace'=>'/standard-order/newTraceQuery.action',  
	];

	///////////////////////////////////////////////////////////////
	// 以下代码不要修改
	/////////////////////////////////////////////////////////////// 

	public static function get_custom_key(){
		if(self::is_sandbox()){
			return '219402';
		}
	    return get_config('express_db_customer_key'); 
	} 

	public static function get_custom_secret(){ 
	  return get_config('express_db_customer_secret'); 
	}
	
	public static function is_sandbox(){
		if(get_config('express_db_status') == 1){
			return true;
		}
	}

	public static function get_key(){
	  if(self::is_sandbox()){
	    $key = 'express_db_sandbox_key';
	  }else{
	    $key = 'express_db_key';
	  }
	  return get_config($key); 
	} 

	public static function get_secret(){
	  if(self::is_sandbox()){
	    $key = 'express_db_sandbox_secret';
	  }else{
	    $key = 'express_db_secret';
	  } 
	  return get_config($key); 
	}

 
	//沙箱环境的地址
	public static  $CALL_URL_BOX = "http://dpsanbox.deppon.com/sandbox-web"; 
	//生产环境的地址
	//public static $CALL_URL_PROD = "http://gwapi.deppon.com";  

	public static function delete_data($s){
		if($s == 'addOrder'){
			if(self::$id){
				self::delete(self::$id);
			}
		}
	}

	public static function get_company_key(){
		if(self::is_sandbox()){
			return get_config('express_db_sandbox_company_key');
		}else {
			return get_config('express_db_company_key');
		}
	}

	public static function get_digest($parmas,$timestamp = ''){
		$timestamp = $timestamp?:microtime();
		list($t1, $t2) = explode(' ',$timestamp);
		$timestamp = (float)sprintf('%.0f',(floatval($t1) + floatval($t2)) * 1000);
		if(is_array($parmas)){
			$parmas = json_encode($parmas,JSON_UNESCAPED_UNICODE);	
		}	 
		$appkey = self::get_key();  
		$appkey = trim($appkey); 
		$res['digest'] = base64_encode(md5($parmas . $appkey . $timestamp)); 
		$res['timestamp'] = $timestamp;
		return $res;
	}



	public static function api($code,$parmas,$ignore_error = false){  
		//客户编码 
		$customer_key = self::get_custom_key();  
		//公司编码 
		$compay_code = self::get_company_key();   
		//sign值由德邦开放平台自动生成
		$sign   = self::get_secret(); 
		$parmas = json_encode($parmas,JSON_UNESCAPED_UNICODE);  
		$res = self::get_digest($parmas);
		$digest = $res['digest'];
		$timestamp = $res['timestamp'];
		$data = array (
		    'companyCode'=> $compay_code,
		    'params'=> $parmas,
		    'digest'=> $digest,
		    'timestamp'=> $timestamp
		);
		$url = self::$urls[$code]; 
		if(self::is_sandbox()){
			$host = self::$CALL_URL_BOX;
			$url = $host.$url; 
		}else { 
			$url  = self::$urls_pro[$code];
		} 
		if(self::$debug){
			echo "<hr> 请求 <hr>";
			pr($url);
			pr($data);
		}  
		$client  = guzzle_http();
    	$res     = $client->request('POST', $url,['form_params'=>$data]); 
    	$res     = (string)$res->getBody(); 
    	$res     = json_decode($res,true); 
    	if(self::$debug){
    		echo "<hr> >>> 结果 <hr>";
    		pr($res);
    	}
    	 
    	if($res['resultCode'] == '1000'){
    		return $res; 
    	}else {
    		self::delete_data($code);
    	}
    	if(!$ignore_error){
    		return json_error(['msg'=>$res['reason']." ".$code]);
    	}

    	return $res;
	}
}