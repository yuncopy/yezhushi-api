<?php

namespace app\api\exception;

/**
 * 用户验证失败时抛出此异常
 */
class UserException extends CommonException
{
    public $status = 200;
    public $code = 10003;
    public $desc = '用户地址不存在';

}