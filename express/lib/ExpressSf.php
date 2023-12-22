<?php 
namespace app\express\lib;
use helper_v3\Pdf;
class ExpressSf extends Base{ 
	public static $title = '顺丰';
	//支持子单
	public static $support_add_sub = true;
	public static $id;
	public static $need_sub_trace = false;
	public static $support_is_sign_back = true;
	public static $debug = false;
	public static $meger_pdf = true;
	/* 
	* 付款方式
	*/
	public static function get_pay_method(){ 
	  return [
		    1=>'寄方付',
		    2=>'收方付', 
	  ];
	} 
	/**
	* 产品类别 
	*/
	public static function get_express_type(){
	  return [
	  		2=>'顺丰标快',
		    1=>'顺丰特快', 
		    6=>'顺丰即日',  
	  ];
	} 
	 
	/**
	* 追加子订单
	* 未发出时可用
	* 单次最大增加20个
	*/
	public static function sub($order_num,$sub_num,$order_info){ 
		$id = $order_info['id'];
		$arr['orderId']   = $order_num;
		$arr['parcelQty'] = $sub_num;  
		$res = self::api('EXP_RECE_GET_SUB_MAILNO',$arr);    
		$express_num = $res['apiResultData']['msgData']['waybillNoInfoList'];  
		if($id && $express_num && is_array($express_num)){
			$ori_express_num = $order_info['express_num']; 
			$in = [];
			$back = []; 
			foreach($ori_express_num as $k=>$v){
				if($v['waybillType'] == 3){
					$back = $v;
					unset($ori_express_num[$k]);
				}
				$in[$v['waybillNo']] = $k;
			}  
			foreach($express_num as $v){
				$waybillNo = $v['waybillNo'];
				if(!isset($in[$waybillNo])){  
					$new_list = [
						'waybillNo'=>$waybillNo,
						'waybillType'=>$v['waybillType'],
						'step'=>2
					];   
					$ori_express_num[] = $new_list;
				}
			}  
			if($back){
				$ori_express_num[] = $back;
			}  
			self::update($id,['express_num'=>$ori_express_num]);			
			//要重新生成面单
			self::_miandan($id);   
		}  
	}
	public static function add_server($order_info,&$arr){
		//拍照回传 
		/*
		value 图片类型 value1 照片张数 value2 可多个，英文逗号分隔， 1=签名，2=盖章，3=签名+盖章，4=身份证号码，5=签名+身份证号码，6=盖章+身份证号码，7=签名+盖章+身份证号码，8、【签收日期】9、【电话号码】  value5:"{\"remark\":\"备注信息\"}"
		*/  
		if($order_info['photo_back']){
			$arr['serviceList'][] = ['name'=>'IN91','value'=>1,'value1'=>1,'value2'=>3];	
		} 
		//电子回单
		if($order_info['ele_back']){
			$arr['serviceList'][] = ['name'=>'IN149','value'=>1];
		} 
	}
	/**
	* 创建面单
	*/
	public static function create_pdf($id){ 
		static::$id = $id; 
		$res = self::get_one($id); 
		$wl_order_num = $res['wl_order_num'];
		$y_order_num  = $res['y_order_num']; 
		$num = $res['num']?:1;
		$order_num = $res['order_num'];
		self::close($order_num);
		$new_order_num = express_order_num();
		self::update($res['id'],['order_num'=>$new_order_num]); 
		$customer = $res['customer_address'];
		$fahuo    = $res['fahuo_address'];
		//创建订单
		$arr = [
			'cargoDetails'=>[
				'amount'=>$res['amount'],
				'count'=>$res['count'],
				'name'=>$res['name'],
				'unit'=>$res['unit'],
				'volume'=>$res['volume'],
				'weight'=>$res['weight'],
			],
			'contactInfoList'=>[
				[
					"contactType"=>2,
					"address"=>$customer['address'],
					"city"=>$customer['city'],
					"company"=>$customer['company'],
					"contact"=>$customer['contact'], 
					"county"=>$customer['county'],
					"mobile"=>$customer['mobile'],
					"province"=>$customer['province'],
					"company"=>$customer['com_title'],
				],
				[ 
					"contactType"=>1,
					"address"=>$fahuo['address'],
					"city"=>$fahuo['city'],
					"company"=>$fahuo['company'],
					"contact"=>$fahuo['contact'], 
					"county"=>$fahuo['county'],
					"mobile"=>$fahuo['mobile'],
					"province"=>$fahuo['province'], 
				]
			],
			"customsInfo"=> [],
			"expressTypeId"=> $res['express_type_id']?:1,
			"extraInfoList"=> [],
			"isOneselfPickup"=> 0,
			"language"=> "zh-CN",
			"monthlyCard"=> $res['monthly']?:'',
			'cargoDesc'=>$res['desc'],
			"orderId"=> $new_order_num,
			"parcelQty"=> $res['parcel_qty']?:1,
			"payMethod"=> $res['pay_method'],
			"totalWeight"=> $res['total_weight'],
			//"isDocall"=>1,
		]; 
		$payment_type = self::get_pay_method()[$res['pay_method']];
		$a = " 寄托物：".$res['name'];
		$remark = $_POST["remark"];
		if($remark){
			$remark = addslashes($remark);
		}
		$remark = $remark.$payment_type;
		$arr['remark'] = $remark;
		if($y_order_num){
			$arr['waybillNoInfoList'] = ['waybillType'=>1,'waybillNo'=>$y_order_num];
			$arr['isGenWaybillNo'] = 0;
		} 
		//是否返回签回单 （签单返还）的运单号， 支持以下值： 1：要求 0：不要求
		if($res['is_sign_back'] == 1){
			$signbackNum = $res['num'];
			$arr['isSignBack'] = 1;
			$arr['extraInfoList'] = [
				//1:签名，2:盖章，3:登记身份证号，4:收取身份证复印件，英文逗号分隔
				['attrName'=>'attr015','attrVal'=>'1'],
				['attrName'=>'attr014','attrVal'=>'singBackInfo'],
				['attrName'=>'signbackNum','attrVal'=>$signbackNum],
			]; 
		} 
		if(self::$debug){
			echo "下单参数";
			pr($arr);
		}
		$res = self::api('EXP_RECE_CREATE_ORDER',$arr);   
		$d   = $res['apiResultData'];
		if(self::$debug){
			echo "下单返回";
			pr($d);
		}
		if($d['errorMsg']) {
			echo json_error(['msg'=>$d['errorMsg']]);exit;
		} 
		$res = self::get_query($new_order_num); 
		$d   = $res['apiResultData'];
		if($d['errorMsg']) {
			echo json_error(['msg'=>$d['errorMsg']]);exit;
		} 
		$api_data = $d['msgData'];
		$list = $api_data['waybillNoInfoList']; 
		if($list && is_array($list)){ 
			$wl_order_num = $list[0]['waybillNo']; 
			$new_list = [];
			foreach($list as $v){
				$text = '';
				if($v['waybillType'] == 3){
					$text = '签回单';
				}
				$new_list[] = [
					'waybillNo'=>$v['waybillNo'],
					'waybillType'=>$v['waybillType'],
					'text'=>$text,
					'step'=>1
				]; 
			}
			if($wl_order_num){ 
				$up = [
					'wl_order_num'=>$wl_order_num, 
					'express_num'=>$new_list,
				];  
				self::update($id,$up); 
			}
			$sf_number = $wl_order_num; 
			$order_info = self::_miandan($id); 
			$pdf_url =  $res['pdf_url'];
			if($num > 1){
				$sub_num = $num - 1;  
				self::sub($new_order_num,$sub_num,$order_info);
			} 
			return $pdf_url;
		}   
	}

