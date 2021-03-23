<?php

namespace app\api\library;

use think\Exception;
use app\common\model\Users as UsersModel;
use app\api\exception\WeChatException;
use app\api\exception\TokenException;

/**
 * 微信登录
 * 如果担心频繁被恶意调用，请限制ip
 * 以及访问频率
 */
class UserToken extends Token
{
    protected  $code;
    protected  $wxLoginUrl;
    protected  $wxAppID;
    protected  $wxAppSecret;

    protected  $userModel;
    /**
     * 构造函数
     *
     * UserToken constructor.
     * @param $code
     */
    public function __construct($code='')
    {
        $this->code = $code;
        $this->wxAppID = config('app_id');
        $this->wxAppSecret = config('app_secret');
        $this->wxLoginUrl = sprintf(
            config('login_url'), $this->wxAppID, $this->wxAppSecret, $this->code);

        $this->userModel = new UsersModel();
    }


    /**
     * 思路1：每次调用登录接口都去微信刷新一次session_key，生成新的Token，不删除久的Token
     * 思路2：检查Token有没有过期，没有过期则直接返回当前Token
     * 思路3：重新去微信刷新session_key并删除当前Token，返回新的Token
     * User: jackin.chen
     * Date: 2020/6/2 下午10:35
     * function: get
     * @return mixed
     * @static
     * @throws Exception
     */
    public function getToken()
    {

        $result = curl_request($this->wxLoginUrl);
        $wxResult = json_decode($result, true);
        if (empty($wxResult)) {
            // 为什么以empty判断是否错误，这是根据微信返回
            // 规则摸索出来的
            // 这种情况通常是由于传入不合法的code
            // 不需要返回给客户端，记录日记即可
            throw new WeChatException([
                ConstStatus::RESPONSE_CODE => ConstStatus::CODE_ERROR,
                ConstStatus::RESPONSE_DESC => '获取session_key及openID时异常，微信内部错误'
            ]);
        } else {
            // 建议用明确的变量来表示是否成功
            // 微信服务器并不会将错误标记为400，无论成功还是失败都标记成200
            // 这样非常不好判断，只能使用errcode是否存在来判断
            // errcode: 40029, errmsg: "invalid code, hints: [ req_id: HQd79a0747th31 ]
            // 进一步借鉴网上的一些经验提示，需要对应替换掉小程序 project.config.json 文件中的 appid 信息
            $loginFail = array_key_exists('errcode', $wxResult);
            if ($loginFail) {
                throw new WeChatException([
                    ConstStatus::RESPONSE_CODE => ConstStatus::CODE_ERROR,
                    ConstStatus::RESPONSE_DESC => $wxResult['errcode'].':'.$wxResult['errmsg']
                ]);
            }
            else {
                return $this->grantToken($wxResult);
            }
        }
    }

    // 颁发令牌
    // 只要调用登陆就颁发新令牌
    // 但旧的令牌依然可以使用
    // 所以通常令牌的有效时间比较短
    // 目前微信的express_in时间是7200秒
    // 在不设置刷新令牌（refresh_token）的情况下
    // 只能延迟自有token的过期时间超过7200秒（目前还无法确定，在express_in时间到期后
    // 还能否进行微信支付
    // 没有刷新令牌会有一个问题，就是用户的操作有可能会被突然中断
    /**
     * Notes: 颁发令牌生产缓存
     * User: jackin.chen
     * Date: 2020/6/6 下午9:41
     * function: grantToken
     * @param $wxResult
     * @return string
     * @throws TokenException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function grantToken($wxResult)
    {
        // 此处生成令牌使用的是TP5自带的令牌
        // 如果想要更加安全可以考虑自己生成更复杂的令牌
        // 比如使用JWT并加入盐，如果不加入盐有一定的几率伪造令牌
        // $token = Request::instance()->token('token', 'md5');
        $openid = $wxResult['openid'];
        $user = $this->userModel->getByOpenID($openid);
        if (!$user) {
            // 借助微信的openid作为用户标识
            // 但在系统中的相关查询还是使用自己的uid
            $uid = $this->userModel->newUser($openid);
        }
        else {
            $uid = $user->id;
        }
        $cachedValue = $this->prepareFormat($wxResult, $uid);
        $token = $this->saveTokenCache($cachedValue);
        return $token;
    }


    /**
     * Notes: 格式化缓存数据
     * User: jackin.chen
     * Date: 2020/6/6 下午9:08
     * function: prepareValue
     * @param $wxResult
     * @param $uid
     * @return mixed
     */
    private function prepareFormat($wxResult, $uid)
    {
        $cachedValue = $wxResult;
        $cachedValue[ScopeEnum::UID_KEY] = $uid; //系统参数
        $cachedValue[ScopeEnum::SCOPE_KEY] = ScopeEnum::User; //权限参数
        return $cachedValue;
    }

    /**
     * Notes: 生成token缓存
     * User: jackin.chen
     * Date: 2020/6/6 下午9:30
     * function: saveTokenCache
     * @param $wxResult
     * @return string
     * @throws TokenException
     */
    private function saveTokenCache($wxResult)
    {
        $token = self::generateToken();
        $value = json_encode($wxResult);
        $expire_in = config('token_expire_in');
        $result = cache($token, $value, $expire_in);

        if (!$result){
            throw new TokenException([
                ConstStatus::RESPONSE_CODE => ConstStatus::CODE_ERROR,
                ConstStatus::RESPONSE_DESC =>ConstStatus:: DESC_CACHE
            ]);
        }
        return $token;
    }


    /**
     * Notes: 验证TOKEN是否存在
     * User: jackin.chen
     * Date: 2020/6/15 上午2:03
     * function: verifyToken
     * @param $token
     * @return bool
     */
    public  function verifyToken($token)
    {
        $exist = Cache($token);
        if($exist){
            return true;
        } else{
            return false;
        }
    }



}
