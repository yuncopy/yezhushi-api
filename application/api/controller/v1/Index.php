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
use app\common\model\Player;
use app\common\model\Subject;
use app\common\model\Users as UsersModel;
use app\common\model\Article as ArticleModel;
use app\common\model\Repair as RepairsModel;
use app\common\model\Slide as SlideModel;
use app\common\model\Player as PlayerModel;
use app\common\model\Subject as SubjectModel;

use think\Request;

class Index extends WxsApi {

    private $userModel = null;
    private $articleModel = null;
    private $repairModel = null;
    private $slideModel = null;
    private $playerModel = null;
    private $subjectModel = null;
    protected $allowMethod = array('get','post');

    /**
     * 构造函数
     * Index constructor.
     * @param Request $request
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->userModel = new UsersModel();
        $this->articleModel = new ArticleModel();
        $this->repairModel = new RepairsModel();
        $this->slideModel = new SlideModel();
        $this->playerModel = new PlayerModel();
        $this->subjectModel = new SubjectModel();
    }


    /**
     * Notes: 获取用户信息兑换token值
     * User: jackin.chen
     * Date: 2021/10/19 7:28 下午
     * function: getToken
     * @param Request $request
     * @return mixed
     */
    public function getToken(Request $request) {

        //统一验证器
        (new CodeValidate())->doValidate();

        //实际处理业务流程
        $code  = $request->post('code');
        $token = (new UserToken($code))->getToken();
        $userCache = cache($token);
        $userInfo = $userCache ? json_decode($userCache,true) : [];

        //处理结果 todo处理网络异常
        return self::json2([
            'token' => $token,
            'userId'=> isset($userInfo['uid']) ? $userInfo['uid'] : 0
        ]);
    }


    /**
     * Notes: 更新用户信息
     * User: jackin.chen
     * Date: 2021/10/19 7:28 下午
     * function: modifyUser
     * @param Request $request
     * @return \think\response\Json
     */
    public function modifyUser(Request $request){
        //验证器
        (new UserValidate())->doValidate();
        //获取参数
        $params = $request->param();
        $data = $this->userModel->modifyUser($params);
        //处理结果 todo处理网络异常
        return self::json2(['userId' => $data]);
    }


    /**
     * Notes: 获取文章分页数据
     * User: jackin.chen
     * Date: 2021/10/19 7:27 下午
     * function: getArticleList
     * @param Request $request
     * @return \think\response\Json
     */
    public function getArticleList(Request $request){

        //获取参数
        $prams  = $request->post();
        $currPage = isset($prams['pageNo']) ? intval($prams['pageNo']) : 1;
        $pageSize = isset($prams['pageSize']) ? intval($prams['pageSize']) : 10;
        $weight = isset($prams['weight']) ? intval($prams['weight']) : null;
        $data = $this->articleModel->pageList($currPage,$pageSize,$weight);
        //处理结果 todo处理网络异常
        return self::json2($data);
    }


    /**
     * Notes: 获取轮播图
     * User: jackin.chen
     * Date: 2021/10/19 7:25 下午
     * function: getBanner
     * @return \think\response\Json
     */
    public function getBanner(){
        $data = $this->slideModel->getSlideList();
        return self::json2($data);
    }


    /**
     * Notes: 获取报修列表
     * User: jackin.chen
     * Date: 2021/10/19 7:29 下午
     * function: getRepairList
     * @param Request $request
     * @return \think\response\Json
     */
    public function getRepairList(Request $request){

        $params = $request->post();
        $uid = $this->getCurrentUid();
        $query = $this->repairModel->field('*,status as orderStatus')
            ->with('address,community')->where(['repair_user_id'=>$uid, 'from_srv'=>1]);

        //处理查询条件
        if(isset($params['orderStatus']) && !empty($params['orderStatus'])){
            $orderStatus = explode(',',$params['orderStatus']);
            $query = $query->whereIn('status',$orderStatus);
        }

        if(isset($params['orderId']) && !empty($params['orderId'])) {
            $query = $query->where('id',$params['orderId']);
        }
        //查询结果
        $list = $query->select();
        $repairList = [];
        if($list){
           foreach ($list as  $item){
               list($orderSrc1, $orderSrc2, $orderSrc3) = array_pad(explode( ',', $item['thumb']), 3, '' );
               $repairList[] = [
                   'areaId1'=>$item['community']['address'],
                   'createTime'=>$item['create_time'],
                   'fName'=>$item['device_name'],
                   'orderId'=>$item['id'],
                   'orderRemarks'=>$item['desc'],
                   'thumb'=>$item['thumb'],
                   'orderSrc1'=>$orderSrc1,
                   'orderSrc2'=>$orderSrc2,
                   'orderSrc3'=>$orderSrc3,
                   'orderStatus'=>$item['orderStatus'],
                   'status'=>$item['status'],
                   'userAddress'=>$item['address']['address'],
                   'userPhone'=>$item['address']['phone'],
                   'userTel'=>$item['address']['phone'], //师傅电话
                   'userName'=>$item['address']['name'], //用户昵称
               ];
           }
        }
        return self::json2($repairList);
    }


    /**
     * Notes: 查询搜索
     * User: jackin.chen
     * Date: 2021/10/19 7:29 下午
     * function: searchAll
     * @param Request $request
     * @return \think\response\Json
     */
    public function searchAll(Request $request){
        $params = $request->post();
        $data = [];
        switch ($params['index']){
            case 0:  // 选手
                $data = $this->playerModel->pagePlayer($params);
                break;
            case 1:  // 活动
                $data = $this->subjectModel->pageSubject($params);
                break;
            case 2:  // 文章
                $data = $this->articleModel->pageArticle($params);
                break;
        }
        return self::json2($data);
    }




    public function recomShopsList(Request $request){

        echo '{
    "stauts": "1",
    "msg": "成功",
    "data": {
        "total": "12",
        "pageSize": 5,
        "start": 0,
        "root": [
            {
                "shopId": "1",
                "shopName": "自营店铺",
                "shopKeywords": "自营店铺关键字",
                "shopDesc": "自营店铺描述",
                "shopBanner": "/uploads/20200601/7cd29c3d7f4ae61b8075b7fbad48a1d1.png"
            },
            {
                "shopId": "3",
                "shopName": "测试2",
                "shopKeywords": "店铺关键字",
                "shopDesc": "店铺描述",
                "shopBanner": "/uploads/20200601/7cd29c3d7f4ae61b8075b7fbad48a1d1.png"
            },
            {
                "shopId": "4",
                "shopName": "测试3",
                "shopKeywords": "店铺关键字",
                "shopDesc": "店铺描述",
                "shopBanner": "/uploads/20200601/7cd29c3d7f4ae61b8075b7fbad48a1d1.png"
            },
            {
                "shopId": "5",
                "shopName": "测试4",
                "shopKeywords": "店铺关键字",
                "shopDesc": "店铺描述",
                "shopBanner": "/uploads/20200601/7cd29c3d7f4ae61b8075b7fbad48a1d1.png"
            },
            {
                "shopId": "6",
                "shopName": "测试5",
                 "shopKeywords": "店铺关键字",
                "shopDesc": "店铺描述",
                "shopBanner": "/uploads/20200601/7cd29c3d7f4ae61b8075b7fbad48a1d1.png"
            }
        ],
        "totalPage": 3,
        "currPage": 1
    }
}';

    }


}