	/**
	* 取面单信息
	* 订单结果查询接口-速运类API
	*/
	public static function get_query($order_num){
	   $msgData = [
	   	  //查询类型：1正向单 2退货单
	      "searchType"=>"1",
	      "orderId"=>$order_num,
	      "language"=>"zh-cn"
	   ];
	   return self::api('EXP_RECE_SEARCH_ORDER_RESP',$msgData);
	}
 
	/**
	* 查看物流轨迹 
	mailNo SF7444472241781 
	routes:{acceptAddress acceptTime remark}  
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
		$phone_no = substr($row['customer_address']['mobile'],-4); 
		$count = count($order_num);
		if($count > 0){
		    $s = '';
		    for($i=0;$i<$count;$i++){
		        $s .= $phone_no.",";
		    }
		    $phone_no = substr($s,0,-1);
		}
		
		$arr = [
			"language"=>"0",
			//1:根据顺丰运单号查询,trackingNumber将被当作顺丰运单号处理
			//2:根据客户订单号查询,trackingNumber将被当作客户订单号处理
			"trackingType"=>"1",
			"trackingNumber"=>$order_num,
			"methodType"=>"1",
			"checkPhoneNo"=>$phone_no
		]; 
		$res = self::api('EXP_RECE_SEARCH_ROUTES',$arr,true); 
		$data = $res['apiResultData']['msgData']['routeResps']; 
		if($data && is_array($data)){ 
			$is_update = false;
		    foreach ($data as &$v){
		    	$routes = $v['routes'];
		        $v['routes'] = array_reverse($routes); 
		        $status_arr = [];
		        $flag = false; 
		        $now_step = "";
		        foreach($routes as $vv){ 
		        	$opCode = $vv['opCode'];
		        	$get_step = self::parse_wuliu_str($vv['remark']);
		        	if($get_step > $now_step){
		        		$now_step = $get_step;
		        	} 
		        	if($now_step == 100){
		        		$flag = true;
		        	}
		        }
		        $status_arr[] = $now_step; 
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
	*/
	public static function get_money($trackingNum,$phone = '',$row = []){ 
	  $phone = $phone?:$row['customer_address']['mobile'];
	  if(is_array($trackingNum)){
	    $count = count($trackingNum);
    	if($count > 0){
    		$s = '';
    		for($i=0;$i<$count;$i++){
    		   $s .= $phone.",";
    		}
    		$phone = substr($s,0,-1);
    	}
	  } 
	  $arr = [ 
	      "trackingType"=>2,
	      "trackingNum"=> $trackingNum,
	      "phone"=>$phone,
	  ];  
	  $res = self::api('EXP_RECE_QUERY_SFWAYBILL',$arr,true); 
	  $types = [
		  	1=>'运费',
		  	3=>'保价',
	  ];
	  if($res['apiResultData']['errorCode']){
	  	 $d     =  $res['apiResultData']['msgData'];
	  	 $money = $d['waybillFeeList'];
	  	 $amount = 0;
	  	 $amount_list = [];
	  	 foreach($money as $v){
	  	 	$amount = bcadd($amount,$v['value'],2);
	  	 	$k = $types[$v['type']];
	  	 	if($v['value'] > 0){
	  	 		$amount_list[$k] = bcmul(($v['value']?:0),1,2);	
	  	 	}	  	 	
	  	 }
	  	 if($amount && $row){
	  	 	if($row['amount'] != $amount){
	  	 		self::update($row['id'],[
	  	 			'amount'     => $amount,
	  	 			'amount_list'=> $amount_list,
	  	 		]);  
	  	 	}
	  	 } 
	  } 
	  return ['msg'=>$res['apiResultData']['errorMsg'],'arr'=>$arr]; 
	}
	/**
	* 取消面单 
	*/
	public static  function close($order_num){
	  $arr = [
	  	  //客户订单操作标识: 1:确认 (丰桥下订单接口默认自动确认，不需客户重复确认，该操作用在其它非自动确认的场景) 2:取消
	      "dealType"=>2,
	      "orderId"=> $order_num
	  ];
	  $res = self::api('EXP_RECE_UPDATE_ORDER',$arr,true);
	  if($res['apiResultData']['errorCode']){

	  } 
	  return ['msg'=>$res['apiResultData']['errorMsg']]; 
	}

