<?php 
namespace app\express\lib;
class Base{
	public static $support_add_sub = false;

	public static $revice_img_url = "/wp-content/plugins/express/lib/template/revice.png";
	public static $sender_img_url = "/wp-content/plugins/express/lib/template/sender.png";
	//底部左侧二维码的URL地址
	public static $qr_url         = "/wp-content/plugins/express/lib/template/##_qr.jpg"; 

	public static function get_one($id){
		if(is_array($id)){
			$where = $id;
		}else{
			$where['id'] = $id;
		}
		return db_get_one("express_order","*",$where);
	}

	public static function get_one_where($where){ 
		return db_get_one("express_order","*",$where);
	}
 
	public static function delete($id){
		if(!$id){
			return;
		}
		db_del("express_order",['id'=>$id]);
	}

	public static function update($id,$data = []){ 
		db_update('express_order',$data,['id'=>$id],true);
	}

	public static function before(){
		
	}

	
	/**
	* 根据物流内容返回状态编码
	*/
	public static function parse_wuliu_str($str){
		if(!$str){
			return;
		}
		$status = '';
		if(strpos($str,'揽收')!==false || strpos($str,'收取')!==false){
			$status = 20;
		}else if(strpos($str,'发往')!==false || strpos($str,'离开')!==false  || strpos($str,'到达')!==false || strpos($str,'运输中')!==false ||
            strpos($remark,'发件') !== false || strpos($remark,'到件') !== false){ 
			$status = 35;
		}else if(strpos($str,'派件')!==false || strpos($str,'派送')!==false || strpos($str,'配送')!==false || strpos($str,'分配')!==false){ 
			$status = 40;
		}else if(strpos($str,'签收')!==false){
			$status = 100;
		}
		return $status; 
	} 

	/**
	* 获取UUID
	*/
	public static function create_uuid() {
	    $chars = md5(uniqid(mt_rand(), true));
	    $uuid = substr ( $chars, 0, 8 ) . '-'
	        . substr ( $chars, 8, 4 ) . '-'
	        . substr ( $chars, 12, 4 ) . '-'
	        . substr ( $chars, 16, 4 ) . '-'
	        . substr ( $chars, 20, 12 );
	    return $uuid ;
	} 
	/**
	* 默认发货人ID
	*/
	public static function get_user_address_id($data = []){
		if($data['mobile']){
			$contact = $data['contact'];
			$mobile  = $data['mobile'];
			$address = $data['address'];
			$arr     = $data['arr'];
			$province   = $arr[0];
			$city       = $arr[1];
			$county     = $arr[2];
			// province county city
			$res = db_get_one("express_fahuo_address","*",[
				'province'=>$province,
				'city'=>$city,
				'county'=>$county,
				'contact'=>$contact,
				'address'=>$address,
				'mobile'=>$mobile,
			]);
			if($res){
				db_update("express_fahuo_address",['updated_at'=>now()],['id'=>$res['id']]);
				return $res['id'];
			}else{
				return db_insert("express_fahuo_address",[
					'province'=>$province,
					'city'=>$city,
					'county'=>$county,
					'contact'=>$contact,
					'address'=>$address,
					'mobile'=>$mobile,
					'status'=>1,
					'created_at'=>now(),
					'updated_at'=>now(),
				]);
			}
		}
	  	return db_get_one("express_fahuo_address",'id',['is_default'=>1]);
	}
	/**
	*  收货人ID
	*/
	public static function get_customer_address_id($data = []){
		if($data['mobile']){
			$com_title = $data['com_title'];
			$contact = $data['contact'];
			$mobile  = $data['mobile'];
			$address = $data['address'];
			$arr     = $data['arr'];
			$province   = $arr[0];
			$city       = $arr[1];
			$county     = $arr[2];
			// province county city
			$res = db_get_one("express_customer_address","*",[
				'province'=>$province,
				'city'=>$city,
				'county'=>$county,
				'contact'=>$contact,
				'address'=>$address,
				'mobile'=>$mobile,
			]);
			if($res){
				if($com_title){
					db_update("express_customer_address",['com_title'=>$com_title],['id'=>$res['id']]);	
				}				
				return $res['id'];
			}else{
				return db_insert("express_customer_address",[
					'province'=>$province,
					'city'=>$city,
					'county'=>$county,
					'contact'=>$contact,
					'com_title'=>$com_title,
					'address'=>$address,
					'mobile'=>$mobile,
					'status'=>1,
					'created_at'=>now(),
					'updated_at'=>now(),
				]);
			}
		}
	  	return db_get_one("express_customer_address",'id',['is_default'=>1]);
	}
 	/**
 	* 选择发货人
 	*/
	public static function get_user_address_select(){
	  $list = [];
	  $all = db_get("express_fahuo_address","*",[]);
	  foreach($all as $v){
	      $list[] = [
	        'label'=>$v['contact']." ".$v['mobile']." ".$v['address'],
	        'value'=>$v['id'],
	      ];
	  }
	  return $list;
	}

	/**
	* 选择客户
	*/
	public static function get_customer_address_select(){
	  $list = [];
	  $all = db_get("express_customer_address","*",[]);
	  foreach($all as $v){
	      $list[] = [
	        'label'=>$v['contact']." ".$v['mobile']." ".$v['address'],
	        'value'=>$v['id'],
	      ];
	  }
	  return $list;
	}

	public static function get_qr_url($name){
		return str_replace('##',$name,self::$qr_url); 
	}
 
}
