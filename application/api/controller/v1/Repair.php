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
use think\Request;
use app\common\model\Category;
use app\common\model\Community;
use app\common\model\Repair as Repairs;
use app\common\model\UserAddress as AddressModel;
use app\api\library\ConstStatus;
use app\api\validate\RepairValidate;
use app\api\validate\RepairOrderIdValidate;

class Repair extends BaseApi {

    protected $allowMethod = array('get','post');

    private $addressModel = null;
    private $communityModel = null;
    private $repairModel = null;

    public function __construct(Request $request) {

        $this->communityModel = (new Community());
        $this->repairModel = (new Repairs());
        $this->addressModel = (new AddressModel());

        //设置过滤方法
        $request->filter(['strip_tags', 'htmlspecialchars']);
        parent::__construct($request);
    }


    /**
     * Notes:获取报修类型
     * User: jackin.chen
     * Date: 2020/6/26 下午1:10
     * function: getRepairType
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRepairType(){

        $cate = Category::getCategoryArray('repair','normal','repair');
        ksort_key($cate,[
            'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field'     => 'weigh',       //排序字段
        ]);
        $cateName = array_column($cate,'name','id');
        $repairType = [];
        foreach ($cateName as $key => $item){
            $repairType[] = [
                'repairId'=>$key,
                'repairName'=>$item
            ];
        }
        //处理结果 todo处理网络异常
        return parent::response($repairType,ConstStatus::CODE_SUCCESS,ConstStatus::DESC_SUCCESS);

    }

    /**
     * Notes: 获取所有小区
     * User: jackin.chen
     * Date: 2020/6/13 上午10:04
     * function: getEstateList
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getEstateList(){

        $list =  $this->communityModel->getCommunityList();
        return parent::response($list,ConstStatus::CODE_SUCCESS,ConstStatus::DESC_SUCCESS);

    }

    /**
     * Notes: 执行文件上传需要权限验证
     * User: jackin.chen
     * Date: 2020/6/26 下午5:51
     * function: repairImage
     * @param Request $request
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \app\api\exception\ParameterException
     */
    public function repairImage(Request $request){
        $file = $request->file('file');
        $upload = $this->fastUpload($file);
        $code = $upload['ret'] ? ConstStatus::CODE_ERROR :ConstStatus::CODE_SUCCESS;
        return parent::response($upload,$code);
    }


    /**
     * Notes: 提交报修单
     * User: jackin.chen
     * Date: 2020/6/26 下午7:27
     * function: submitRepairOrder
     * @param Request $request
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \app\api\exception\ParameterException
     * @throws \app\api\exception\TokenException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function submitRepairOrder(Request $request){


        (new RepairValidate)->doValidate();

        $params = $request->post();

        $consigneeId = $params['consigneeId'];
        $fid = $params['fid']; //报修设备id

        //处理小区
        $userAddress = $this->addressModel->where('id',$consigneeId)->find();
        $community = $this->communityModel->where('id',$userAddress->community_id)->find();

        //用户名称
        $uid = $this->getCurrentUid();
        //报修类型
        $cate = Category::getCategoryArray('repair','normal','repair');
        $cateName = array_column($cate,'name','id');

        //处理报修图片
        $thumb = function () use($params){
            $orderSrc = [];
            foreach ($params as $key =>$param){
                if(in_array($key,['orderSrc1','orderSrc2','orderSrc3']) && $param){
                    $orderSrc[] = $param;
                }
            }
            return $orderSrc ? implode(',',$orderSrc) : '';
        };
        $data = [
            'community_code'=>$community->code,
            'repair_user_id'=>$uid,
            'consignee_id'=>$consigneeId,
            'community_id'=>$userAddress->community_id,
            'device_name'=>$cateName[$fid],
            'device_id'=>$fid, //报修设备id
            'desc'=>$params['orderRemarks'],
            'from_srv'=>1,  //报修来源 1 后台管理人员 1 前端业主
            'thumb'=>$thumb(),
            'create_time'=>time()
        ];
        $repair = $this->repairModel->save($data);
        $code  = $repair ? ConstStatus::CODE_SUCCESS : ConstStatus::CODE_ERROR;
        return parent::response(['res'=>$repair],$code);
    }

    /**
     * Notes: 确认维修
     * User: jackin.chen
     * Date: 2020/6/27 下午1:28
     * function: finishRepairOrder
     * @param Request $request
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \app\api\exception\ParameterException
     * @throws \app\api\exception\TokenException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function  finishRepairOrder(Request $request){

        (new RepairOrderIdValidate)->doValidate();

        $orderId = $request->post('orderId');
        $uid = $this->getCurrentUid();
        $repairOrder = $this->repairModel->where([
            'id'=>$orderId,
            'user_id'=>$uid,
            'from_srv'=>1,
        ])->find();
        $repair = 0;
        if($repairOrder){
            $repairOrder->status = 3; // 确认维修
            $repair = $repairOrder->save();
        }
        $code  = $repair ? ConstStatus::CODE_SUCCESS : ConstStatus::CODE_ERROR;
        return parent::response(['res'=>$repair],$code);
    }



    /**
     * Notes:获取报修列表
     * User: jackin.chen
     * Date: 2021/3/6 下午4:58
     * function: getRepairList
     * @param Request $request
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRepairList(Request $request){

        $params = $request->post();
        $id = !empty($params['id']) ? intval($params['id']) : 0;
        $data = $this->repairModel->getRepairList($id,[$params['p'],$params['cnt'],$params['deviceid']]);
        if($id){
            $thumb = explode(',',$data['thumb']);
            $data['thumb'] = current($thumb);
            $data['status_text'] = $this->repairModel->getOrderStatusAttr($data['status']);
            $data['thumbs'] = $thumb;
        }else{
            collection($data['result'])->each(function ($item){
                $item->status_text = $this->repairModel->getOrderStatusAttr($item['status']);
                $item->thumb = current(explode(',',$item['thumb']));
            });
        }
        return parent::response($data,ConstStatus::CODE_SUCCESS,ConstStatus::DESC_SUCCESS);
    }

}