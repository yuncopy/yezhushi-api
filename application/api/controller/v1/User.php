<?php
/**
 * Created by PhpStorm.
 * FileName: Article.php
 * User: Administrator
 * Date: 2017/10/28
 * Time: 10:18
 */

namespace app\api\controller\v1;

use app\common\controller\BaseApi;
use app\api\library\UserToken;
use app\api\validate\CodeValidate;
use app\api\validate\UserValidate;
use app\api\library\ConstStatus;
use app\common\model\Users as UsersModel;
use app\api\exception\ParameterException;
use think\Request;

class User extends BaseApi {

    private $userModel = null;
    protected $allowMethod = array('get','post');

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->userModel = new UsersModel();
    }


    /**
     * Notes: 获取用户信息兑换token值
     * User: jackin.chen
     * Date: 2020/6/6 下午9:47
     * function: getToken
     * @param Request $request
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \app\api\exception\ParameterException
     * @throws \think\Exception
     */
    public function getToken(Request $request) {

        //统一验证器
        (new CodeValidate())->doValidate();

        //实际处理业务流程
        $code  = $request->post('code');
        $token = (new UserToken($code))->getToken();
        //处理结果 todo处理网络异常
        return parent::response([
            'token' => $token,  //不对外暴露用户相关信息
        ],ConstStatus::CODE_SUCCESS,ConstStatus::DESC_SUCCESS);
    }

    /**
     * Notes: 验证TOKEN
     * User: jackin.chen
     * Date: 2020/6/15 上午2:07
     * function: verifyToken
     * @param string $token
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws ParameterException
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
        return parent::response([
            'isValid' => $valid,  //不对外暴露用户相关信息
        ],ConstStatus::CODE_SUCCESS,ConstStatus::DESC_SUCCESS);
    }

    /**
     * Notes: 更新用户信息
     * User: jackin.chen
     * Date: 2020/6/16 下午10:10
     * function: modifyUser
     * @param Request $request
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws ParameterException
     * @throws \app\api\exception\TokenException
     * @throws \think\exception\DbException
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
        return parent::response([
            'result' => $result
        ],ConstStatus::CODE_SUCCESS,ConstStatus::DESC_SUCCESS);
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
        return parent::response($data,ConstStatus::CODE_SUCCESS,ConstStatus::DESC_SUCCESS);
    }


}