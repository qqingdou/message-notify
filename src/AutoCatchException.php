<?php
/**
 * Created by PhpStorm.
 * User: Alvin Tang
 * Date: 2021/6/10
 * Time: 10:14
 * Email: pingtang000@foxmail.com
 * Desc: 自动捕获异常
 */

namespace MessageNotify;


class AutoCatchException
{
	/**
	 * 是否已经初始化
	 * @var bool
	 */
	private static $isInit				=	false;

	/**
	 * 初始化
	 * @param $projectId
	 * @param $key
	 */
	static function init($projectId,	$key){
		MessageNotify::singleInstance()
			->setProjectId($projectId)
			->setKey($key);
		self::registerErrorHandler();
	}

	/**
	 * 注册异常处理
	 */
	private static function registerErrorHandler(){
		if(!self::$isInit){

			ini_set('display_errors', false);
			set_exception_handler(function ($exception){
				$myOpenErrorBody	=	new MessageBody();
				$myOpenErrorBody->setTitle('自动捕获异常-set_exception_handler');
				$myOpenErrorBody->setType(E_ERROR);
				$myOpenErrorBody->setMessage(sprintf("错误消息%s\t堆栈:%s", $exception->getMessage(), $exception->getTraceAsString()));
				$myOpenErrorBody->setFile($exception->getFile());
				$myOpenErrorBody->setLine($exception->getLine());
				MessageNotify::singleInstance()->addMessage($myOpenErrorBody);
			});
			set_error_handler(function ($code, $message, $file, $line){
				$myOpenErrorBody	=	new MessageBody();
				$myOpenErrorBody->setTitle('自动捕获异常-set_error_handler');
				$myOpenErrorBody->setType($code);
				$myOpenErrorBody->setMessage($message);
				$myOpenErrorBody->setFile($file);
				$myOpenErrorBody->setLine($line);
				MessageNotify::singleInstance()->addMessage($myOpenErrorBody);
			});
			register_shutdown_function(function (){
				$error			=	error_get_last();
				if($error){
					$myOpenErrorBody	=	new MessageBody();
					$myOpenErrorBody->setTitle('异常自动捕获');
					$myOpenErrorBody->setType(isset($error['type']) ? $error['type'] : 0);
					$myOpenErrorBody->setMessage((isset($error['message']) ? $error['message'] : ''));
					$myOpenErrorBody->setFile(isset($error['file']) ? $error['file'] : '');
					$myOpenErrorBody->setLine(isset($error['line']) ? $error['line'] : '');
					MessageNotify::singleInstance()->addMessage($myOpenErrorBody);
				}
			});
			self::$isInit	=	true;
		}
	}
}