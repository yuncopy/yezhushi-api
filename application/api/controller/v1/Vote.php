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
use app\api\library\ConstStatus;
use app\common\model\Subject as SubjectModel;
use app\common\model\Slide as SlideModel;
use app\common\model\Player as PlayerModel;
use app\common\model\Record as recordModel;

use think\Request;

class Vote extends BaseApi {


    protected $allowMethod = array('get','post');
    private $slideModel = null;
    private $subjectModel = null;
    private $playerModel = null;
    private $recordModel = null;
    private $title = null;

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->subjectModel = new SubjectModel();
        $this->slideModel = new SlideModel();
        $this->playerModel = new playerModel();
        $this->recordModel = new recordModel();
    }

    /**
     * Notes: 获取轮播图
     * User: jackin.chen
     * Date: 2021/2/4 下午3:38
     * function: getBanner
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getBanner(){
        $voteFlag = 'vote-swiper';
        $data = $this->slideModel->getSlideList($voteFlag);
        return parent::response($data,ConstStatus::CODE_SUCCESS,ConstStatus::DESC_SUCCESS);
    }

    /**
     * Notes: 获取进行中项目
     * User: jackin.chen
     * Date: 2021/2/27 下午5:12
     * function: getSubject
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSubject(){
        $status = [1,2];
        $limit = 4;
        $result = $this->subjectModel->getDataList($status,$limit);
        $data = collection($result)->each(function ($item){
            $array = explode(',', $item->thumb);
            return $item->thumbs = isset($array[0]) ? $array[0] : "";
        });
        return parent::response($data,ConstStatus::CODE_SUCCESS,ConstStatus::DESC_SUCCESS);
    }


    /**
     * Notes: 查询投票选手数据
     * User: jackin.chen
     * Date: 2021/2/27 下午6:20
     * function: getSubjectPlayer
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSubjectPlayer(Request $request){

        $prams  = $request->post();
        $currPage = isset($prams['p']) ? intval($prams['p']) : 1;
        $pageSize = isset($prams['size']) ? intval($prams['size']) : 6;
        $status = [1,2];
        $result = $this->subjectModel->subjectPlayerList($status,['current'=>$currPage,'size'=>$pageSize]);
        $data = collection($result)->each(function ($item){
            return $item->thumbs =  explode(',', $item->thumb);
        });
        return parent::response($data[0],ConstStatus::CODE_SUCCESS,ConstStatus::DESC_SUCCESS);
    }


    /**
     * Notes: 获取项目列表
     * User: jackin.chen
     * Date: 2021/2/28 上午2:58
     * function: getSubjectList
     * @param Request $request
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function getSubjectList(Request $request){

        $prams  = $request->post();
        $currPage = isset($prams['p']) ? intval($prams['p']) : 1;
        $pageSize = isset($prams['size']) ? intval($prams['size']) : 6;
        $status = [1,2];
        $result = $this->subjectModel->subjectList($status,['current'=>$currPage,'size'=>$pageSize]);
        collection($result['result'])->each(function ($item){
            return $item->thumbs  =current(explode(',', $item->thumb));
        });
        return parent::response($result,ConstStatus::CODE_SUCCESS,ConstStatus::DESC_SUCCESS);
    }


    /**
     * Notes: 获取
     * User: jackin.chen
     * Date: 2021/2/28 上午3:51
     * function: getSubjectPlayerList
     * @param Request $request
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSubjectPlayerList(Request $request){

        $prams  = $request->post();
        $currPage = isset($prams['p']) ? intval($prams['p']) : 1;
        $pageSize = isset($prams['size']) ? intval($prams['size']) : 6;
        $subjectId = isset($prams['subjectid']) ? ($prams['subjectid']) : 0;
        $status = [1,2];
        $field = ['id','title','thumb','intro','content','players','votes','views','voters'];
        $result = $this->subjectModel->subjectPlayerList($status,['current'=>$currPage,'size'=>$pageSize],['id'=>$subjectId],$field);
        $data = collection($result)->each(function ($item){
           $item->thumbs =  explode(',', $item->thumb);
           $item->thumb =  current($item->thumbs);
        });
        return parent::response($data[0],ConstStatus::CODE_SUCCESS,ConstStatus::DESC_SUCCESS);
    }


    /**
     * Notes: 获取选手消息
     * User: jackin.chen
     * Date: 2021/3/2 下午5:48
     * function: getPlayerInfo
     * @param Request $request
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPlayerInfo(Request $request){
        $prams  = $request->post();
        $playerId = isset($prams['playerid']) ? ($prams['playerid']) : 0;
        $status = [1];
        $result = $this->playerModel->getPlayer($status,$playerId);
        return parent::response($result,ConstStatus::CODE_SUCCESS,ConstStatus::DESC_SUCCESS);
    }



    public function submitVote(Request $request){

        $prams  = $request->post();
        $prams['userid'] =  $this->getCurrentUid();
        $icon = 'none';
        $this->title =  ConstStatus::VOTE_ERROR;
        do{
            $subject_id = $prams['subjectid'];

            //获取项目规则
            $subject = $this->subjectModel->subjectLimit($subject_id);
            if(!is_null($this->timeLimit($subject))){
                break;
            }

            //每天验证项目信息
            if(!is_null($this->perVoteLimit($prams,$subject))){
                break;
            }
            //活动天数累积验证
            if(!is_null($this->maxVoteLimit($prams,$subject))){
                break;
            }

            //保存投票记录
            $result = $this->recordModel->saveVote([
                'user_id'=>$prams['userid'],
                'subject_id'=>$prams['subjectid'],
                'player_id'=>$prams['playerid'],
            ]);
            //自增
            $this->playerModel->where('id',$prams['playerid'])->setInc('votes',1);
            if(!$result) break;
            $icon = 'success';
            $this->title = ConstStatus::VOTE_SUCCESS;
        }while(0);
        return parent::response(['icon'=>$icon,'title'=>$this->title],ConstStatus::CODE_SUCCESS,ConstStatus::DESC_SUCCESS);
    }

    /**
     * Notes: 验证时间
     * User: jackin.chen
     * Date: 2021/3/2 下午9:03
     * function: timeLimit
     * @param $subject
     * @return bool
     */
    private function timeLimit($subject){
        $time = format_time();
        $begin_time = $subject['begin_time'];
        $end_time = $subject['end_time'];
        if($begin_time >= $time || $time > $end_time){
           return $this->title = ConstStatus::VOTE_TIME_ERROR;
        }
        return null;
    }



    /**
     * Notes:每天统计次数
     * User: jackin.chen
     * Date: 2021/3/3 上午10:36
     * function: perVoteLimit
     * @param $prams
     * @param $subject
     * @return null|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function perVoteLimit($prams,$subject){

        $player_id = $prams['playerid']; // 选手ID
        $pervotelimit = $subject['pervotelimit']; //每天可为同一选手投票数，0表示不限制
        $pervoteplayers = $subject['pervoteplayers']; //每天可投选手数，0表示不限制
        $pervotenums = $subject['pervotenums']; //每天可投票的次数，0表示不限制

        //获取记录信息
        $start_time = strtotime('today'); //获得今天零点的时间戳
        $end_time = $start_time + 86400;        //获得点的时间戳
        $data = $this->recordModel->getVoteRecord(['user_id'=>$prams['userid']],[$start_time,$end_time]);

        //每天可投选手数，0表示不限制
        if(!empty($data['voteplayers']) && !empty($pervoteplayers) && $data['voteplayers'] >= $pervoteplayers){
            return  $this->title = ConstStatus::VOTE_TIME_PLAYER."({$pervoteplayers})";
        }

        //每天可为同一选手投票数，0表示不限制
        if(!empty($data['votelimit'][$player_id]) && !empty($pervotelimit) && $data['votelimit'][$player_id] >= $pervotelimit){
            return $this->title = ConstStatus::VOTE_LIMIT_PLAYER."({$pervotelimit})";
        }

        //每天可投票的次数，0表示不限制
        if(!empty($data['votenums']) && !empty($pervotenums) && $data['votenums'] >= $pervotenums){
            return $this->title = ConstStatus::VOTE_NUM_PLAYER."({$pervotenums})";
        }
        return null;
    }


    /**
     * Notes: 累积投票限制
     * User: jackin.chen
     * Date: 2021/3/3 上午10:37
     * function: maxVoteLimit
     * @param $prams
     * @param $subject
     * @return null|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function maxVoteLimit($prams,$subject){

        $player_id = $prams['playerid']; // 选手ID
        $maxvotelimit = $subject['maxvotelimit']; //每天可为同一选手投票数，0表示不限制
        $maxvoteplayers = $subject['maxvoteplayers']; //每天可投选手数，0表示不限制
        $maxvotenums = $subject['maxvotenums']; //每天可投票的次数，0表示不限制

        //获取记录信息
        $start_time = $subject['begin_time']; //获得今天零点的时间戳
        $end_time = $subject['end_time'];       //获得点的时间戳
        $data = $this->recordModel->getVoteRecord(['user_id'=>$prams['userid']],[$start_time,$end_time]);

        //可投选手数，0表示不限制
        if(!empty($data['voteplayers']) && !empty($maxvoteplayers) && $data['voteplayers'] > $maxvoteplayers){
            return  $this->title = ConstStatus::VOTE_MAX_TIME_PLAYER."({$maxvoteplayers})";
        }

        //可为同一选手投票数，0表示不限制
        if(!empty($data['votelimit'][$player_id]) && !empty($maxvotelimit) && $data['votelimit'][$player_id] > $maxvotelimit){
            return $this->title = ConstStatus::VOTE_MAX_LIMIT_PLAYER."({$maxvotelimit})";
        }

        //可投票的次数，0表示不限制
        if(!empty($data['votenums']) && !empty($maxvotenums) && $data['votenums'] > $maxvotenums){
            return $this->title = ConstStatus::VOTE_MAX_NUM_PLAYER."({$maxvotenums})";
        }
        return null;

    }





    public function getGoodsList(Request $request){

        echo '{
    "stauts": "1",
    "msg": "成功",
    "data": {
        "total": "18",
        "pageSize": 3,
        "start": 0,
        "root": [
            {
                "brandName": "来听",
                "goodsId": "34",
                "goodsName": "86型双回路执行器",
                "goodsImg": "/uploads/20200601/7cd29c3d7f4ae61b8075b7fbad48a1d1.png",
                "marketPrice": "300.00",
                "shopPrice": "212.00",
                "saleCount": "0",
                "goodsSpec": ""
            },
            {
                "brandName": "汉威智能",
                "goodsId": "33",
                "goodsName": "家庭健康管理套装",
                "goodsImg": "/uploads/20200601/7cd29c3d7f4ae61b8075b7fbad48a1d1.png",
                "marketPrice": "1799.00",
                "shopPrice": "1688.00",
                "saleCount": "0",
                "goodsSpec": ""
            },
            {
                "brandName": "汉威智能",
                "goodsId": "31",
                "goodsName": "汉威智能8000F",
                "goodsImg": "/uploads/20200601/7cd29c3d7f4ae61b8075b7fbad48a1d1.png",
                "marketPrice": "2700.00",
                "shopPrice": "2680.00",
                "saleCount": "0",
                "goodsSpec": ""
            }
        ],
        "totalPage": 6,
        "currPage": 1
    }
}';

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

    /**
     * Notes: 获取报修列表
     * User: jackin.chen
     * Date: 2020/6/26 下午11:49
     * function: getRepairList
     * @param Request $request
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \app\api\exception\TokenException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRepairList(Request $request){

        $params = $request->post();
        $uid = $this->getCurrentUid();
        $query = $this->repairModel->field('*,status as orderStatus')
            ->with('address,community')->where(['member_id'=>$uid, 'from'=>1]);

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
        return parent::response($repairList,ConstStatus::CODE_SUCCESS,ConstStatus::DESC_SUCCESS);
    }
    
}