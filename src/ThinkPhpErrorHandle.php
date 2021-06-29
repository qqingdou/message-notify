<?php
/**
 * Created by PhpStorm.
 * User: Alvin Tang
 * Date: 2021/6/28
 * Time: 18:08
 * Email: pingtang000@foxmail.com
 * Desc: ThinkPhp 异常接管
 */

namespace MessageNotify;

use Exception;
use MessageNotify\MessageNotify;
use think\console\Output;
use think\exception\Handle;

class ThinkPhpErrorHandle extends Handle
{
	public function render(Exception $e)
	{
		MessageNotify::singleInstance()->exception($e);

		return parent::render($e);
	}

	public function renderForConsole(Output $output, Exception $e)
	{
		MessageNotify::singleInstance()->exception($e);

		parent::renderForConsole($output, $e);
	}
}