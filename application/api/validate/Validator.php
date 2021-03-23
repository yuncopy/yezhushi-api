<?php


namespace app\api\validate;

use think\Request;
use think\Validate;
use app\api\library\ConstStatus;
use app\api\exception\ParameterException;

/**
 *
 * 基类定义了很多自定义验证方法
 * 这些自定义验证方法其实，也可以直接调用
 *
 * @throws ParameterException
 * @return true
 * Class BaseValidate
 * 验证类的基类
 */
class Validator extends Validate
{

    /**
     * Notes: 检测所有客户端发来的参数是否符合验证类规则
     * User: jackin.chen
     * Date: 2020/6/5 下午10:22
     * function: goCheck
     * @return bool
     * @throws ParameterException
     */
    public function doValidate()
    {
        //必须设置contetn-type:application/json

        $request = Request::instance();
        $params = $request->param();
        if (!$this->check($params)) {
            $error  = $this->getError();
            $message = is_array($error) ? implode(';', $error) : $error;
            throw new ParameterException([
                ConstStatus::RESPONSE_CODE => ConstStatus::CODE_ERROR,
                ConstStatus::RESPONSE_DESC => $message
            ]);
        }
        return true;
    }


    /**
     * Notes: 通常传入request.post变量数组
     * User: jackin.chen
     * Date: 2020/6/13 下午10:42
     * function: getDataByRule
     * @param array $arrays 按照规则key过滤后的变量数组
     * @return array
     * @throws ParameterException
     */
    public function getDataByRule($arrays=[])
    {
        if (array_key_exists('user_id', $arrays)
            | array_key_exists('uid', $arrays)
           // | array_key_exists('userId', $arrays)
        ){
            // 不允许包含user_id或者uid，防止恶意覆盖user_id外键
            throw new ParameterException([
                ConstStatus::RESPONSE_CODE => ConstStatus::CODE_ERROR,
                ConstStatus::RESPONSE_DESC => ConstStatus::DESC_USER_ID
            ]);
        }
        $newArray = [];
        foreach ($this->rule as $key => $value) {
            $newArray[$key] = $arrays[$key];
        }
        return $newArray;
    }

}