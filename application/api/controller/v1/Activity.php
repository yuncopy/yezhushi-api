<?php
/**
 * Created by PhpStorm.
 * FileName: Article.php
 * User: Administrator
 * Date: 2017/10/28
 * Time: 10:18
 */

namespace app\api\controller\v1;

use app\api\library\ConstStatus;
use app\common\controller\WxsApi;
use think\Request;
use app\common\model\Activity as ActivityModel;


class Activity extends WxsApi {

    private $activityModel = null;
    protected $allowMethod = array('get','post');

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->activityModel = new ActivityModel();
    }


    /**
     * Notes: 获取活动信息
     * User: jackin.chen
     * Date: 2020/6/28 下午2:22
     * function: getActivityList
     * @param Request $request
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getActivityList(Request $request){

        $currPage = $request->post('pageNo',1);
        $pageSize = $request->post('pageSize',10);
        $post_id = $request->post('id',0);

        //echo THINK_VERSION;
        $map['status']  = ['=',1];
        $map['end_time']  = ['>=',time()];
        $post_id && $map['id']  = ['=',$post_id];

        $activity = $this->activityModel->getActivityList($map,$currPage,$pageSize);
        return parent::response($activity ? $activity : [],ConstStatus::CODE_SUCCESS);
    }



    /**
     * Notes: 获取文章详情
     * User: jackin.chen
     * Date: 2020/6/21 下午3:45
     * function: read
     * @param Request $request
     * @param $id
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \app\api\exception\ParameterException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function read(Request $request, $id) {

        //验证器
        (new ArticleValidate())->doValidate();

        //业务逻辑
        $result = $this->articleModel->getArticleInfo($id);
        return parent::response($result,ConstStatus::CODE_SUCCESS);
    }

}