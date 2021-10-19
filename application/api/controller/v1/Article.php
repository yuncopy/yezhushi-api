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
use app\api\validate\ArticleValidate;
use app\common\model\Article as ArticleModel;


class Article extends WxsApi {

    private $articleModel = null;
    protected $allowMethod = array('get');

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->articleModel = new ArticleModel();
    }


    /**
     * Notes: 获取文章详情
     * User: jackin.chen
     * Date: 2021/10/19 7:36 下午
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