<?php
/**
 * Created by PhpStorm.
 * FileName: ConstStatus.php
 * User: Administrator
 * Date: 2017/10/28
 * Time: 13:38
 */

namespace app\api\library;

class ConstStatus {


    //公共状态
    const Msg_001 = '服务器内部错误，请联系系统管理员～～';
    private static $setMap = [
        //1xxx
        1000 => '请求成功',
    ];

    /**
     * Notes: 获取提示信息
     * User: jackin.chen
     * Date: 2021/7/31 3:13 下午
     * function: getMsg
     * @param $status
     * @return string
     * @static
     */
    public  static function getMsg($status){
        return !empty(self::$setMap[$status]) ? self::$setMap[$status] : self::Msg_001;
    }


    //定义返回键名
    const RESPONSE_STATUS = 'status';
    const RESPONSE_CODE = 'code';
    const RESPONSE_DESC = 'desc';
    const RESPONSE_API = 'api';
    const RESPONSE_DATA = 'data';





    const CODE_99999 = 99999;
    //状态码
    const CODE_SUCCESS = 10000;
    const CODE_ERROR = 10001;
    const DESC_SUCCESS = '请求成功';
    const DESC_ERROR = '请求异常';
    const DESC_CACHE = '服务器缓存内部错误';
    const DESC_USER = '用户不存在';
    const DESC_USER_ID = '参数中包含有非法的参数名user_id或者uid';
    const DESC_ADDRESS_COUNT = '保存地址已达到上限';
    const DESC_TOKEN_EMPTY = 'TOKEN不允许为空';
    const DESC_CACHE_EMPTY = '尝试获取的Token变量并不存在';
    const DESC_ADDRESS_EMPTY = '用户地址不存在';
    const DESC_DEFAULT = '设置默认地址失败';
    const VOTE_SUCCESS = '投票成功';
    const VOTE_ERROR = '再试一次';
    const VOTE_TIME_ERROR = '活动已结束';
    const VOTE_TIME_PLAYER = '已超过每天可投选手数';
    const VOTE_LIMIT_PLAYER = '已超过每天同一选手投票数';
    const VOTE_NUM_PLAYER = '已超过每天可投票的次数';
    const VOTE_MAX_TIME_PLAYER = '已超过您在活动最大可投选手数';
    const VOTE_MAX_LIMIT_PLAYER = '已超过您在活动最大为同一选手投票数';
    const VOTE_MAX_NUM_PLAYER = '已超过您在活动最大可投票的次数';


}