	/**
	* 创建面单
	*/
	public static function _miandan($order_id){ 
	  if(is_local()){
	  	self::$meger_pdf = false;
	  }
	  $order_info = self::get_one($order_id);
	  $order_res     = $order_info; 
	  $clientcode    = $order_info['api_data']['clientCode']; 
	  $sf_number     = $order_info['wl_order_num'];
	  $express_num   = $order_info['express_num'];
	  $new_express_num = $express_num;
	  $documents = [
	      ["masterWaybillNo"=>$sf_number]
	  ]; 
	  if($new_express_num){
	  	$documents = [];
	  	$masterWaybillNo = '';
	  	$seq = 1; 
	  	$is_back = false; 
	  	$back = [];
	  	foreach($new_express_num as $k=>$v){
	  		if($v['waybillType'] == 3){
	  			$is_back = true;
	  			$back = ['backWaybillNo'=>$v['waybillNo']]; 	
	  			unset($new_express_num[$k]);
	  		} 		
	  	}
	  	$sum = count($new_express_num);
	  	$masterWaybillNo = $sf_number; 
	  	foreach($new_express_num as $v){ 
	  		$d = [
	  			"masterWaybillNo"=>$masterWaybillNo,  
	  		];  
	  		if($sum > 1){
	  			$d["seq"] = $seq;
	  			$d["sum"] = $sum;
	  		}
	  		$d['branchWaybillNo'] = $v['waybillNo']; 
	  		if($seq == 1){
	  			unset($d['branchWaybillNo'],$d['backWaybillNo']);
	  		}
	  		$documents[] = $d;
			$seq++; 
	  	}
	  	if($back){
	  		$documents[] = $back;
	  	}
	  }   
	  $arr = [
	    "templateCode"=> get_config('express_sf_printer'),
	    "version"=>"2.0", 
	    "sync"=>true,
	    "fileType"=>"pdf",
	    "documents"=>$documents, 
	  ];   
	  if(self::$debug){
	  	echo "生成面单";  
	  	pr($arr);
	  }
	  $res = self::api('COM_RECE_CLOUD_PRINT_WAYBILLS',$arr);   
	  if($res['apiResultData']['success'] == 1){
	    $fs = $res['apiResultData']['obj']['files'];
	    $new_update = [];
	    $file_count = count($fs);
	    $merger_pdf = [];
	    foreach($fs as $v){
	      $url = $v['url'];
	      $token = $v['token'];
	      if($url){
	        $client = guzzle_http();
	        $headers = [
	          'headers'=>[
	            'X-Auth-token'=>$token
	          ],
	          'form_params'=>[
	            'seqNo'=>$v['seqNo'],
	            'areaNo'=>$v['areaNo'],
	            'pageNo'=>$v['pageNo'],
	          ]
	        ]; 
	        $res    = $client->request('GET', $url,$headers);
	        $body = (string)$res->getBody(); 
	        $waybillNo = $v['waybillNo']; 
	        $pdf_url = '/uploads/sf_pdf/'.$waybillNo.'.pdf';
			$pdf_file = PATH.$pdf_url;
			$merger_pdf[] = $pdf_file;
			$dir  = get_dir($pdf_file); 
			create_dir_if_not_exists([$dir]);    
	        file_put_contents($pdf_file,$body);
	        $new_update[$waybillNo] = $pdf_url; 
	        if($file_count == 1){
	        	self::update($order_id,['pdf_url'=>$pdf_url]); 	
	        }	        
	      } 
	    }
	    
	    if(count($merger_pdf) > 1){ 
            $f_pdf_url = '/uploads/db_pdf/'.$sf_number.'-merger-'.mt_rand(0,1000).'.pdf';
    		$save_path = PATH.$f_pdf_url; 
    		if(self::$meger_pdf){
	            Pdf::merger($merger_pdf,$save_path);
	            self::update($order_id,['pdf_url'=>$f_pdf_url]);
	        }
	    }
	    
	    if($new_update){
	    	$express_num = $order_res['express_num'];
	    	$name = '';
	    	foreach($express_num as &$_express_num_v){
	    		$_express_num_v['pdf_url'] = $new_update[$_express_num_v['waybillNo']];
	    		$name .= $_express_num_v['waybillNo'];
	    	}  
			$up = [
	    		'express_num'=>$express_num,
	    	];  
	    	self::update($order_id,$up);  
	    	$order_info = self::get_one($order_id);
	    } 
	  } 
	  return $order_info;
	}

	
	///////////////////////////////////////////////////////////////
	// 以下代码不要修改
	/////////////////////////////////////////////////////////////// 

