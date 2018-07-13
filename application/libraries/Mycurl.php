<?php
/**
 *
 * @authors Your Name (you@example.org)
 * @date    2018-07-12 19:41:47
 * @version $Id$
 */

class Mycurl {

	/**
	 * @param $url
	 * @return mixed
	 */
	public function curlGet($url) {
		// 1. 初始化
		$ch = curl_init();
		// 2. 设置选项，包括URL
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		// 3. 执行并获取HTML文档内容
		$output = curl_exec($ch);
		if ($output === FALSE) {
			echo "CURL Error:" . curl_error($ch);
		}
		// 4. 释放curl句柄
		curl_close($ch);
		return $output;
	}

	/**
	 * @param $url
	 * @param $postData
	 * @return mixed
	 */
	public function curlPost($url, $postData = null) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		$ch_arr = array(CURLOPT_TIMEOUT => 3, CURLOPT_RETURNTRANSFER => 1);
		curl_setopt_array($ch, $ch_arr);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	/**
	 * @param $URL
	 * @param $type
	 * @param $params
	 * @param null $headers
	 * @return mixed
	 */
	public function curlRequest($URL, $type, $params = null, $headers = null) {
		$ch = curl_init($URL);
		$timeout = 1500; //请求超时时间15秒
		//判断ssl连接方式
		if (stripos($URL, 'https://') !== false) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSLVERSION, 1);
		}

		//请求头信息
		if (isset($headers)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		} else {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		//请求超时时间
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

		//请求方式
		switch ($type) {
		case "GET":curl_setopt($ch, CURLOPT_HTTPGET, true);
			break;
		case "POST":curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			break;
		case "PUT":curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			break;
		case "PATCH":curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			break;
		case "DELETE":curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			break;
		}
		$file_contents = curl_exec($ch); //获得返回值
		curl_close($ch);
		return $file_contents;
	}
}