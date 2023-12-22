<?php 
namespace app\express\lib;
use helper_v3\Pdf;
class ExpressJt extends Base{ 
	public static $title = '极兔';
	public static $support_add_sub = false;
	public static $support_is_sign_back = false;
	public static $id;
	/* 
	* 付款方式
	*/
	public static function get_pay_method(){   
	  return [
	    'PP_PM'=>'寄付月结',
	    'CC_CASH'=>'到付现结', 
	  ];
	} 
	/**
	* 产品类别 
	*/
	public static function get_express_type(){
	  return [
	  	'EZ'=>'标准快递', 
	  ];
	} 

	public static function is_sandbox(){
	   $sandbox    = self::get_config('express_jt_status');
	   if($sandbox == 1){
	    	return true;
	   }else {
	   	    return false;
	   }
	}

	public static $urls = [
	   	'getLocation'=>'/webopenplatformapi/api/location/getLocation',
	   	// 散客寄件 addLooseOrder
	   	// 月结寄件 addOrder
	   	'addOrder'=>'/webopenplatformapi/api/order/v2/addOrder',
	   	'cancelOrder'=>'/webopenplatformapi/api/order/cancelOrder',
	   	'trace'=>'/webopenplatformapi/api/logistics/trace',
	   	'miandan'=>'/webopenplatformapi/api/order/printOrder',
	   	'miandan_money'=>'/webopenplatformapi/api/ess/balance',
	   	//运费，不是按运单号的，有问题 https://open.jtexpress.com.cn/#/apiDoc/other/freight
	   	'get_money'=>'/webopenplatformapi/api/spmComCost/getComCost',
    ];

 	public static function delete($s){
		if($s == 'addOrder' || $s == 'miandan'){
			if(self::$id){
				db_del("express_order",['id'=>self::$id]);
			}
		}
	}
	/**
	* 创建面单
	*/
	public static function create_pdf($id){ 
		self::$id = $id;
		$res = self::get_one($id);   
		$num = $res['num']?:1;
		$order_num = $res['order_num']; 
		$new_order_num = $order_num; 
		self::update($res['id'],['order_num'=>$new_order_num]);
		$customer = $res['customer_address'];
		$fahuo    = $res['fahuo_address'];
		//创建订单
		$countryCode = "CHN";
		$goodsType = $res['name'];
		//保价金额(数值型)，单位：元
		$offerFee = 0; 
		$orderType = self::get_customer_code();
		$remark = '';
		$arr = [
			//保价金额(数值型)，单位：元
			"offerFee"=>$offerFee,
			//重量，单位kg，范围0.01-30
			"weight"=>0.02,
			//客户订单号（传客户自己系统的订单号）
			"txlogisticId"=>$new_order_num,
			//合作网点编码（没有则不传）
			"network"=>"",
			//快件类型：EZ(标准快递)
			"expressType"=>$res['express_type_id'],
			//订单类型（有客户编号为月结）1、 散客；2、月结；
			"orderType"=>$orderType,
			//服务类型 ：02 门店寄件 ； 01 上门取件 
			"serviceType"=>"02",
			//物流公司上门取货开始时间 yyyy-MM-dd HH:mm:ss
			"sendStartTime"=>"",
			//客户物流公司上门取货结束时间 yyyy-MM-dd HH:mm:ss
			"sendEndTime"=>"",
			"goodsType"=>"bm000006",
			//派送类型： 06 代收点自提 05 快递柜自提 04 站点自提 03 派送上门
			"deliveryType"=>"03",
			//支付方式：PP_PM("寄付月结"), CC_CASH("到付现结");
			"payType"=>$res['pay_method'],
			"sender"=>[
				"address"=>$fahuo['address'],
				"city"=>$fahuo['city'],
				"company"=>$fahuo['company'],
				"name"=>$fahuo['contact'], 
				"area"=>$fahuo['county'],
				"phone"=>$fahuo['mobile'],
				"prov"=>$fahuo['province'], 
				"countryCode"=>$countryCode,
			],
			"receiver"=>[
				"address"=>$customer['address'],
				"city"=>$customer['city'],
				"company"=>$customer['company'],
				"name"=>$customer['contact'], 
				"area"=>$customer['county'],
				"phone"=>$customer['mobile'],
				"prov"=>$customer['province'],
				"countryCode"=>$countryCode,
			], 
			'remark'=>$remark,
		];  
		$res = self::api('addOrder',$arr); 
		$wl_order_num = $res['billCode']; 
		if(!$wl_order_num) {
			echo json_error(['msg'=>$d['msg']]);exit;
		}    
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
		$res['customer_code'] = self::get_customer_code();
		$up = [
			'wl_order_num'=> $wl_order_num,
			'api_data'    => $res,
			'express_num' => $express_num,
			'wuliu_info'  => $wuliu_info,
		];   
		self::update($id,$up); 
		$sf_number = $wl_order_num; 
		self::_miandan($id); 
	}
 

