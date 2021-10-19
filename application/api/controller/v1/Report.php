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
use app\api\library\ConstStatus;
use app\common\controller\WxsApi;
use app\api\validate\ReportValidate;
use app\common\model\Report as ReportModel;


class Report extends WxsApi {

    private $reportModel = null;
    protected $allowMethod = array('get','post');

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->reportModel = new ReportModel();
    }

    public function community(){
        return $this->belongsTo('Community','community_code','code');
    }



    /**
     * Notes: 获取当天到访记录
     * User: jackin.chen
     * Date: 2021/10/19 7:39 下午
     * function: getReport
     * @return \think\response\Json
     */
    public function getReport(){

        $uid = $this->getCurrentUid();
        $start_time = strtotime('today'); //获得今天零点的时间戳
        $end_time = $start_time + 86400; //获得点的时间戳
        $user = $this->reportModel->with('community')->where('user_id',$uid)
            ->whereTime('reporttime', 'between', [$start_time, $end_time])->find();
        $data = $user ? $user->visible(['addr','name','phone','visiter','temperature','health','addr','community.name']) :[];

        return self::json2($data);
    }



    /**
     * Notes: 保存上报信息
     * User: jackin.chen
     * Date: 2021/10/19 7:40 下午
     * function: saveReport
     * @param Request $request
     * @return \think\response\Json
     */
    public function saveReport(Request $request){

        $validate = new ReportValidate();
        $validate->doValidate();
        $uid = $this->getCurrentUid();
        $post = $request->post();
        // 根据规则取字段是很有必要的，防止恶意更新非客户端字段
        $data = $validate->getDataByRule($post);

        //准备数据
        $params = [
            'community_code'=>$data['areaId1'],
            'community_id'=>$data['areaId'],
            'user_id'=>$uid,
            'name'=>$data['userName'],
            'phone'=>$data['userPhone'],
            'visiter'=>$data['visiter'],
            'temperature'=>$data['temperature'],
            'health'=>$data['health'],
            'ipaddr'=>request()->ip(),
            'addr'=>$data['address'],
            'reporttime'=>format_time(),
        ];
        $res = $this->reportModel->save($params);
        return self::json2(['res'=>$res]);
    }

}