	//沙箱环境的地址
	public static  $CALL_URL_BOX = "http://sfapi-sbox.sf-express.com/std/service";
	//生产环境的地址
	public	static $CALL_URL_PROD = "https://sfapi.sf-express.com/std/service"; 

	public static function delete_data($s){
		if($s == 'EXP_RECE_CREATE_ORDER'){
			if(self::$id){
				self::delete(self::$id);
			}
		}
	}

	public static  function api($serviceCode,$msgData,$ignore_err = false){ 
	  if(is_array($msgData)){
	    $msgData = json_encode($msgData);
	  }
	  $res = self::run($serviceCode,$msgData);
	  if(!$ignore_err){
	  	 if($res['apiErrorMsg']){
	  	 		self::delete_data($serviceCode);
		    	json_error(['msg'=>$res['apiErrorMsg'].$serviceCode.' 001']);
		  }
		  if($res['apiResultData']['errorMsg']){
		  	self::delete_data($serviceCode);
		  	json_error(['msg'=>$res['apiResultData']['errorMsg'].$serviceCode." 002"]);
		  }
		  $err = $res['apiResultData']['errorMessage'];
		  if($err){
		  	$err_1 = '';
		  	if(is_array($err)){
		  		foreach($err as $k=>$v){
		  			$err_1 .=$k.$v;
		  		}
		  	}else{
		  		$err_1 = $err;
		  	}
		  	json_error(['msg'=>$err_1.$serviceCode]);
		  }
	  }
	 
	  return $res;
	} 