	/**
	* 查看面单时触发派件通知 
	*/
	public static  function notice($sf_number){
	   
	}
	/**
	* 查看物流轨迹  
	*/
	public static function get_wuliu($row){
		if($row['status'] < 1 || $row['status'] == 100){
			return $row['wuliu_info'];
		}
		$express_num = $row['express_num'];
		$order_num = [];
		foreach($express_num as $v){
			$order_num[] = $v['waybillNo']; 
		}  
		if(!is_array($order_num)){
		$order_num = [$order_num];
		}
		$order_num_str = '';
		foreach($order_num as $v){
			$order_num_str.=$v.',';
		}
		$order_num_str = substr($order_num_str,0,-1);
		if(self::is_sandbox()){
			//$order_num_str = 'UT0000346840348';
		}
		$arr = [ 
			"billCodes"=>$order_num_str, 
		];
		$trace = self::api('trace',$arr); 
		if($trace && is_array($trace)){ 
				$is_update = false;
				$data = [];
				$routes = [];
				$status_arr = [];
		        $flag = false; 
			    foreach ($trace as $v){
			    	$billCode = $v['billCode']; 
			    	$details = $v['details'];
			    	$route = [];
			    	$now_step = "";
			    	foreach($details as $v){
			    		$scanType = $d['scanType'];
				    	$vv = [
				    		'opCode'=>$opCode,
				    		'remark'=>$d['desc'],
				    		'acceptTime'=>$d['scanTime'],
				    		'acceptAddress'=>$d['scanNetworkName'],
				    	]; 
				    	$route[] = $vv;
				    	$get_step = self::parse_wuliu_str($vv['remark']);
			        	if($get_step > $now_step){
			        		$now_step = $get_step;
			        	} 
			        	if($now_step == 100){
			        		$flag = true;
			        	} 
				    	$routes[] = array_reverse($route); 
			    	} 
			    	$status_arr[] = $now_step; 
			    	$data[] = [
			    		'mailNo'=>$billCode,
			    		'routes'=>$routes,
			    	];    
			    }  
			    if($flag){
		        	foreach($status_arr as $v){
		        		if($v != 100){
		        			return $data;
		        		}
		        	}
		        	self::update($row['id'],['status'=>100]); 
		        }else if($now_step && $row['status'] < $now_step){ 
	        		self::update($row['id'],['wuliu_info'=>$data,'status'=>$now_step]); 
	        	}
		}
		return $data;
	}
	/**
	* 订单运费查寻
	* 
	* 清单运费查询接口-速运类API 
	*/
	public static function get_money($trackingNum,$phone = '',$row = []){  
	   
	}
	/**
	* 取消面单
	* 订单确认/取消接口-速运类API 
	*/
	public static  function close($order_num){
	   
	}
	/**
	* 极兔面单是收费的，需要联系线下网点充值
	* https://www.jtexpress.cn/serviceWebsite.html
	*/
	public static function get_miandan_money(){  
	  return  self::api('miandan_money',[])['balance'];  
	}
	/**
	* 创建面单
	*/
	public static function _miandan($order_id){
	  $money = self::get_miandan_money();
	  if($money <= 0){
	  	 //throw new Exception("面单余额不足，无法获取生成面单");	  	 
	  }
	  $res = self::get_one($order_id);
	  $order_res     = $res; 
	  $customerCode  = $res['api_data']['customer_code']; 
	  $wl_order_num  = $res['wl_order_num'];
	  $express_num   = $res['express_num']; 
	  $documents = [
	      ["masterWaybillNo"=>$wl_order_num]
	  ];  
	  $arr = [
	  	'billCode'=>$wl_order_num,
	  	'customerCode'=>$customerCode,
	  	'isPrivacyFlag'=>false,
	  ];  
	  $res = self::api('miandan',$arr); 
	  if($res['base64EncodeContent']){
	  	$content = $res['base64EncodeContent'];  
        $pdf_url = '/uploads/sf_pdf/'.$wl_order_num.'.pdf';
		$pdf_file = PATH.$pdf_url;
		$dir  = get_dir($pdf_file); 
		create_dir_if_not_exists([$dir]);   
        file_put_contents($pdf_file,$content); 
        foreach($express_num as $vv){
        	$v['pdf_url'] = $pdf_url;
        }
	    self::update($order_id,[
	    	'pdf_url'=>$pdf_url,
	    	'express_num'=>$express_num
	    ]); 
	  }  
	} 

	
	///////////////////////////////////////////////////////////////
	// 以下代码不要修改
	/////////////////////////////////////////////////////////////// 
	public static function get_config($key){
		return get_config($key);
	}
	//沙箱环境的地址
	public static  $CALL_URL_BOX = "https://uat-openapi.jtexpress.com.cn";
	//生产环境的地址
	public static $CALL_URL_PROD = "https://openapi.jtexpress.com.cn"; 

