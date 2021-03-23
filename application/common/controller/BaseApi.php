<?php
/**
 * Created by PhpStorm.
 * FileName: BaseApi.php
 * User: Administrator
 * Date: 2017/10/28
 * Time: 10:35
 */

namespace app\common\controller;


use think\Exception;
use fast\Random;
use think\Config;
use think\Request;
use think\Response;
use app\api\library\ConstStatus;
use app\api\exception\TokenException;
use app\api\exception\ParameterException;
use app\api\library\ScopeEnum;

class BaseApi
{

    protected $allowMethod = null;

    public function __construct(Request $request)
    {
        error_reporting(0);
        $this->validateAllowMethod($request);
    }

    public function _empty(Request $request)
    {
        return $this->response([], ConstStatus::CODE_ERROR, sprintf('无效的操作 - %s', $request->action()));
    }

    /**
     * 输出
     * @param array $data 输出数据
     * @param string $code 状态码
     * @param string $desc 描述
     * @param string $type 输出类型|json|jsonp|redirect|view|xml
     * @return Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function response($data = [], $code = '', $desc = '', $type = 'json')
    {

        $response = array(
            ConstStatus::RESPONSE_CODE => $code,
            ConstStatus::RESPONSE_DESC => $desc,
            ConstStatus::RESPONSE_DATA => $data
        );
        return Response::create($response, $type);
    }

    /**
     * 获取自增的主键id
     * @param $model
     * @param string $pk
     * @return mixed
     */
    public function getAutoId($model, $pk = 'id')
    {
        return $model->$pk;
    }

    /**
     * 通用查询参数构造
     * @return array
     */
    protected function buildParams()
    {
        $request = Request::instance();
        //过滤条件 - 精确匹配
        //参数形式 filter[参数名]=参数值
        $where = (array)$request->request("filter/a");
        //排序
        //参数形式 sort[参数名]=参数值
        $order = (array)$request->request("sort/a");
        foreach ($where as $k => $v) {
            if (filter_var($k, FILTER_VALIDATE_INT) !== false) {
                unset($where[$k]);
            }
        }
        foreach ($order as $k => $v) {
            if (filter_var($k, FILTER_VALIDATE_INT) !== false) {
                unset($order[$k]);
            }
        }
        return array($where, $order);
    }

    /**
     * 验证必需字段
     * @param $requiredField
     * @param $params
     * @return Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    protected function validateField($requiredField, $params)
    {
        $result = '';
        foreach ($requiredField as $key => $item) {
            if (array_key_exists($key, $params) === false || !$params[$key]) {
                $result = $item;
                break;
            }
        }
        if ($result) {
            $response = array(
                'data' => [],
                'code' => ConstStatus::CODE_ERROR,
                'desc' => sprintf('%s不能为空', $result)
            );
            die(json($response)->send());
        }
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
            die(json($response)->send());
        }
    }


    /**
     * Notes: 管理缓存
     * // 设置缓存数据 cache('name', $value, 3600);
     * // 获取缓存数据 var_dump(cache('name'));
     * // 删除缓存数据 cache('name', NULL);
     * User: jackin.chen
     * Date: 2020/6/13 下午6:00
     * function: getOrSetCache
     * @param $key
     * @param $func
     * @param int $time
     * @return array|mixed
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
            $out_data = json_decode($redis_data, true);
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
                $vars = json_decode($vars, true);
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



    /**
     * Notes: 文件上传
     * User: jackin.chen
     * Date: 2020/6/26 下午5:52
     * function: fastUpload
     * @param $file
     * @return array
     * @throws ParameterException
     */
    public function fastUpload($file){

        if (empty($file)){
            throw new ParameterException([
                ConstStatus::RESPONSE_CODE => ConstStatus::CODE_ERROR,
                ConstStatus::RESPONSE_DESC => '未上传文件或超出服务器上传限制'
            ]);
        }
        //判断是否已经存在附件
        $sha1 = $file->hash();
        $upload = Config::get('upload');
        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int) $upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix ? $suffix : 'file';
        $replaceArr = [
            '{year}'     => date("Y"),
            '{mon}'      => date("m"),
            '{day}'      => date("d"),
            '{hour}'     => date("H"),
            '{min}'      => date("i"),
            '{sec}'      => date("s"),
            '{random}'   => Random::alnum(16),
            '{random32}' => Random::alnum(32),
            '{filename}' => $suffix ? substr($fileInfo['name'], 0, strripos($fileInfo['name'], '.')) : $fileInfo['name'],
            '{suffix}'   => $suffix,
            '{.suffix}'  => $suffix ? '.' . $suffix : '',
            '{filemd5}'  => md5_file($fileInfo['tmp_name']),
        ];
        $savekey = $upload['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
        $fileName = substr($savekey, strripos($savekey, '/') + 1);
        //
        $splInfo = $file->validate(['size' => $size])->move(ROOT_PATH . '/public' . $uploadDir, $fileName);
        if ($splInfo)
        {
            $imagewidth = $imageheight = 0;
            if (in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf']))
            {
                $imgInfo = getimagesize($splInfo->getPathname());
                $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
                $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
            }
            $params = array(
                'filesize'    => $fileInfo['size'],
                'imagewidth'  => $imagewidth,
                'imageheight' => $imageheight,
                'imagetype'   => $suffix,
                'imageframes' => 0,
                'mimetype'    => $fileInfo['type'],
                'url'         => $uploadDir . $splInfo->getSaveName(),
                'uploadtime'  => time(),
                'storage'     => 'local',
                'sha1'        => $sha1,
            );
            $attachment = model("attachment");
            $attachment->create(array_filter($params));
            \think\Hook::listen("upload_after", $attachment);

            //处理结果
            return ['ret'=> 0,'msg'=> '上传成功','url'=>$uploadDir . $splInfo->getSaveName()];
        }
        else
        {
            // 上传失败获取错误信息
            return ['ret'=>-1, 'msg'=>$file->getError(),'url'=>''];
        }
    }
}