<?php
/**
 * Created by PhpStorm.
 * User: Alvin Tang
 * Date: 2021/6/10
 * Time: 10:11
 * Email: pingtang000@foxmail.com
 * Desc: 消息体
 */

namespace MessageNotify;


class MessageBody
{
	private $title			=	'';
	private $type			=	E_ERROR;
	private $line			=	0;
	private $file			=	'';
	private $time			=	0;
	private $message		=	'';
	private $requestUrl		=	'';
	private $requestBody	=	array();
	private $customData		=	'';
	private $userAgent		=	'';
	private $clientIp		=	'';

	/**
	 * 获取WEB端IP
	 * @return string
	 */
	private function getWebUserIp(){
		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$ip  = $_SERVER["HTTP_X_FORWARDED_FOR"];
			$ips = explode(',', $ip);//阿里cdn
			$ip  = $ips[0];
		} elseif (isset($_SERVER["HTTP_CDN_SRC_IP"])) {
			$ip = $_SERVER["HTTP_CDN_SRC_IP"];
		} elseif (getenv('HTTP_CLIENT_IP')) {
			$ip = getenv('HTTP_CLIENT_IP');
		} elseif (getenv('HTTP_X_FORWARDED')) {
			$ip = getenv('HTTP_X_FORWARDED');
		} elseif (getenv('HTTP_FORWARDED_FOR')) {
			$ip = getenv('HTTP_FORWARDED_FOR');
		} elseif (getenv('HTTP_FORWARDED')) {
			$ip = getenv('HTTP_FORWARDED');
		} else {
			$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
		}
		$ip = str_replace(array('::ffff:', '[', ']'), array('', '', ''), $ip);

		return $ip;
	}
	/**
	 * @return string
	 */
	public function getUserAgent()
	{
		$userAgent	=	$this->userAgent;
		if(empty($userAgent) && !$this->isCli()){
			$userAgent	=	isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		}
		return $userAgent;
	}

	/**
	 * @param string $userAgent
	 */
	public function setUserAgent($userAgent)
	{
		$this->userAgent = $userAgent;
	}
	/**
	 * @return string
	 */
	public function getClientIp()
	{
		$clientIp	=	$this->clientIp;
		if(empty($clientIp) && !$this->isCli()){
			$clientIp	=	$this->getWebUserIp();
		}
		return $clientIp;
	}

	/**
	 * @param string $clientIp
	 */
	public function setClientIp($clientIp)
	{
		$this->clientIp = $clientIp;
	}

	/**
	 * @return string
	 */
	public function getCustomData()
	{
		return $this->customData;
	}

	/**
	 * 客户自定义数据
	 * @param string $customData
	 */
	public function setCustomData($customData)
	{
		$this->customData = $customData;
	}
	/**
	 * @return array
	 */
	public function getRequestBody()
	{
		$requestBody		=	$this->requestBody;
		if(empty($requestBody)){
			$requestBody	=	json_encode($_POST);
		}
		return $requestBody;
	}

	/**
	 * @param array $requestBody
	 */
	public function setRequestBody($requestBody)
	{
		$this->requestBody = $requestBody;
	}
	/**
	 * 是否终端
	 * @return bool
	 */
	private function isCli(){
		return	php_sapi_name()	==	'cli';
	}
	/**
	 * @return string
	 */
	public function getRequestUrl()
	{
		$requestUrl		=	$this->requestUrl;
		if(empty($requestUrl)){
			if(!$this->isCli()){
				$scheme		=	isset($_SERVER['HTTP_X_CLIENT_SCHEME']) ? $_SERVER['HTTP_X_CLIENT_SCHEME'] : 'http';
				$host		=	isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
				$path		=	isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
				$requestUrl	=	sprintf('%s://%s%s', $scheme, $host, $path);
			}
		}
		return	$requestUrl;
	}

	/**
	 * @param string $requestUrl
	 */
	public function setRequestUrl($requestUrl)
	{
		$this->requestUrl = $requestUrl;
	}
	/**
	 * @return int
	 */
	public function getTime()
	{
		$time			=	$this->time;
		if($time	<=	0){
			$time		=	time();
		}
		return $time;
	}

	/**
	 * @param int $time
	 */
	public function setTime($time)
	{
		$this->time = $time;
	}
	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}
	/**
	 * @return string
	 */
	public function getFile()
	{
		$file	=	$this->file;
		if(empty($file)){
			if(isset($_SERVER['SCRIPT_FILENAME'])){
				$file	=	$_SERVER['SCRIPT_FILENAME'];
			}
		}
		return $file;
	}

	/**
	 * @param string $file
	 */
	public function setFile($file)
	{
		$this->file = $file;
	}
	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @param string $message
	 */
	public function setMessage($message)
	{
		$this->message = $message;
	}
	/**
	 * @return int
	 */
	public function getLine()
	{
		return $this->line;
	}

	/**
	 * @param int $line
	 */
	public function setLine($line)
	{
		$this->line = $line;
	}
	/**
	 * @return int
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param int $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * 转化成数组
	 * @return array
	 */
	public function toArray(){
		return	array(
			'type'			=>	$this->getType(),
			'title'			=>	$this->getTitle(),
			'file'			=>	$this->getFile(),
			'line'			=>	$this->getLine(),
			'message'		=>	$this->getMessage(),
			'request_url'	=>	$this->getRequestUrl(),
			'request_body'	=>	$this->getRequestBody(),
			'time'			=>	$this->getTime(),
			'user_agent'	=>	$this->getUserAgent(),
			'client_ip'		=>	$this->getClientIp(),
			'custom_data'	=>	$this->getCustomData(),
		);
	}

	/**
	 * 转化成字符串
	 * @return string
	 */
	public function toString(){
		return	json_encode($this->toArray());
	}
}