	//POST
	public static  function send_post($url, $post_data) {   
		$post_data  = self::get_data($post_data); 
		$apiAccount = self::get_key();    
    	$content = http_build_query(['bizContent' => $post_data]); 
	    $bizContent = json_encode($post_data,JSON_UNESCAPED_UNICODE);
	    if(self::is_sandbox()){
	    	file_put_contents(PATH.'/data/jt.txt',$bizContent."\n",FILE_APPEND);
	    } 
	    $options = array( 
	        'http'   => array(
	            'method' => 'POST', 
	            'header' => array(
	            	'Content-type: application/x-www-form-urlencoded',
                    'apiAccount:' . $apiAccount,
                    'digest:' . self::get_header_sign($post_data),
                    'timestamp: '.time()
                ),
	            'content' => $content,
	            'timeout' => 15 * 60 // 超时时间（单位:s）
	        )
	    );   
	    $context = stream_context_create($options);
	    $result = file_get_contents($url, false, $context);  
	    return $result;
	}

	public static function delete_data($s){ 
		if(self::$id){
			self::delete(self::$id);
		} 
	}
	
	public static  function api($serviceCode,$data,$ignore_err = false){  
	  $res = self::run($serviceCode,$data);
	  if(!$ignore_err && $res['code'] != 1 ){
	  	 if($res['msg']){
	  	 	self::delete_data($serviceCode);
		    json_error(['msg'=>$res['msg'].$serviceCode]);
		  }
	  } 
	  return $res['data'];
	}  

	public static function run($serviceCode,$data)
	{ 
		$url       = self::$urls[$serviceCode];
		$status    = self::get_config('express_jt_status');
		if($status == 1){
			$url = self::$CALL_URL_BOX.$url."?uuid=53d3989d83954d52bf84fcf9f9693068";
		}else{
			$url = self::$CALL_URL_PROD.$url;
		}   
		$resultCont = self::send_post($url, $data); 
		$res = json_decode($resultCont,true);  
		return $res;
	} 

	public static function get_key(){
	  if(self::get_config('express_jt_status') == 1){
	    $key = 'express_jt_sandbox_key';
	  }else{
	    $key = 'express_jt_key';
	  }
	  return self::get_config($key); 
	}

	public static function get_customer_code(){
	  if(self::get_config('express_jt_status') == 1){
	    $key = 'express_jt_sandbox_customer_code';
	  }else{
	    $key = 'express_jt_customer_code';
	  }
	  return self::get_config($key); 
	}

	public static function get_pwd(){
	  if(self::get_config('express_jt_status') == 1){
	    $key = 'express_jt_sandbox_pwd';
	  }else{
	    $key = 'express_jt_pwd';
	  }
	  return self::get_config($key); 
	}

	public static function get_private_key(){
	  if(self::get_config('express_jt_status') == 1){
	    $key = 'express_jt_sandbox_private_key';
	  }else{
	    $key = 'express_jt_private_key';
	  }
	  return self::get_config($key); 
	}

	public static  function get_data($data){  
		$data = array_filter($data);
		$data['countryCode'] = 'CHN';
	    $data['customerCode'] = self::get_customer_code(); 
	    $data['digest']       = self::get_content_sign();   
	    return json_encode($data);
	}
 
    public static function get_content_sign(){ 
    	$str = strtoupper(self::get_customer_code() . md5(self::get_pwd() . 'jadada236t2')) . self::get_private_key(); 
    	return base64_encode(pack('H*', strtoupper(md5($str)))); 
    } 

    public static function get_header_sign($post){
    	$privatekey = self::get_private_key();
    	return base64_encode(pack('H*',strtoupper(md5($post.$privatekey)))); 
    }

}
