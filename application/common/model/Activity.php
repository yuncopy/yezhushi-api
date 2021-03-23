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

class Activity extends Model {

    // 表名,不含前缀
    public $name = 'activity';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    public function community(){
        return $this->belongsTo('Community','community_code','code');
    }


    /**
     * Notes: 定义读取器
     * User: jackin.chen
     * Date: 2020/6/28 上午10:33
     * function: getCreateTimeAttr
     * @param $value
     * @return false|string
     */
    /* 目前在前端统一处理
    public function getCreateTimeAttr($value)
    {
        return date('Y-m-d',$value);
    }
    public function getBeginTimeAttr($value)
    {
        return date('Y-m-d',$value);
    }
    public function getEndTimeAttr($value)
    {
        return date('Y-m-d',$value);
    }
    */

    /**
     * Notes: 获取社区活动列表
     * User: jackin.chen
     * Date: 2020/6/28 下午4:46
     * function: getActivityList
     * @param array $map
     * @param int $currPage
     * @param int $pageSize
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getActivityList($map=[],$currPage=1,$pageSize=3)
    {

        //查询查看详情使用
        if(isset($map['id']) && !empty($map['id'])){
            return $this->field(
                'place,sponsor_unit,id as catId,title as articleTitle,create_time as createTime,content as articleContent,begin_time as beginTime,end_time as endTime'
            )->where($map)->order([
                    'begin_time'=>'asc'
                ])->limit($pageSize)->find();
        }else{
            //查询列表使用
             $query = $this->field('id,id as articleId,title as activityTitle,thumb as activityImg,create_time as createTime,begin_time as beginTime,end_time as endTime')
                ->where($map)->order([
                    'begin_time'=>'asc'
                ])->paginate($pageSize,false,['page'=>$currPage]);
            return [
                'currPage'=>$currPage,
                'totalPage'=>ceil($query->total()/$pageSize),
                'pageSize'=>(int)$pageSize,
                'root'=>$query->items()
            ];
        }
    }

}