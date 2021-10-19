<?php
/**
 * Created by PhpStorm.
 * FileName: Article.php
 * User: Administrator
 * Date: 2017/10/28
 * Time: 10:18
 */

namespace app\api\controller\v1;

use app\common\controller\WxsApi;
use app\api\library\UserToken;
use app\api\validate\CodeValidate;
use app\api\validate\UserValidate;
use app\api\library\ConstStatus;
use app\common\model\Users as UsersModel;
use app\api\exception\ParameterException;
use think\Request;

class User extends WxsApi {

    private $userModel = null;
    protected $allowMethod = array('get','post');

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->userModel = new UsersModel();
    }



    /**
     * Notes: 获取用户信息兑换token值
     * User: jackin.chen
     * Date: 2021/10/19 7:40 下午
     * function: getToken
     * @param Request $request
     * @return \think\response\Json
     */
    public function getToken(Request $request) {

        //统一验证器
        (new CodeValidate())->doValidate();

        //实际处理业务流程
        $code  = $request->post('code');
        $token = (new UserToken($code))->getToken();
        //处理结果 todo处理网络异常
        return self::json2([
            'token' => $token,  //不对外暴露用户相关信息
        ]);
    }


    /**
     * Notes: 验证TOKEN
     * User: jackin.chen
     * Date: 2021/10/19 7:41 下午
     * function: verifyToken
     * @param string $token
     * @return \think\response\Json
     */
    public function verifyToken($token='')
    {
        if(!$token){
            throw new ParameterException([
                ConstStatus::RESPONSE_CODE => ConstStatus::CODE_ERROR,
                ConstStatus::RESPONSE_DESC => ConstStatus::DESC_TOKEN_EMPTY
            ]);
        }
        $valid = (new UserToken())->verifyToken($token);
        return self::json2([
            'isValid' => $valid,  //不对外暴露用户相关信息
        ]);
    }


    /**
     * Notes: 更新用户信息
     * User: jackin.chen
     * Date: 2021/10/19 7:41 下午
     * function: modifyUser
     * @param Request $request
     * @return \think\response\Json
     */
    public function modifyUser(Request $request){

        //验证器
        $validate = new UserValidate();
        $validate->doValidate();

        //获取参数
        $params = $validate->getDataByRule($request->post());
        $params['userId'] = $this->getCurrentUid();
        $result = $this->userModel->modifyUser($params);

        //处理结果 todo处理网络异常
        return self::json2([
            'result' => $result,  //不对外暴露用户相关信息
        ]);
    }



    public function getMyInfo(Request $request){

        $data = [
            'userId'=>4945,
            'userScore'=>1,
            'userCode'=>3,
            'allScore'=>5,
            'orderNum'=>[
                ['cnt'=>1],
                ['cnt'=>2],
                ['cnt'=>3],
            ]
        ];
        return self::json2($data);
    }


}