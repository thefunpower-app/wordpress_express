<?php 
namespace app\express\lib;
/**
* https://open.yto.net.cn/linkInterFace/developerInformation
* 
*/
class ExpressYt extends Base{ 
	public static $title = '圆通';
	public static $support_add_sub = false;
	public static $support_is_sign_back = false;
	public static $server = '';
	public static $id;
	public static $need_sub_trace = false;
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
		    'PK'=>'普通普快',
		    'YZD'=>'圆准达',  
	  ];

	} 

	public static $urls = [
		'addOrder'=>'/open/privacy_create_adapter/v1/05L9aS/{K21000119}',
		//散客
		'addOrderSan'=>'/open/order_create_adapter/v2/05L9aS/TEST_STD',
		'cancelOrder'=>'/open/korder_cancel_adapter/v1/05L9aS/{K21000119}',
		//散客
		'cancelOrderSan'=>'/open/order_cancel_adapter/v2/05L9aS/TEST_STD',
		'trace'=>'/open/track_query_adapter/v1/05L9aS/TEST',
		'miandan'=>'/open/waybill_print_adapter/v1/05L9aS/{K21000119}',
		'miandan_money'=>'/open/waybill_balance_adapter/v1/05L9aS/{K21000119}', 
		'get_price'=>'/open/charge_adapter/v1/05L9aS/TEST', 
	];
 

	public static function delete_data($s){
		if($s == 'addOrder' || $s == 'miandan'){
			if(self::$id){
				self::delete(self::$id);
			}
		}
	}

	/**
	* 创建面单
	*/
	public static function create_pdf($id){  
		static::$id = $id;
		$res = self::get_one($id);   
		$num = $res['num']?:1;
		$order_num = $res['order_num']; 
		$customer = $res['customer_address'];
		$fahuo    = $res['fahuo_address'];
		$increments = []; 
		//增值类型：1-代收货款；2-到付；4-保价，注：增值服务不能同时选择代收和到付。
		//$increments[] = ['type'=>4,'amount'=>5000];		
		// 金额，单位：元，代收货款金额：[3,20000]；到付金额：[1,5000]；保价金额：[100,30000]。
		$pay_method = $res['pay_method'];
		if($pay_method == 2){
			$increments[] = ['type'=>2,'amount'=>1];	
		} 
		$arr = [
			'logisticsNo'=>$order_num,
			'senderName'=>$fahuo['contact'],
			'senderProvinceName'=>$fahuo['province'],
			'senderCityName'=>$fahuo['city'],
			'senderCountyName'=>$fahuo['county'],
			'senderAddress'=>$fahuo['address'],
			'senderMobile'=>$fahuo['mobile'],  
			'recipientName'=>$customer['contact'],  
			'recipientProvinceName'=>$customer['province'],  
			'recipientCityName'=>$customer['city'],  
			'recipientCountyName'=>$customer['county'],  
			'recipientAddress'=>$customer['address'],  
			'recipientMobile'=>$customer['mobile'],  
			'increments'=>$increments, 
		];  

		$res = self::api('addOrder',$arr); 
		if(!$res['mailNo']){
			return json_error(['msg'=>'下单异常']);
		}
		$wl_order_num = $res['mailNo'];  
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
		$up = [
			'wl_order_num'=> $wl_order_num,
			'api_data'    => $res,
			'express_num' => $express_num,
			'wuliu_info'  => $wuliu_info,
		];  
		self::update($id,$up); 
		self::_miandan($id,$wl_order_num);  
	}

	public static function is_sandbox(){
	   $sandbox    = self::get_config('express_yt_status');
	   if($sandbox == 1){
	    	return true;
	   }else {
	   	    return false;
	   }
	}
 
	/**
	* 查看物流轨迹
	*/
	public static  function get_wuliu($row){ 
	   if($row['status'] < 1 || $row['status'] == 100){
			return $row['wuliu_info'];
	   }
	   $logisticsNo = $row['wl_order_num']; 
	   $wuliu_info  = $row['wuliu_info']; 
	   $status      = self::get_config('express_yt_status');   
	   $res = self::api('trace',[
		 'NUMBER'=>$logisticsNo, 
	   ],TRUE); 
	   if(self::is_sandbox()){
		   $res = '[
			    {
			        "waybill_No": "YT2000000000000",
			        "upload_Time": "2023-04-24 20:37:33",
			        "infoContent": "GOT",
			        "processInfo": "您的快件被【浙江省金华市义乌市上溪镇】揽收，揽收人: xxx (xxxxxxxxx)",
			        "city": "金华市",
			        "district": "义乌市",
			        "weight": 0.68
			    },
			    {
			        "waybill_No": "YT2000000000000",
			        "upload_Time": "2023-04-24 23:12:15",
			        "infoContent": "DEPARTURE",
			        "processInfo": "您的快件离开【浙江省金华市义乌市上溪镇】，准备发往【义乌转运中心直营公司】",
			        "city": "金华市",
			        "district": "义乌市"
			    },
			    {
			        "waybill_No": "YT2000000000000",
			        "upload_Time": "2023-04-25 01:45:03",
			        "infoContent": "ARRIVAL",
			        "processInfo": "您的快件已经到达【义乌转运中心直营公司】",
			        "city": "金华市",
			        "district": "义乌市"
			    },
			    {
			        "waybill_No": "YT2000000000000",
			        "upload_Time": "2023-04-25 02:35:03",
			        "infoContent": "DEPARTURE",
			        "processInfo": "您的快件离开【义乌转运中心直营】",
			        "city": "金华市",
			        "district": "义乌市"
			    },
			    {
			        "waybill_No": "YT2000000000000",
			        "upload_Time": "2023-04-25 09:14:38",
			        "infoContent": "ARRIVAL",
			        "processInfo": "您的快件已经到达【上虞转运中心公司】",
			        "city": "绍兴市",
			        "district": "上虞区"
			    },
			    {
			        "waybill_No": "YT2000000000000",
			        "upload_Time": "2023-04-25 09:49:16",
			        "infoContent": "DEPARTURE",
			        "processInfo": "您的快件离开【上虞转运中心】，准备发往【浙江省绍兴市上虞市公司】",
			        "city": "绍兴市",
			        "district": "上虞区"
			    },
			    {
			        "waybill_No": "YT2000000000000",
			        "upload_Time": "2023-04-25 09:50:56",
			        "infoContent": "ARRIVAL",
			        "processInfo": "您的快件已经到达【浙江省绍兴市上虞市公司】",
			        "city": "绍兴市",
			        "district": "上虞区"
			    },
			    {
			        "waybill_No": "YT2000000000000",
			        "upload_Time": "2023-04-25 13:50:14",
			        "infoContent": "SENT_SCAN",
			        "processInfo": "【浙江省绍兴市上虞市】的顾金毅(xxxxxxxx)正在派件，圆通已开启“安全呼叫”保护您的电话隐私，请放心接听！[95161和185211号段的上海号码为圆通业务员专属号码]",
			        "city": "绍兴市",
			        "district": "上虞区"
			    },
			    {
			        "waybill_No": "YT2000000000000",
			        "upload_Time": "2023-04-25 15:09:52",
			        "infoContent": "SIGNED",
			        "processInfo": "您的快件已签收，签收人: xxx。如有疑问请联系: xxxxxxxx，投诉电话: 0575-89288222。感谢使用圆通速递，期待再次为您服务！举手之劳勿忘送件人，请在[评价快递员]处赐予我们五星好评~",
			        "city": "绍兴市",
			        "district": "上虞区"
			    }
			]';
			$res = json_decode($res,true);
	   } 
	   if($res['map']){
	   		return;
	   } 
	   $flag = false;
	   foreach($res as $v){
		   	if($v['processInfo'] && $v['upload_Time'] && $v['processInfo']){
		   		$route = [
					'acceptAddress'=> $v['processInfo'],
					'acceptTime'   => $v['upload_Time'],
					'remark'       => $v['processInfo'],
				];
				//根据物流内容返回状态值
				$status = self::parse_wuliu_str($route['remark']);
				if($row['status'] < $status){
	        		self::update($row['id'],['status'=>$status]); 
	        	}
				$routes[] = $route;
				$flag = true;
		   	} 
	   }
	   if($flag){ 
	   	 $wuliu_info = [];
	   	 $wuliu_info[0]['mailNo'] = $logisticsNo; 
	   	 $wuliu_info[0]['routes'] = $routes;  
	   	 self::update($row['id'],['wuliu_info'=>$wuliu_info]);
	   	 return $wuliu_info;
	   } 
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
	public static  function close($order_num){
	   $res = self::get_one(['order_num'=>$order_num]);
	   $logisticsNo = $res['wl_order_num'];
	   $res = self::api('cancelOrder',[
		 'logisticsNo'=>$logisticsNo,
		 'cancelDesc'=>'用户主动取消', 
	   ]);

	}
 
	/**
	* 创建面单
	*/
	public static function _miandan($order_id,$waybillNo,$clientcode=''){  
		//电子面单余额查询
		$res = self::api('miandan_money',[
		 'clientId'=>get_config('express_yt_customer_key'),
		 'timestamp'=>time(),
		 'requestDate'=>date('Ymd'),
		]);
		$money = $res['data']; //70951
		if(!$money || !$money <= 0){

		} 
		$res = self::get_one($order_id);
		$express_num   = $res['express_num']; 
		if(self::is_sandbox()){
			$waybillNo = 'YT2819004229916';
		} 
		$arr = [
			'waybillNo'=>$waybillNo, 
		]; 
		$res = self::api('miandan',$arr);
		if($res['code'] == -1) {
			self::delete_data(self::$server);
			return json_error(['msg'=>$res['message']." ".$waybillNo]);
		}
		$c = $res['data']['pdfBase64']; 
		if($c && $c = base64_decode($c)){
			$pdf_url = '/uploads/yt_pdf/'.$waybillNo.'-'.mt_rand(0,1000).'.pdf';
			$pdf_file = PATH.$pdf_url;
			$dir  = get_dir($pdf_file); 
			create_dir_if_not_exists([$dir]);   
		    file_put_contents($pdf_file,$c); 
		    self::update($order_id,['pdf_url'=>$pdf_url]);
		    $new_update = [];
		    $new_update[$waybillNo] = $pdf_url;   
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
		}
	}
	///////////////////////////////////////////////////////////////
	// 以下代码不要修改
	/////////////////////////////////////////////////////////////// 
	public static function get_config($key){ 
		return get_config($key);
	}

	//POST
	public static  function send_post($url, $post_data,$ignore_err=false) { 
		$url = str_replace("{K21000119}",get_config('express_yt_customer_key'),$url);
		preg_match_all('/\/open\/([a-z_]*)/i',$url,$r);
		$m = $r[1][0]; 
    	$content    = [
    		'param'    => json_encode($post_data,JSON_UNESCAPED_UNICODE),
    		'timestamp'=> time(),
    		'sign'     => self::get_sign($post_data,$m),
    		'format'   => 'JSON'
    	];    

	    try {
	    	$client  = guzzle_http();
	    	$res     = $client->request('POST', $url,['form_params'=>$content]); 
	    	$res     = (string)$res->getBody(); 
	    	return $res;
	    } catch (\Exception $e) {
	    	$err = $e->getMessage();  
	    	self::delete_data(self::$server);
	    	if($ignore_err){return;}
	    	return json_error(['msg'=>$err]);
	    } 
	}

	public static  function api($serviceCode,$data,$ignore_err = false){  
	    self::$server = $serviceCode; 
		$url       = self::$urls[$serviceCode];
		$status    = self::get_config('express_yt_status');
		if($status == 1){
			$url = self::$CALL_URL_BOX.$url;
		}else{
			$url = self::$CALL_URL_PROD.$url;
		}   
		$res = self::send_post($url, $data,$ignore_err);   
		$res = json_decode($res,true);  
		return $res;
	} 

	//沙箱环境的地址
	public static  $CALL_URL_BOX = "https://openuat.yto56test.com:6443"; 
	//生产环境的地址
	public static $CALL_URL_PROD = "https://openuat.yto56test.com:6443";   
 
	public static function get_sign($par = [],$method='privacy_create_adapter'){ 		
		$version = self::get_version($method);
		$str     = json_encode($par,JSON_UNESCAPED_UNICODE).$method.$version.trim(self::get_config('express_yt_customer_secret'));   
		return base64_encode(pack('H*', md5($str)));
	}
	
	public static function get_version($method){
		$version = 'v1';
		return $version;
	}
}
