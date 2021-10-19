<?php
/**
 * Created by PhpStorm.
 * FileName: Article.php
 * User: Administrator
 * Date: 2017/10/28
 * Time: 10:18
 */

namespace app\api\controller\v1;

use think\Request;
use think\Db;
use app\api\library\ConstStatus;
use app\common\controller\WxsApi;
use app\api\validate\UserAddressValidate;
use app\api\validate\AddressIdValidate;
use app\api\exception\UserException;
use app\api\exception\ParameterException;
use app\common\model\UserAddress as AddressModel;
use app\common\model\Users as userModel;


class Address extends WxsApi {

    private $addressModel = null;
    private $userModel = null;
    protected $allowMethod = array('get','post');

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->addressModel = new AddressModel();
        $this->userModel = new userModel();
    }


    /**
     * Notes: 获取用户地址
     * User: jackin.chen
     * Date: 2021/10/19 7:33 下午
     * function: getUserAddress
     * @param Request $request
     * @return \think\response\Json
     */
    public function getUserAddress(Request $request){

        $uid = $this->getCurrentUid();
        $where = ['user_id'=>$uid, 'delete_time'=>0];
        $param = $request->get();

        //设置过滤条件
        if(isset($param['is_default']) && !empty($param['is_default'])){
            $where['is_default'] = $param['is_default'];
        }
        if(isset($param['addressId']) && !empty($param['addressId'])){
            $where['id'] = $param['addressId'];
        }

        $userAddress = $this->addressModel->with('community,user')->where($where)->select();
        $addressData = [];
        if(!$userAddress){
            throw new UserException([
                ConstStatus::RESPONSE_CODE => ConstStatus::CODE_ERROR,
                ConstStatus::RESPONSE_DESC => ConstStatus:: DESC_ADDRESS_EMPTY
            ]);
        }
        //返回必要的数据
        foreach ($userAddress as $address){
            $addressData[] = [
                "address"=> $address['address'],
                'addressId'=> $address['id'],
                'areaId1'=> $address['community']['name'],
                'areaId'=> $address['community']['id'],
                'isDefault'=>$address['is_default'],
                'userName'=>$address["name"],
                'userPhone'=> $address["phone"]
            ];
        }
        return self::json2($addressData);
    }

    /**
     * Notes: 设置用户默认地址
     * User: jackin.chen
     * Date: 2021/10/19 7:34 下午
     * function: setDefaultAddress
     * @param Request $request
     * @return \think\response\Json
     */
    public function setDefaultAddress(Request $request){

        $res = Db::transaction(function ()use($request) {
            $validate = new UserAddressValidate();
            $validate->doValidate();
            $uid = $this->getCurrentUid();
            $user = $this->userModel->get($uid);
            if(!$user){
                throw new ParameterException([
                    ConstStatus::RESPONSE_CODE => ConstStatus::CODE_ERROR,
                    ConstStatus::RESPONSE_DESC => ConstStatus::DESC_USER
                ]);
            }
            $default = $this->addressModel->where(['user_id'=>$uid])->update([
                'is_default' => 0,
                'update_time' => time(),
            ]);
            if(!$default){
                throw new ParameterException([
                    ConstStatus::RESPONSE_CODE => ConstStatus::CODE_ERROR,
                    ConstStatus::RESPONSE_DESC => ConstStatus::DESC_DEFAULT
                ]);
            }
            $data = $validate->getDataByRule($request->post());
            return $this->addressModel->where([
                'user_id'=>$uid,
                'id'=>$data['addressId']
            ])->update(['is_default'=>1,'update_time'=>time()]);
        });
        $code  = $res ? ConstStatus::CODE_SUCCESS : ConstStatus::CODE_ERROR;
        return self::json2(['res'=>$res],$code);
    }


    /**
     * Notes: 删除用户地址
     * User: jackin.chen
     * Date: 2021/10/19 7:34 下午
     * function: deleteAddress
     * @param Request $request
     * @return \think\response\Json
     */
    public function deleteAddress(Request $request){

        (new AddressIdValidate())->doValidate();
        $uid = $this->getCurrentUid();

        $address = $this->addressModel->where(['user_id'=>$uid,'id'=>$request->post('id')])->find();
        $res = 0;
        if(!empty($address)){
            $address->delete_time=time();
            $address->save();
            $res = $address->id;
        }
        $code  = $res ? ConstStatus::CODE_SUCCESS : ConstStatus::CODE_ERROR;
        return self::json2(['res'=>$res],$code);
    }


    /**
     * Notes: 保存地址信息
     * User: jackin.chen
     * Date: 2020/6/26 下午3:46
     * function: userAddressOption
     * @param Request $request
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws ParameterException
     * @throws UserException
     * @throws \app\api\exception\TokenException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function userAddressOption(Request $request){
        $validate = new UserAddressValidate();
        $validate->doValidate();
        $uid = $this->getCurrentUid();
        $user = $this->userModel->get($uid);
        if(!$user){
            throw new ParameterException([
                ConstStatus::RESPONSE_CODE => ConstStatus::CODE_ERROR,
                ConstStatus::RESPONSE_DESC => ConstStatus::DESC_USER
            ]);
        }
        $post = $request->post();
        // 根据规则取字段是很有必要的，防止恶意更新非客户端字段
        $data = $validate->getDataByRule($post);
        $params = [
            'name'=>$data['userName'],
            'phone'=>$data['userPhone'],
            'community_id'=>$data['areaId'],
            'address'=>$data['address'],
            'is_default'=>$data['isDefault']
        ];
        $address_id = isset($post['addressId']) && !empty($post['addressId']) ? $post['addressId'] : 0;
        //如果设置默认地址
        if($data['isDefault']) {
            $this->addressModel->where(['user_id'=>$uid])->update(['is_default'=>0]);
        }
        if($address_id){
            $userAddress = $this->addressModel->where(['user_id'=>$uid,'id'=>$address_id])->find();
            if(!$userAddress){
                throw new UserException([
                    ConstStatus::RESPONSE_CODE => ConstStatus::CODE_ERROR,
                    ConstStatus::RESPONSE_DESC => ConstStatus:: DESC_ADDRESS_EMPTY
                ]);
            }
            $res = $userAddress->save($params);
            return self::json2(['res'=>$res]);
        }else{
            //获取用户地址模型
            $userAddress = $user->address;
            $count_address = count($userAddress);
            $max_count_address = 20;
            if( $count_address > $max_count_address){
                throw new ParameterException([
                    ConstStatus::RESPONSE_CODE => ConstStatus::CODE_ERROR,
                    ConstStatus::RESPONSE_DESC => ConstStatus::DESC_ADDRESS_COUNT
                ]);
            }
            // 关联属性不存在，则新建
            $query = $user->address()->save($params);
            return self::json2(['res'=>$query->id]);
        }
    }

}