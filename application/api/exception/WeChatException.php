<?php

namespace app\api\exception;
use think\Exception;

/**
 * 微信服务器异常
 */
class WeChatException extends CommonException
{
    public $status = 200;
    public $code = 10002;
    public $desc = 'wechat unknown error';

}