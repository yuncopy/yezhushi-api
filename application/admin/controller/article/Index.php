<?php
namespace app\admin\controller\article;

use app\common\controller\Backend;
use app\common\model\Category;

/**
 * Created by PhpStorm.
 * FileName: Index.php
 * User: Administrator
 * Date: 2017/10/20
 * Time: 13:42
 * @remark 用于通用内容管理
 */

class Index extends Backend {

    protected $model = null;
    protected $categoryModel = null;
    //检索时匹配的字段
    protected $searchfields = 'title,introduction';
    protected $noNeedRight = ['selectpage'];

    public function _initialize() {
        parent::_initialize();
        $this->model = model('Article');
        $this->categoryModel = new Category();
        $this->view->assign("categoryList", $this->categoryModel->getTreeCategory(['article']));
    }

    public function index() {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name')) {
                return $this->selectpage();
            }
            return $this->handleSearch($this->searchfields);
        }
        return $this->view->fetch();
    }

    public function detail($ids = null) {
        return parent::modify($ids);
    }

    public function add() {
        return parent::create();
    }

    public function edit($ids = null) {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $this->request->post(['row' => $params]);
        }
        return parent::modify($ids,'add');
    }

    public function del($ids = null){
        $where = array(
            'id' => array('in',$ids)
        );
        parent::delete($where);
    }

    public function selectpage() {
        return parent::selectpage();
    }

    private function handleSearch($searchfields=null) {
        list($where, $sort, $order, $offset, $limit) = $this->buildparams($searchfields);
        $total = $this->model->where($where)->count();
        $list = $this->model->with("category")->where($where)->order($sort, $order)->limit($offset, $limit)->select();
        $result = array("total" => $total, "rows" => $list);
        return json($result);
    }

}