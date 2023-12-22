<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 
use helper_v3\Xls;
use helper_v3\Pdf;
use helper_v3\Image;
global $_wp_express_upload_path; 
Xls::$base_url  = $_wp_express_upload_path;

$where = [];
$per_page = $_POST['per_page'] = $_POST['per_page']?:2; 
$is_img = $_POST['is_img'];
$date = $_POST['date'];
if($date){ 
	if($date[0]){
		$where['created_at[>=]'] = trim($date[0].' 00:00:01');	
	}
	if($date[1]){
		$where['created_at[<=]'] = trim($date[1].' 23:59:59');	
	} 
} 
$wq = $_POST['wq'];
if($wq){
	$or = [];
	$or['order_num[~]'] = $wq; 
	$or['wl_order_num[~]'] = $wq; 
	$or['com_title[~]'] = $wq;
	$or['contact[~]'] = $wq;
	$or['mobile[~]'] = $wq;
	$or['address[~]'] = $wq;
	$where['OR'] = $or;
}

$status = $_POST['status'];
if($status){ 
	if($status == -1){
		$where['status[<]'] = 1;
	}else{
		$where['status'] = $status;
	}
}else{
	$where['status[>]'] = 0;
}

$where['ORDER'] = ['created_at'=>'DESC'];

$method = $_POST['method'];

if($method == 'export'){ 
	$is_today = g("is_today");
	if($is_today){
		$where['created_at[>=]'] = date("Y-m-d 00:00:01");
		$where['created_at[<=]'] = date("Y-m-d 23:59:59");
	}else{
		if(!$date || !$date[0] || !$date[1]){
			return json_error(['msg'=>'请先选择时间']);
		}
		$day = count(get_dates($date[0],$date[1]));
		if($day > 30){
			return json_error(['msg'=>'仅支持导出30天内的数据']);
		}
	}
	$where['status[>]'] = 0;
	$all = db_get("express_order",$where);
	$values = [];
	foreach($all as &$v){
		$index++;
		$v['index']  = $index; 
		get_express_order_row($v);
		$customer_address = $v['customer_address'];
		$top_address = $customer_address['province'].$customer_address['city'].$customer_address['county'];
		$top_address = express_get_new_address($top_address);
		$wuliu_info = $v['express_num']; 
		$wl_order_num_str = '';
		foreach ($wuliu_info as $vv){
			if($vv['text'] != '签回单'){
				$wl_order_num_str .= $vv['waybillNo']." , ";
			} 
		}
		$wl_order_num_str = substr($wl_order_num_str,0,-2);
		 
		$values[] = [
	        'wl_order_num'=>$wl_order_num_str,
	        'com_title'=>$customer_address['com_title'],
	        'mobile'=>$customer_address['mobile'],
	        'address'=>$top_address.$customer_address['address'],
	        'name'=>$customer_address['contact'],
	    ];
	} 
	if($is_img){
	    $mpdf = Pdf::init();
	    $html = "";
	    ob_start();
	    ?>
	    <style>
	        table{border-collapse:collapse;}
	    </style>
	    <table class="pure-table">
            <thead>
                <tr>
                    <th style='text-align: left;width:50px;'>序号</th>
                    <th style='text-align: left;'>物流单号</th>
                    <th style='text-align: left;width:100px;'>收件公司</th>
                    <th style='text-align: left;width:80px;'>收件人</th>
                    <th style='text-align: left;width:80px;'>收件人手机号</th>
                    <th style='text-align: left;'>收件人地址</th>
                </tr>
            </thead>
            <tbody>
                <?php $j=1; foreach ($values as $v){?>
                <tr style="border-bottom:1px solid #000;">
                    <td style='text-align: left;border-bottom:1px solid #000;'><?=$j?></td>
                    <td style='text-align: left;border-bottom:1px solid #000;'><?=$v['wl_order_num']?></td>
                    <td style='text-align: left;border-bottom:1px solid #000;'><?=$v['com_title']?></td>
                    <td style='text-align: left;border-bottom:1px solid #000;'><?=$v['name']?></td>
                    <td style='text-align: left;border-bottom:1px solid #000;'><?=$v['mobile']?></td>
                    <td style='text-align: left;border-bottom:1px solid #000;'><?=$v['address']?></td>
                </tr>
                <?php $j++; }?>
            </tbody>
        </table>
        
        <?php 
	    $html = ob_get_contents();
	    ob_end_clean();
        $mpdf->WriteHTML($html);
        $name = md5(now());
        $file = PATH.'/data/express/copy_pdf_image/'.$name.'.pdf';
        $dir = get_dir($file);
        create_dir_if_not_exists([$dir]); 
        $mpdf->Output($file);
        $output = PATH.'/data/express/copy_pdf_image/'.$name.mt_rand(0,999);
        $dir = get_dir($file);
        create_dir_if_not_exists([$dir]); 
        Pdf::pdf_to_image($file,$output);
        $list = glob($output.'/*.jpg');
        $output_image = PATH.'/data/express/'.$name.mt_rand(0,999).".jpg";
        if(count($list) > 1){
            Image::merger($list,$output_image); 
            $content = file_get_contents($output_image);
            unlink($output_image);
        }else {
            $content = file_get_contents($list[0]);
        } 
        unlink($file);
        unlink($output);
        foreach ($list as $v){
            unlink($v);
        }
	    return json_success(['data'=>'data:image/png;base64,'.base64_encode($content)]);
	}
	
	Xls::$sheet_width = [
	    'A' => "50",
	    'B' => "40",
	    'C' => "15",
	    'D' => "15",
	    'E' => "100", 
	]; 

	$title = "订单".date("Y-m-d-His");
	$url = Xls::create([
	    'wl_order_num'=>'物流单号',
	    'com_title'=>'收件公司',
	    'name'=>'收件人',
	    'mobile'=>'收件人手机号',
	    'address'=>'收件人地址',
	], $values, $title, FALSE); 
	if($url){
		return json_success(['data'=>get_express_home_url().$url]);
	}else{
		return json_error(['msg'=>'生成excel失败']);
	}

}else{ 
	$all = db_pager("express_order",$where);
	$current_page = $all['current_page'];
	$index = ($current_page - 1)*$per_page; 
	foreach($all['data'] as &$v){
		$index++;
		$v['index']  = $index; 
		get_express_order_row($v);
	}
	$status = express_status_list_el_select();
	$all['status'] = $status;
	$all['where'] = $where;
	return json_success($all);
}


