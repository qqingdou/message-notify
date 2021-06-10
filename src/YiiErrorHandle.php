<?php
/**
 * Created by PhpStorm.
 * User: Alvin Tang
 * Date: 2021/6/10
 * Time: 10:12
 * Email: pingtang000@foxmail.com
 * Desc: YII 异常处理组件
 */

namespace MessageNotify;

use yii\base\ErrorHandler;

class YiiErrorHandle extends ErrorHandler
{
	protected function renderException($exception)
	{
		MessageNotify::singleInstance()->exception($exception);
	}
}