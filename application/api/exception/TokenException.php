<?php

namespace app\api\exception;

/**
 * token验证失败时抛出此异常 
 */
class TokenException extends CommonException
{
    public $status = 200;
    public $code = 10003;
    public $desc = 'Token已过期或无效Token';

}