<?php
/**
 * Created by PhpStorm.
 * FileName: Article.php
 * User: Administrator
 * Date: 2017/10/28
 * Time: 10:18
 */

namespace app\api\controller\v1;

use app\api\validate\ArticleValidate;
use app\common\controller\WxsApi;
use think\Request;
use app\common\model\Activity as ActivityModel;
use app\common\model\Article as ArticleModel;


class Activity extends WxsApi {

    private $activityModel = null;
    private $articleModel = null;
    protected $allowMethod = array('get','post');

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->activityModel = new ActivityModel();
        $this->articleModel = new ArticleModel();
    }



    /**
     * Notes: 获取活动信息
     * User: jackin.chen
     * Date: 2021/10/19 7:30 下午
     * function: getActivityList
     * @param Request $request
     * @return \think\response\Json
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
        return self::json2($activity ? $activity : []);
    }


    /**
     * Notes: 获取文章详情
     * User: jackin.chen
     * Date: 2021/10/19 7:33 下午
     * function: read
     * @param Request $request
     * @param $id
     * @return \think\response\Json
     */
    public function read(Request $request, $id) {

        //验证器
        (new ArticleValidate())->doValidate();
        //业务逻辑
        $result = $this->articleModel->getArticleInfo($id);
        return self::json2($result);
    }

}