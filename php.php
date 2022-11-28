<?php
/*******
作者逸 QQ1626424216 
*******/
$userid="";
//用户ID 手机号
$passwd="";
//用户密码
$lapiurl="https://api.sanfengyun.com/www/login.php";
//登陆更新cookie接口 自行抓包获取，目前支持三丰云，阿贝云，优豆云
$lapiurl1="https://api.abeiyun.com/www/login.php";
$lapiurl2="https://api.udouyun.com/www/login.php";
$gapiurl = "https://api.sanfengyun.com/www/vps.php";
//获取vps状态接口 自行抓包获取，目前支持三丰云，阿贝云，优豆云
$gapiurl1 = "https://api.abeiyun.com/www/vps.php";
$gapiurl2 = "https://api.udouyun.com/www/vps.php";
$lapiurl=$lapiurl;
//获取cookie链接
$gapiurl=$gapiurl;
//获取vps信息链接
/***********函数区域**********/
echo mainrun();
function mainrun() {
	$get_d=json_decode(getinfo());
	$get_e=json_encode($get_d,true);
	if ($get_d->code==0) {
		if ($get_d->info->msg->content[0]->ip=="") {
			return json_encode(array("code"=>"0","msg"=>"账号正常，但没有产品","info"=>"账号正常，但没有产品"));
		} else {
			return json_encode(array("code"=>"0","msg"=>"一切正常","info"=>'主机ID:'.$get_d->info->msg->content[0]->vm_id.'\nCPU:'.$get_d->info->msg->content[0]->CPU.'核心 内存:'.$get_d->info->msg->content[0]->RAM.'GB 硬盘:'.$get_d->info->msg->content[0]->SSD.'GB 网络:'.$get_d->info->msg->content[0]->NET_TYPE.'Mbps \n服务器IP:'.$get_d->info->msg->content[0]->ip.'\n服务器系统:'.$get_d->info->msg->content[0]->OsType.'\n创建时间:'.$get_d->info->msg->content[0]->StartTime.'\n到期时间:'.$get_d->info->msg->content[0]->EndTime.'\n剩余时间'.$get_d->info->msg->content[0]->left_time));
			//正常cookie
		}
	} else if ($get_d->code==-1) {
		$upcookie=json_decode(upcookie(),true);
		if ($upcookie['code']==3) {
			exit(mainrun());
		}
	} else if ($get_d->code==-2) {
		exit($get_e);
	} else {
		exit(json_encode(array("code"=>"-7","msg"=>"未知错误","info"=>"")));
	}
}
function getinfo() {
	global $gapiurl;
	$post_data = array("cmd" => "vps_list","vps_type" => "free");
	//执行的json提交post
	$cookiefile=@file_get_contents(".".md5($_SERVER['DOCUMENT_ROOT']).".cachecookie.txt");
	//cookie文件，自定义文件，当前为隐藏文件+MD5 较安全
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $gapiurl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_COOKIE, $cookiefile);
	$output = curl_exec($ch);
	curl_close($ch);
	//$data = json_decode($output, true);
	$data = json_decode(trim($output,chr(239).chr(187).chr(191)),true);
	if ($data['response']=="50140") {
		return json_encode(array("code"=>"-1","msg"=>"账号cookie过期","info"=>""));
		//失效cookie
	} else if ($data['response']=="200") {
		return json_encode(array("code"=>"0","msg"=>"账号一切正常","info"=>$data));
		//正常cookie
	} else {
		return json_encode(array("code"=>"-2","msg"=>"无法使用cookie/无法解析账号/疑似封号/疑似黑名单","info"=>""));
		//未知错误/获取失败/封号/网络异常等等
	}
}
function upcookie() {
	/*
 * 模拟post请求 函数 解析
 */
	global $userid;
	global $passwd;
	global $lapiurl;
	function post_curl($url, $params=[], $headers=[]) {
		global $userid;
		global $passwd;
		global $lapiurl;
		$httpInfo = array();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
		curl_setopt( $ch, CURLOPT_USERAGENT , 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36' );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 30 );
		curl_setopt( $ch, CURLOPT_TIMEOUT , 30);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
		curl_setopt( $ch , CURLOPT_POST , true );
		curl_setopt( $ch , CURLOPT_POSTFIELDS , http_build_query($params));
		curl_setopt( $ch , CURLOPT_URL , $url );
		$response = curl_exec( $ch );
		if ($response === FALSE) {
			return false;
		}
		curl_close( $ch );
		return $response;
	}
	$cookiefile=".".md5($_SERVER['DOCUMENT_ROOT']).".cachecookie.txt";
	//cookie文件，自定义文件，当前为隐藏文件+MD5 较安全
	$get=explode("\r\n\r\n",post_curl($lapiurl, array("cmd" => "login","id_mobile" => $userid,"password"=>$passwd), 1));
	// 提交并解析COOKIE
	preg_match("/set\-cookie:([^\r\n]*)/i", $get[0], $matches);
	if (json_decode($get[1],true)['response']=="200") {
		@file_put_contents($cookiefile, explode(';', $matches[1])[0]);
		//写入cookie文件
		return json_encode(array("code"=>"3","msg"=>"账号登录成功，cookie更新成功","info"=>""));
		//登陆成功
	} else if(strpos(json_decode($get[1],true)['response'],"500103") !== false) {
		return json_encode(array("code"=>"-3","msg"=>"密码错误，cookie更新失败","info"=>""));
		//密码错误
	} else if(strpos(json_decode($get[1],true)['response'],"500101") !== false) {
		return json_encode(array("code"=>"-4","msg"=>"用户不存在，cookie更新失败","info"=>""));
		//用户名不存在
	} else {
		return json_encode(array("code"=>"-5","msg"=>"无法更新cookie/用户被封禁/无法解析用户数据","info"=>""));
		//未知错误，疑似被封号
	