	public static function get_key(){
	  if(get_config('express_sf_status') == 1){
	    $key = 'express_sf_sandbox_key';
	  }else{
	    $key = 'express_sf_key';
	  }
	  return get_config($key); 
	}

	public static function run($serviceCode,$msgData)
	{ 
		$partnerID = get_config('express_sf_customer_key');
		$checkword = self::get_key();
		$status    = get_config('express_sf_status');
		if($status == 1){
			$url = self::$CALL_URL_BOX;
		}else{
			$url = self::$CALL_URL_PROD;
		}
		$requestID = self::create_uuid(); 
		//获取时间戳
		$timestamp = time(); 
		//通过MD5和BASE64生成数字签名
		$msgDigest = base64_encode(md5((urlencode($msgData .$timestamp. $checkword)), TRUE));
		//发送参数
		$post_data = array(
		    'partnerID' => $partnerID,
		    'requestID' => $requestID,
		    'serviceCode' => $serviceCode,
		    'timestamp' => $timestamp,
		    'msgDigest' => $msgDigest,
		    'msgData' => $msgData
		);   
		$resultCont = self::send_post($url, $post_data); 
		$res = json_decode($resultCont,true);
		if($res['apiResultData']){
			$res['apiResultData'] = json_decode($res['apiResultData'],true);
		}
		return $res;
	}
	

	//POST
	public static  function send_post($url, $post_data) { 
	    $postdata = http_build_query($post_data);
	    $options = array(
	        'http' => array(
	            'method' => 'POST',
	            'header' => 'Content-type:application/x-www-form-urlencoded;charset=utf-8',
	            'content' => $postdata,
	            'timeout' => 15 * 60 // 超时时间（单位:s）
	        )
	    );
	    $context = stream_context_create($options);
	    $result = file_get_contents($url, false, $context); 
	    return $result;
	}
}
