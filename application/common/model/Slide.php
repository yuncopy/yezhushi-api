<?php
/**
 * Created by PhpStorm.
 * FileName: Article.php
 * User: Administrator
 * Date: 2017/10/20
 * Time: 13:51
 */

namespace app\common\model;


use think\Model;

class Slide extends Model {
    // 表名,不含前缀
    public $name = 'slide';

    // 增删改查时必需的字段，为空则不作限制
    public $requiredField = array(
        'find' => array('id' => 'id'),
        'list' => array('cid' => '轮播分类'),
        'add' => array('cid' => '轮播分类'),
        'update' => array('cid' => '轮播分类'),
        'delete' => array('id' => 'id')
    );


    /**
     * Notes:获取轮播图
     * User: jackin.chen
     * Date: 2021/10/26 8:04 下午
     * function: getSlideList
     * @param string $cmCode
     * @return array
     */
    public function getSlideList($cmCode = 'index-swiper') {
        $slideCategory = new SlideCategory();
        $slide = $slideCategory->getCateByCode($cmCode);
        $result = [];
        if($slide){
            $result = $this->where(['cid'=>$slide->id,'status'=>1])
                ->field('name,description,link,image')
                ->order('sort','asc')
                ->limit(5)
                ->select()->toArray();
        }
        return $result;
    }
}