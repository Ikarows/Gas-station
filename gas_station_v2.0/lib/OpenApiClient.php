<?php
/*
 * 会员营销系统开放平台 SKD V1.0.0
 * 此 SDK 适用于 PHP 5 及其以上版本。
 * 文本编码方式UTF-8
 * lib文件夹为SDK核心文件夹，包含了对OpenAPI接口的请求和数据的接收，OpenApiClient.php文件为核心文件
 *
 * LastupDate 2016-05-16
 */
class OpenApiClient {
	private $OpenId;
	private $Secret;
	private $xml;
	
	public function __construct($OpenId, $Secret) {
		$this->OpenId = $OpenId;
		$this->Secret = $Secret;
	}
	
	private function postData($url, $data) {
		$ch = curl_init ();
		$timeout = 300; // 设定超时时间
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
		$handles = curl_exec ( $ch );
		$httpCode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		if ($httpCode != 200) {
			if ($httpCode == 0) {
				return "{\"status\":-1,\"message\":\"网络连接失败！\"}";
			} else {
				return "{\"status\":-1,\"message\":\"网络或者服务器出现异常，HTTP返回状态码" . $httpCode . "\"}";
			}
		}
		curl_close ( $ch );
		return $handles;
	}
	
	public function CallHttpPost($action, $data) {
		if (empty ( $action )) {
			return array (
					"status" => - 1,
					"message" => "方法名不能为空！" 
			);
		}
		
		if (! isset ( $data ) || count ( $data ) == 0) {
			return array (
					"status" => - 1,
					"message" => "请求参数不能为空！" 
			);
		}
		
		$xmlPath = dirname ( __FILE__ ) . "\\OpenApiData.xml";
		
		if (! isset ( $this->xml )) {
			if (! file_exists ( $xmlPath )) {
				return array (
						"status" => - 1,
						"message" => "API配置文件不存在！" 
				);
			}
			$this->xml = simplexml_load_file ( $xmlPath );
		}
		
		$actionNode = $this->xml->xpath ( "//Action[@name='" . $action . "']" );
		if ($actionNode == null) {
			return array (
					"status" => - 1,
					"message" => "未能找到" . $action . "方法！" 
			);
		}
		
		$controllerName = $actionNode [0]->attributes ();
		$controllerName = $controllerName["controller"];
		$json_data = json_encode ( $data );
		$TimeStamp = time ();
		$Signature = strtoupper ( md5 ( $this->OpenId . $this->Secret . $TimeStamp . $json_data ) );
		$url = "http://openapi.1card1.cn/" . $controllerName . "/" . $action . "?openId=" . $this->OpenId . "&signature=" . $Signature . "&timestamp=" . $TimeStamp;
		$postData = "data=" . $json_data;
		$result_data = $this->postData ( $url, $postData );
		$array = json_decode ( $result_data, true );
		return $array;
	}
}
?>