<?php
/**
 * Created by PhpStorm.
 * User: Alvin Tang
 * Date: 2021/6/10
 * Time: 10:09
 * Email: pingtang000@foxmail.com
 * Desc: 消息通知
 */

namespace MessageNotify;


class MessageNotify
{
	/**
	 * 单例实体
	 * @var null
	 */
	private static $singleInstance		=	null;
	private $projectId					=	0;
	private $key						=	'';
	private $errors						=	array();

	/**
	 * 单例
	 * @return null|static
	 */
	static function singleInstance(){
		if(empty(self::$singleInstance)){
			self::$singleInstance	=	new static();
		}
		return	self::$singleInstance;
	}

	/**
	 * @return array
	 */
	private function getErrors()
	{
		return $this->errors;
	}

	/**
	 * 获取通知地址
	 * @return string
	 */
	private function getNotifyUrl(){
		return	'https://open-api.51baocuo.com/message/notify';
	}
	/**
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @param string $key
	 * @return $this
	 */
	public function setKey($key)
	{
		$this->key = $key;
		return	$this;
	}

	/**
	 * @return int
	 */
	public function getProjectId()
	{
		return $this->projectId;
	}

	/**
	 * @param int $projectId
	 * @return $this
	 */
	public function setProjectId($projectId)
	{
		$this->projectId = $projectId;
		return	$this;
	}

	/**
	 * 构造函数
	 * MessageNotify constructor.
	 */
	function __construct()
	{
	}

	/**
	 * 清除数据
	 */
	private function clearData(){
		$this->errors	=	array();
	}

	/**
	 * 推送所有数据
	 */
	public function push(){
		$errors	=	$this->getErrors();
		$this->clearData();
		if($errors &&	count($errors) > 0){
			$this->notify($errors);
		}
	}

	/**
	 * 构建加密字符串
	 * @param $params
	 * @return string
	 */
	private function buildSignStr($params){
		ksort($params);
		$connects	=	[];
		foreach ($params as $key => $value){
			array_push($connects, sprintf('%s=%s', $key, $value));
		}
		return	implode('&', $connects);
	}

	/**
	 * 发送通知
	 * @param array $data
	 * @return bool
	 */
	private function notify($data){
		$time		=	time();
		$nonce		=	md5(uniqid() . time() . rand());
		$headers	=	array(
			'Content-Type: application/json',
		);

		$aesKey				=	$this->getAesKey();
		$aesEncryptData		=	self::aesEncrypt(json_encode($data), $aesKey);

		$params				=	[
			'time'			=>	$time,
			'nonce'			=>	$nonce,
			'project_id'	=>	$this->getProjectId(),
			'messages'		=>	$aesEncryptData,
		];

		$signStr			=	$this->buildSignStr($params);
		$params['sign']		=	hash_hmac('sha256', $signStr, $this->getKey());

		return	$this->curlPost($this->getNotifyUrl(), json_encode($params), 3, $headers);
	}

	/**
	 * aes-128-ecb 加密
	 * @param $data
	 * @param $key
	 * @return string
	 */
	private static function aesEncrypt($data, $key) {
		$data =  openssl_encrypt($data, 'aes-128-ecb', $key, OPENSSL_RAW_DATA);
		return base64_encode($data);
	}

	/**
	 * 获取AES KEY
	 * @return string
	 */
	private function getAesKey(){
		$key		=	$this->getKey();
		$payLength	=	16;
		if(strlen($key) < $payLength){
			return	str_pad($key, $payLength, '0', STR_PAD_RIGHT);
		}else{
			return	substr($key, 0, $payLength);
		}
	}

	/**
	 * 追加异常数据
	 * @param MessageBody $messageBody
	 * @return $this
	 */
	function addMessage($messageBody){
		$title	=	$messageBody->getTitle();
		$title	=	empty($title) ? '手动发送消息' : $title;
		$messageBody->setTitle($title);
		array_push($this->errors,	$messageBody->toArray());
		return	$this;
	}

	/**
	 * 手动触发异常通知
	 * @param Exception|\Exception $exception
	 * @return $this
	 */
	function	exception($exception){
		$myOpenErrorBody	=	new MessageBody();
		$myOpenErrorBody->setTitle('触发异常');
		$myOpenErrorBody->setType(E_ERROR);
		$myOpenErrorBody->setMessage(sprintf("错误消息%s\t堆栈:%s", $exception->getMessage(), $exception->getTraceAsString()));
		$myOpenErrorBody->setFile($exception->getFile());
		$myOpenErrorBody->setLine($exception->getLine());
		$this->addMessage($myOpenErrorBody);
		return	$this;
	}

	/**
	 *	异常对象转换为消息体
	 * @param Exception|\Exception $exception
	 * @return MessageBody
	 */
	static function exception2MessageBody($exception){
		$myOpenErrorBody	=	new MessageBody();
		$myOpenErrorBody->setTitle('触发异常');
		$myOpenErrorBody->setType(E_ERROR);
		$myOpenErrorBody->setMessage(sprintf("错误消息%s\t堆栈:%s", $exception->getMessage(), $exception->getTraceAsString()));
		$myOpenErrorBody->setFile($exception->getFile());
		$myOpenErrorBody->setLine($exception->getLine());
		return	$myOpenErrorBody;
	}

	/**
	 * 发送网络请求
	 * @param $url
	 * @param $data
	 * @param int $timeout
	 * @param array $headers
	 * @return bool
	 */
	private function curlPost($url,	$data, $timeout = 3, $headers	=	array()){
		try{
			$curl = curl_init();
			curl_setopt($curl, 	CURLOPT_URL, $url);
			curl_setopt($curl,	CURLOPT_POST,	1);
			curl_setopt($curl, 	CURLOPT_TIMEOUT, $timeout);
			curl_setopt($curl,	CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl,	CURLOPT_POSTFIELDS, $data);
			curl_setopt($curl,	CURLOPT_HTTPHEADER, $headers);
			curl_exec($curl);
			curl_close($curl);
			return	true;
		}catch (\Exception $exception){
			return false;
		}
	}

	/**
	 * 析构函数
	 */
	function __destruct()
	{
		$this->push();
	}
}