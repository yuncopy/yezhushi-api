<?php
namespace app\api\exception;

use think\Exception;
use app\api\library\ConstStatus;

/**
 * Class CommonException
 * 自定义异常类的基类
 */
class CommonException extends Exception
{
    public $status = 200;
    public $code = 10000;
    public $desc = 'invalid parameters';

    /**
     * 初始化辅值
     *
     * CommonException constructor.
     * @param array $params
     */
    public function __construct($params=[])
    {
        if(!is_array($params)){
            return;
        }

        //业务状态码
        if(array_key_exists(ConstStatus::RESPONSE_CODE,$params)){
            $this->code = $params[ConstStatus::RESPONSE_CODE];
        }
        //业务描述
        if(array_key_exists(ConstStatus::RESPONSE_DESC,$params)){
            $this->desc = $params[ConstStatus::RESPONSE_DESC];
        }
        //HTTP状态码
        if(array_key_exists(ConstStatus::RESPONSE_STATUS,$params)){
            $this->status = $params[ConstStatus::RESPONSE_STATUS];
        }
    }
}

