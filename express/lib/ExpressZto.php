<?php 
namespace app\express\lib;
/*
中通
https://open.zto.com/#/documents?menuId=4

composer require helophp/zopsdk-php
*/
use zop\ZopClient;
use zop\ZopProperties;
use zop\ZopRequest;
use ExpressTemplate\Zto; 
class ExpressZto extends Base{ 
	public static $title = '中通'; 
	public static $support_add_sub = false;
	public static $support_is_sign_back = false;
	public static $server;
	public static $id;
	//需要订阅物流轨迹
	public static $need_sub_trace = true;
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
		    //'YZD'=>'圆准达',  
	  ];

	}  
	public static $urls = [
		'addOrder'=>'/zto.open.createOrder',
		//散客
		'addOrderSan'=>'',
		'cancelOrder'=>'/zto.open.cancelPreOrder',
		//散客
		'cancelOrderSan'=>'',
		'trace'=>'/zto.merchant.waybill.track.query',
		'sub_trace'=>'/zto.merchant.waybill.track.subsrcibe', 
		'miandan'=>'/zto.open.order.print',
		'miandan_money'=>'/zto.open.queryAvailableBalanceNew', 
		'get_price'=>'',  
		//网点信息查询
		'get_site_code'=>'/zto.sp.getBaseOrganizeByFullNameGateway',
		//绑定电子面单
		'bind_account'=>'/zto.open.bindingEaccount',
		//大头笔
		'bag_mark'=>'/zto.innovate.bagAddrMark'
	];
	/**
	* 网点信息查询
	*/
	public static function get_site_code($name = '上海徐汇'){
		$arr['fullName'] = $name;
		$res = self::api('get_site_code',$arr); 
		return $res['result']['orgCode']; 
	}
	/**
	* 绑定电子面单账号
	*/
	public static function bind_account($eaccount,$eaccountPwd,$siteCode){ 
		$siteCode = self::get_site_code($siteCode)?:$siteCode;
		$arr['eaccount']    = $eaccount;
		$arr['eaccountPwd'] = $eaccountPwd;
		$arr['siteCode']    = $siteCode;  
		$res = self::api('bind_account',$arr);   
	}
    /**
	* 大头笔
	*/
	public static function bag_mark($receive,$sender,$unionCode){  
		$receive_address = $receive['province'].$receive['city'].$receive['county'].$receive['address'];
		$sender_address = $sender['province'].$sender['city'].$sender['county'].$sender['address'];

	    $opt['receive_address'] = $receive_address; 
	    $opt['receive_province'] = $receive['province']; 
	    $opt['receive_city'] = $receive['city']; 
	    $opt['receive_district'] = $receive['county']; 

	    $opt['send_address'] = $sender_address; 
	    $opt['send_province'] = $sender['province']; 
	    $opt['send_city'] = $sender['city']; 
	    $opt['send_district'] = $sender['county']; 

	    $opt['unionCode'] = $unionCode;  
		$res = self::api('bag_mark',$opt);   
		return $res['result'];
	} 
	public static function is_sandbox(){
		if(get_config("express_zto_status") == 1){
			return true;
		}else{
			return false;
		}
	}

	public static function before(){
		if(self::is_sandbox()){
			return;
		}
		//下单前要绑定电子面单账号
		$eaccount = get_config("express_zto_customer_key");
		$eaccountPwd = get_config("express_zto_customer_secret");
		$siteCode = get_config("express_zto_site_code");
		$key = "bind_zto_".$eaccount.md5($eaccountPwd.$siteCode);
		$val = get_config($key);
		if($val){
			return;
		}
		$res = self::bind_account($eaccount,$eaccountPwd,$siteCode); 
		set_config($key,1);
	}
	
	/**
	* 创建面单
	*/
	public static function create_pdf($id){   
	    static::$id = $id; 
		$res = self::get_one($id);   
		$num = $res['num']?:1;
		$order_num = $res['order_num']; 
		self::close($order_num,true);
		$customer = $res['customer_address'];
		$fahuo    = $res['fahuo_address'];
		$increments = [];
		$arr = [
			//合作模式 ，1：集团客户；2：非集团客户
			'partnerType'=>2,
			//partnerType为1时，orderType：1：全网件 2：预约件。 partnerType为2时，orderType：1：全网件 2：预约件（返回运单号） 3：预约件（不返回运单号） 4：星联全网件
			'orderType'=>2, 
			'partnerOrderCode'=>$order_num,
			'senderInfo'=>[
				'senderName'=>$fahuo['contact'],
				'senderProvince'=>$fahuo['province'],
				'senderCity'=>$fahuo['city'],
				'senderDistrict'=>$fahuo['county'],
				'senderAddress'=>$fahuo['address'],
				'senderMobile'=>$fahuo['mobile'],  
			], 
			'receiveInfo'=>[
				'receiverName'=>$customer['contact'],  
				'receiverProvince'=>$customer['province'],  
				'receiverCity'=>$customer['city'],  
				'receiverDistrict'=>$customer['county'],  
				'receiverAddress'=>$customer['address'],  
				'receiverMobile'=>$customer['mobile'],  
			], 
			'accountInfo'=>[
				//电子面单账号
				'accountId'=>get_config('express_zto_customer_key'),
				//电子面单密码（测试环境传ZTO123）
				'accountPassword'=>get_config('express_zto_customer_secret'),
			]
		]; 
		$bill_code = $res['y_order_num'];  
		if($bill_code){
			$arr['billCode'] = $bill_code;
			$arr['orderType'] = 3;  
		}
		$res = self::api('addOrder',$arr);  
		$wl_order_num = $res['result']['billCode']; 
		if(!$wl_order_num){
			return json_error(['msg'=>'下单异常']);
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
		$up = [
			'wl_order_num'=> $wl_order_num,
			//'api_data'    => $res,
			'express_num' => $express_num,
			'wuliu_info'  => $wuliu_info,
			'is_trace'=>0,
		];  
		self::update($id,$up);   
		self::sub_trace($wl_order_num,$customer['mobile']);
		self::_miandan($id,$wl_order_num,$res['result']);  
	}

	/**
	* 订阅物流轨迹
	*/
    public static function sub_trace($wl_order_num,$phone){
        $data = [
    		 'billCode'=>$wl_order_num, 
    		 'mobilePhone'=>substr($phone,-4),
    	];   
		$res = self::api('sub_trace',$data);  
	    if($res['status'] == 1){ 
	    	return true;
	    }else {
	    	return false;
	    }
    }
	/**
	* 取面单信息
	* 订单结果查询接口-速运类API
	*/
	public static function get_query($order_num){
	   
	}

	/**
	* 查看面单时触发派件通知
	 
	*/
	public static  function notice($sf_number){
	   
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
	   $res = self::api('trace',[
		 'billCode'=>$logisticsNo, 
	   ]);
	   if($res['result']){ 
			$routes = [];
			foreach($res as $v){
				$route = [
					'acceptAddress'=> '',
					'acceptTime'   => date('Y-m-d H:i:s',$v['scanDate']),
					'remark'       => $v['desc'],
					'data'         => $v,
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
	}
	/**
	* 订单运费查寻 
	*/
	public static function get_money($trackingNum,$phone = '',$row = []){ 
	   
	}
	/**
	* 取消订单
	*/
	public static  function close($order_num,$ignore_error = false){
	   $res = self::get_one(['order_num'=>$order_num]);
	   $logisticsNo = $res['wl_order_num'];
	   $res = self::api('cancelOrder',[
		 'billCode'=>$logisticsNo,
		 'cancelType'=>1,  
	   ],$ignore_error); 
	}
	/**
	* 创建面单
	*/
	public static function _miandan($order_id,$wl_order_num,$order_info){  
		$pdf_url = '/uploads/db_pdf/'.$wl_order_num.'-'.mt_rand(0,1000).'.pdf';
		$save_path = PATH.$pdf_url;
		$dir  = get_dir($save_path);
		create_dir_if_not_exists([$dir]);  
		$res = self::get_one($order_id);  
		$bag_mark = self::bag_mark($res['customer_address'],$res['fahuo_address'],$wl_order_num);
		$tpl = new Zto;
		$tpl->image_url = '';
		//收 图片的URL地址
		$tpl->revice_img_url = self::$revice_img_url; 
		$tpl->qr_url         = self::get_qr_url('zto'); 

		$tpl->revice_img_url = "/wp-content/plugins/express/lib/template/revice.png";
		//底部左侧二维码的URL地址
		$tpl->qr_url         = "/wp-content/plugins/express/lib/template/zto_qr.png";   
		$info = [
		    'time'=>now(),
		    'bill_code'=>$wl_order_num,//运单号
		    'mark'=>$bag_mark['mark'],//大头笔
		    'bag_addr_1'=>$bag_mark['bagAddr'], //集包地
		    'bag_addr_2'=>'',
		    'name'=>$res['name'], //品名内容
		    'desc'=>$res['desc'], //备注
		    'type'=>'标快',//
		    //收货人
		    'receiver'=>[
		        'name'=>$res['customer_address']['contact'],
		        'phone'=>$res['customer_address']['mobile'],
		        'address'=>$res['customer_address']['province'].$res['customer_address']['city'].$res['customer_address']['county'].$res['customer_address']['address'],
		    ],
		    'sender'=>[
		        'name'=>$res['fahuo_address']['contact'],
		        'phone'=>$res['fahuo_address']['mobile'],
		        'address'=>$res['fahuo_address']['province'].$res['fahuo_address']['city'].$res['fahuo_address']['county'].$res['fahuo_address']['address'],
		    ],
		    'save_path'=> $save_path,
		    //'return_content'=>true,
		];   
		if($express_num && count($express_num) > 1){
			$total = count($express_num);
			$i = 1;
			$up = [];
			$merger_pdf = [];
			foreach($express_num as $k=>$v){
				$pdf_url = '/uploads/db_pdf/'.$wl_order_num."-".$v['waybillNo'].mt_rand(0,99999).'.pdf';
				$save_path = PATH.$pdf_url;
				$merger_pdf[] = $save_path;
				if($wl_order_num == $v['waybillNo']){
					$up['pdf_url'] = $pdf_url;
				}
				$info['bill_code'] =  $v['waybillNo'];
				$tpl->output($info); 
				$express_num[$k]['pdf_url'] = $pdf_url;
			}
			if(count($merger_pdf) > 1){ 
	            $f_pdf_url = '/uploads/db_pdf/'.$wl_order_num.'-merger-'.mt_rand(0,1000).'.pdf';
	    		$save_path = PATH.$f_pdf_url; 
	            Pdf::merger($merger_pdf,$save_path);
	            $up['pdf_url'] = $f_pdf_url;
		    }
			$up['express_num'] = $express_num;
			self::update($order_id,$up);
		}else { 
			$tpl->output($info); 
			$express_num[0]['pdf_url'] = $pdf_url;
			$up = [
				'pdf_url'=>$pdf_url,
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

	public static  function api($serviceCode,$data,$ignore_err = false){  
	  self::$server = $serviceCode; 
	  $res = self::run($serviceCode,$data,$ignore_err); 
	  return $res;
	}  

	//沙箱环境的地址
	public static  $CALL_URL_BOX = "https://japi-test.zto.com"; 
	//生产环境的地址
	public static $CALL_URL_PROD = "https://japi.zto.com";   

    public static function delete_data($s){ 
		if(self::$id && $s == 'addOrder'){
			self::delete(self::$id);
		} 
	}
	
	public static function run($serviceCode,$data,$ignore_err = false){   
		$url       = self::$urls[$serviceCode];
		$status    = self::get_config('express_zto_status');
		if($status == 1){
			$url = self::$CALL_URL_BOX.$url;
			$companyid = get_config("express_zto_sandbox_key");
			$key = get_config("express_zto_sandbox_secret");
		}else{
			$url = self::$CALL_URL_PROD.$url;
			$companyid = get_config("express_zto_key");
			$key = get_config("express_zto_secret");
		}    
		$properties = new ZopProperties($companyid, $key);
		$client = new ZopClient($properties);
		$request = new ZopRequest();
		$request->setUrl($url);
		$request->setData(json_encode($data));
		$res =  $client->execute($request);  
		$res =  json_decode($res,true);
		if(!$ignore_err){
			if($res['status'] != 1){
			    self::delete_data($serviceCode);
				echo json_encode(['msg'=>$res['message'].$url,'type'=>250,'type'=>'error'],JSON_UNESCAPED_UNICODE);
				exit;
			}
		}
		return $res;
	} 


}