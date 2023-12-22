<?php 
namespace app\express\lib;
use ExpressTemplate\Yd; 
use helper_v3\Pdf;
class ExpressYd extends Base{ 
	public static $title = '韵达';
	public static $support_add_sub = true;
	public static $support_is_sign_back = false;
	public static $id;
	
	public static $order_num;

	public static function get_backurl(){ 
		return host().'/wp-content/plugins/express/callback/yd.php';
	}
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
	  		1=>'普通快递', 
	  ];
	} 
	/**
	* 订单运费查寻
	* 
	* 
	*/
	public static function get_money($trackingNum,$phone = '',$row = []){ 
	   
	}
	/**
	* 取消面单
	* 订单确认/取消接口-速运类API 
	*/
	public static  function close($order_num,$ignore_error = false){
	   $res = self::get_one(['order_num'=>$order_num]); 
	   $wl_order_num = $res['wl_order_num']; 
	   $arr = [ 
			'appid'=>self::get_key(),
			'partner_id'=>self::get_custom_key(),
			'secret'=>self::get_custom_secret(),
			'orders'=>[
				["mailno"=>$wl_order_num,'order_serial_no'=>$order_num],
			], 
		];  
	   $res = self::api('cancelOrder',$arr,$ignore_error); 
	}
	/**
	* 查看物流轨迹
	*/
	public static  function get_wuliu($row){ 
	   if($row['status'] < 1 || $row['status'] == 100){
			return $row['wuliu_info'];
	   }
	   $wl_order_num = $row['wl_order_num'];  
	   $res = self::api('trace',[
		 'mailno'=>$wl_order_num, 
	   ],TRUE);  
	   $steps = $res['data']['steps'];
	   if(!$steps){
	   		return $row['wuliu_info']; 
	   }
	   $routes = [];
	   foreach($steps as $v){
	   		$route = [
				'acceptAddress'=> '',
				'acceptTime'   => $v['time'],
				'remark'       => $v['description'], 
			];
			$now_step = self::parse_wuliu_str($route['remark']); 
        	if($row['status'] < $now_step){
        		self::update($row['id'],['status'=>$now_step]);
        		$is_update = true;
        	}
			$routes[] = $route;
	   } 
	   $wuliu_info = [];
	   $wuliu_info['mailNo'] = $wl_order_num;
	   $wuliu_info['routes'] = $routes;
	   $wuliu_info_list = [$wuliu_info];
	   self::update($row['id'],['wuliu_info'=>$wuliu_info_list]); 
	   return $wuliu_info_list;
	}
	/**
	* 创建面单
	*/
	public static function create_pdf($id){   
		static::$id = $id; 
		$res = self::get_one($id);   
		$wl_order_num = $res['wl_order_num'];
		self::close($wl_order_num,true);
		$y_order_num  = $res['y_order_num']; 
		$num = $res['num']?:1;
		$order_num = $res['order_num']; 
		$new_order_num = express_order_num();
		self::update($res['id'],['order_num'=>$new_order_num]); 
		$customer = $res['customer_address'];
		$fahuo    = $res['fahuo_address'];  
		$monthly = $res['monthly'];
		$res = self::create_month_order($res,$new_order_num,$customer,$fahuo); 
		$d = $res['data'];
		$main = $d[0];
		$wl_order_num = $main['mail_no']; 
		$express_num = [];
		$wuliu_info = [];
		$pdf_info_list = [];
		foreach($d as $v){
			$pdf_info =  json_decode($v['pdf_info'],true);
			$mail_no = $v['mail_no']; 
			$express_num[] = [ 
    			'waybillNo'=>$mail_no,  
    		];
    		$wuliu_info[] = [ 
    			'mailNo'=>$mail_no,  
    		]; 
    		$pdf_info_list[] = ['mail_no'=>$mail_no, 'pdf_info'=>$pdf_info,'wl_order_num'=>$v['order_serial_no']];
    		self::sub_trace($v['order_serial_no'],$mail_no);   
		}  
		$up = [
			'wl_order_num'=> $wl_order_num, 
			'express_num' => $express_num,
			'wuliu_info'  => $wuliu_info,
			'is_trace'=>0,
		];  
		self::update($id,$up);  
	    self::_miandan($id,$pdf_info_list);    
		return $res;
	}
	/**
	* 创建面单
	*/
	public static function _miandan($order_id,$pdf_info_list){  
	    $res = self::get_one($order_id);  
	    $express_num = $res['express_num'];
	    $tpl = new Yd;
		$tpl->image_url = '';
		//收 图片的URL地址
		$tpl->revice_img_url = self::$revice_img_url;
		$tpl->sender_img_url = self::$sender_img_url;
		$pdf_url_list = [];
		$f_pdf_url = '';
		$count = count($pdf_info_list);
		$j = 1;
		$merger_pdf = [];
	    foreach ($pdf_info_list as $v){
	        $wl_order_num = $v['wl_order_num'];
    	    $mail_no = $v['mail_no'];
    	    $pdf_info = $v['pdf_info'][0][0]; 
    		$pdf_url = '/uploads/db_pdf/'.$wl_order_num.'-'.mt_rand(0,1000).'.pdf';
    		$save_path = PATH.$pdf_url;
    		$merger_pdf[] = $save_path;
    		$dir  = get_dir($save_path);
    		create_dir_if_not_exists([$dir]);   
    		if($res['pay_method'] == 1){
    			$t = '寄方付';
    		}else{
    			$t = '收方付';
    		}
    		$desc = '韵达快递：'.$t.'<br>';
    		if($pdf_info['cus_area1']){
    			$desc .= $pdf_info['cus_area1'];
    		}
    		if($pdf_info['remark']){
    			$desc .= $pdf_info['remark'];
    		} 
    		if($count > 0){
    		    $bag_addr_2 = $j.'/'.$count;    
    		}
    		$info = [
    		    'bill_code'=>$wl_order_num,//运单号
    		    'mark'=>$pdf_info['position']." ".$pdf_info['position_no'],//大头笔
    		    'to'=>[$pdf_info['tp_status'],$pdf_info['innerProvinceName']],
    		    'bag_addr_1'=>$pdf_info['package_wdjc'], //集包地
    		    'bag_addr_2'=>$bag_addr_2,
    		    'name'=>$name, //品名内容
    		    'desc'=>$desc, //备注
    		    'type'=>'标快',//
    		    //收货人
    		    'receiver'=>[
    		        'name'=>$pdf_info['receiver_name'],
    		        'phone'=>$pdf_info['receiver_mobile'],
    		        'address'=>$pdf_info['receiver_area_names'].$pdf_info['receiver_address'],
    		    ],
    		    'sender'=>[
    		        'name'=>$pdf_info['sender_name'],
    		        'phone'=>$pdf_info['sender_mobile'],
    		        'address'=>$pdf_info['sender_area_names'].$pdf_info['sender_address'],
    		    ],
    		    'save_path'=> $save_path,
    		    //'return_content'=>true,
    		];    
			$tpl->output($info); 
			if(!$f_pdf_url){
			    $f_pdf_url = $pdf_url;
			}
			$pdf_url_list[$mail_no] = $pdf_url; 
			$j++;
	    } 
	    foreach ($express_num as $k=>$v){
	        $waybillNo = $v['waybillNo'];
	        $pdf_url  = $pdf_url_list[$waybillNo];
	        if($pdf_url){
	            $express_num[$k]['pdf_url'] = $pdf_url;
	        }
	    }
	    if(count($merger_pdf) > 1){ 
            $f_pdf_url = '/uploads/db_pdf/'.$wl_order_num.'-merger-'.mt_rand(0,1000).'.pdf';
    		$save_path = PATH.$f_pdf_url;
    		create_dir_if_not_exists([$dir]);
            Pdf::merger($merger_pdf,$save_path);
	    }
	    $up = [
			'pdf_url'=>$f_pdf_url,
			'express_num'=>$express_num,
		];
    	self::update($order_id,$up);
	}
	
	/**
	* 月结
	*/
	public static function create_month_order($res,$new_order_num,$customer,$fahuo){ 
		//创建订单 
		$order_detail = [
			'order_serial_no'=>$new_order_num,
			'khddh'=>$new_order_num,
			'order_type'=>'common',
			'node_id'=>350,
			'receiver'=>[ 
				"address"=>$customer['address'],
				"city"=>$customer['city'],
				"company"=>$customer['company'],
				"name"=>$customer['contact'], 
				"county"=>$customer['county'],
				"mobile"=>$customer['mobile'],
				"province"=>$customer['province'],
				"company"=>$customer['com_title'],
			],
			'sender'=>[ 
				"address"=>$fahuo['address'],
				"city"=>$fahuo['city'],
				"company"=>$fahuo['company'],
				"name"=>$fahuo['contact'], 
				"county"=>$fahuo['county'],
				"mobile"=>$fahuo['mobile'],
				"province"=>$fahuo['province'], 
			],
		];
		if($res['pay_method'] == 2){
			//到付
			$order_detail['markingInfos'][] = ['type'=>'DF'];
		} 
		$sub_num = $res['sub_num'];
		$total = $res['num'];
		if($sub_num > 0){   
			$order_detail['multi_pack'] = ['mulpck'=>1];
			$markingInfos = [];
			$markingInfos[] = ["type"=>"MUL","markingValue"=>["value"=>$total]]; 
			$order_detail['markingInfos'] = $markingInfos;
		} 
		$order_detail_list = [];  
		for($i = 0;$i<$total;$i++){
		    if($i>0){
		        $order_detail['order_serial_no'] = $new_order_num."_".$i;
		    }
		    self::$order_num[] = $order_detail['order_serial_no'];
		    $order_detail_list[] = $order_detail;
		}
		$arr = [ 
			'appid'=>self::get_key(),
			'partner_id'=>self::get_custom_key(),
			'secret'=>self::get_custom_secret(),
			'orders'=>$order_detail_list, 
		];  
		if($res['pdf_url']){
		    if($sub_num > 0){ 
		        $order_detail['multi_pack'] = [
		            'mulpck'=>1,
		            'endmark'=>1,
		            'total'=>$total,
		        ];
    			$markingInfos = [];
    			$markingInfos[] = ["type"=>"MUL","markingValue"=>["value"=>$total]]; 
    			$order_detail['markingInfos'] = $markingInfos;
		    }
			$res = self::api('updateOrder',$arr);  
			return $res;
		}else{ 
		    
			$res =  self::api('addOrder',$arr);  
			return $res;
		}
	}
	/**
	* 订阅物流轨迹
	*/
    public static function sub_trace($order_num,$wl_order_num){
        $data = [
    		 'orders'=>[
    		 	['orderid'=>$order_num,'mailno'=>$wl_order_num,]
    		 ]  
    	];   
		$res = self::api('sub_trace',$data);   
    } 
	
	public static $urls = [
		'addOrder'=>'/openapi-api/v1/accountOrder/createBmOrder',
		'updateOrder'=>'/openapi-api/v1/accountOrder/updateBmOrder',
		'cancelOrder'=>'/openapi-api/v1/accountOrder/cancelBmOrder',
		//散客
		'addOrderSan'=>'/openapi-api/v1/order/pushOrder', 
		//散客
		'cancelOrderSan'=>'/openapi-api/v1/order/cancelOrder',
		'trace'=>'/openapi/outer/logictis/query',
		'sub_trace'=>'/openapi/outer/logictis/subscribe',
		'miandan'=>'/open/waybill_print_adapter/v1/05L9aS/{K21000119}',
		//电子面单余量查询接口
		'miandan_money'=>'/openapi-api/v1/accountOrder/searchCount', 
		'get_price'=>'/open/charge_adapter/v1/05L9aS/TEST', 
	];

	///////////////////////////////////////////////////////////////
	// 以下代码不要修改
	/////////////////////////////////////////////////////////////// 

 
	//沙箱环境的地址
	public static  $CALL_URL_BOX = "https://u-openapi.yundasys.com"; 
	//生产环境的地址
	public static $CALL_URL_PROD = "https://openapi.yundaex.com";  

	public static function delete_data($s){
		if($s == 'addOrder'){
			if(self::$id){
				self::delete(self::$id);
			}
		}
	}
	
	public static  function api($serviceCode,$msgData,$ignore_err = false){  
	  $url = self::$urls[$serviceCode];
	  if(get_config('express_yd_status') == 1){
	    $url = self::$CALL_URL_BOX.$url;
	  }else{
	    $url = self::$CALL_URL_PROD.$url;
	  }
	  $res = self::send_post($serviceCode,$url,$msgData); 
	  if(!$ignore_err){ 
		  	if($res['result'] != 1){
		  		self::delete_data($serviceCode);
			  	json_error(['msg'=>$res['message'].$serviceCode,'res'=>$res]); 
		  	} 
	  } 
	  return $res;
	} 


	public static function get_custom_key(){
	  return get_config('express_yd_customer_key'); 
	} 

	public static function get_custom_secret(){ 
	  return get_config('express_yd_customer_secret'); 
	}
	

	public static function get_key(){
	  if(get_config('express_yd_status') == 1){
	    $key = 'express_yd_sandbox_key';
	  }else{
	    $key = 'express_yd_key';
	  }
	  return get_config($key); 
	} 

	public static function get_secret(){
	  if(get_config('express_yd_status') == 1){
	    $key = 'express_yd_sandbox_secret';
	  }else{
	    $key = 'express_yd_secret';
	  } 
	  return get_config($key); 
	}


	public static  function send_post($serviceCode,$url, $data) {  
		if(!$data){
			return;
		} 
		$json = json_encode($data,JSON_UNESCAPED_UNICODE);   	 
		$sign = md5($json.'_'.self::get_secret());  
		$header = [
			'app-key' => self::get_key(),
			'sign'    => $sign,
			'req-time'=> time(),
			'Content-Type'=>'application/json;charset=UTF-8',
		];  
		try {
			$client = guzzle_http();
			$res    = $client->request('POST', $url,[
				'headers'=> $header,
				'body'   => $json
			]);  
			$res = (string)$res->getBody(); 
			return json_decode($res,true); 
		} catch (\Exception $e) {
			self::delete_data($serviceCode);
			return json_error(['msg'=>$e->getMessage()]); 
		} 
	} 

}