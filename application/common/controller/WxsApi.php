<?php
/**
 * Created by PhpStorm.
 * FileName: BaseApi.php
 * User: Administrator
 * Date: 2017/10/28
 * Time: 10:35
 */

namespace app\common\controller;


use think\Controller;
use think\Request;
use think\Response;
use app\api\library\ConstStatus;
use app\api\exception\TokenException;
use app\api\library\ScopeEnum;

class WxsApi extends Controller
{

    protected $allowMethod = null;
    public function __construct(Request $request)
    {
        parent::__construct($request); //调用父类沟通函数
        error_reporting(0);
        $this->validateAllowMethod($request);
    }

    /**
     * 验证当前请求是否允许
     * @param Request $request
     * @return bool
     */
    protected function validateAllowMethod(Request $request)
    {
        $method = $request->method();
        if ($this->allowMethod && !in_array(strtolower($method), $this->allowMethod)) {
            $response = array(
                'data' => [],
                'code' => ConstStatus::CODE_ERROR,
                'desc' => sprintf('非法请求 - %s', $method)
            );
            return (json($response)->send());
        }
    }



    protected function checkExclusiveScope()
    {

    }


    /**
     * Notes: JSON返回数据
     * User: jackin.chen
     * Date: 2021/7/31 3:19 下午
     * function: json2
     * @param array $data
     * @param int $status
     * @param int $code
     * @return \think\response\Json
     * @static
     */
    public static function  json2( array $data, $status = ConstStatus::CODE_SUCCESS,$code=200){

        $header = ['Content-Type' => 'application/json'];
        $options = ['json_encode_param' => JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE];
        $response = array(
            'code' => $status,
            'desc' => ConstStatus::getMsg($status),
            'data' => $data
        );
        return  Response::create($response, 'json', $code, $header, $options);
    }


    /**
     * Notes: 管理缓存
     * User: jackin.chen
     * Date: 2020/6/13 下午6:00
     * function: getOrSetCache
     * @param $key
     * @param $func
     * @param int $time
     * @return array|mixed
     * // 设置缓存数据 cache('name', $value, 3600);
     * // 获取缓存数据 var_dump(cache('name'));
     * // 删除缓存数据 cache('name', NULL);
     */
    public function getOrSetCache($key, $func, $time = 86400)
    {
        $out_data = [];
        if ($key && is_callable($func)) {
            $redis_data = cache($key);
            if (!$redis_data || is_null($redis_data)) {
                $data = $func();
                $redis_data = json_encode($data, JSON_UNESCAPED_UNICODE);
                cache($key, $redis_data, $time);
            }
            $out_data = json_decode($redis_data,true);
        }
        return $out_data;
    }

    /**
     * Notes: 获取当前请求token值
     * User: jackin.chen
     * Date: 2020/6/16 下午10:03
     * function: getCurrentHeaderToken
     * @return string
     */
    private function getCurrentHeaderToken(){

        return Request::instance()->header('token');
    }

    /**
     * Notes: 获取TOKEN对应的缓存值
     * User: jackin.chen
     * Date: 2020/6/16 下午10:00
     * function: getCurrentTokenVar
     * @param $key
     * @return mixed
     * @throws TokenException
     */
    public  function getCurrentTokenVar($key)
    {
        $token = $this->getCurrentHeaderToken();
        $vars = cache($token);
        if (!$vars) {
            throw new TokenException([
                ConstStatus::RESPONSE_CODE => ConstStatus::CODE_ERROR,
                ConstStatus::RESPONSE_DESC => ConstStatus:: DESC_CACHE
            ]);
        } else {
            if (!is_array($vars)) {
                $vars = json($vars);
            }
            if (array_key_exists($key, $vars)) {
                return $vars[$key];
            } else {
                throw new TokenException([
                    ConstStatus::RESPONSE_CODE => ConstStatus::CODE_ERROR,
                    ConstStatus::RESPONSE_DESC => ConstStatus:: DESC_CACHE_EMPTY
                ]);
            }
        }
    }

    /**
     * Notes: 当前用户ID
     * User: jackin.chen
     * Date: 2020/6/16 下午10:04
     * function: getCurrentUid
     * @return mixed
     * @throws TokenException
     */
    public  function getCurrentUid()
    {
        $uid = $this->getCurrentTokenVar(ScopeEnum::UID_KEY);
        return $uid;
    }
}