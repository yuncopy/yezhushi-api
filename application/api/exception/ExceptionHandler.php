<?php
/**
 * Created by PhpStorm.
 * User: 七月
 * Date: 2017/2/12
 * Time: 19:44
 */

namespace app\api\exception;

use think\exception\Handle;
use think\Log;
use think\Request;
use Exception;
use app\api\library\ConstStatus;

/*
 * 重写Handle的render方法，实现自定义异常消息
*/

class ExceptionHandler extends Handle
{
    private $status;
    private $code;
    private $desc;

    /**
     * Notes: 自定义异常处理
     * User: jackin.chen
     * Date: 2020/6/5 下午11:46
     * function: render
     * @param Exception $e
     * @return \think\Response|\think\response\Json
     */
    public function render(Exception $e)
    {

        if ($e instanceof CommonException) {
            //如果是自定义异常，则控制http状态码，不需要记录日志
            //因为这些通常是因为客户端传递参数错误或者是用户请求造成的异常
            //不应当记录日志
            $this->status = $e->status;
            $this->code = $e->code;
            $this->desc = $e->desc;
        } else {

            // 如果是服务器未处理的异常，将http状态码设置为500，并记录日志
            if (config('app_debug')) {
                // 调试状态下需要显示TP默认的异常页面，因为TP的默认页面
                // 很容易看出问题
                return parent::render($e);
            }

            $this->status = ConstStatus::STATUS_500; //HTTP状态码
            $this->desc = ConstStatus::DESC_500; //业务状态描述
            $this->code = ConstStatus::CODE_99999; //业务状态码
            $this->recordErrorLog($e);
        }
        $request = Request::instance();
        $result = [
            ConstStatus::RESPONSE_CODE => $this->code,
            ConstStatus::RESPONSE_DESC=> $this->desc,
            ConstStatus::RESPONSE_API => $request->url(),
            ConstStatus::RESPONSE_DATA => []
        ];
        return json($result, $this->status);
    }

    /*
     * 将异常写入日志
     */
    private function recordErrorLog(Exception $e)
    {
        Log::init([
            'type' => 'File',
            'path' => LOG_PATH,
            'level' => ['error']
        ]);
//        Log::record($e->getTraceAsString());
        Log::record($e->getMessage(), 'error');
    }
}