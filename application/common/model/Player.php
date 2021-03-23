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

class Player extends Model {

    // 表名,不含前缀
    public $name = 'vote_player';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function community(){
        return $this->belongsTo('Community','community_code','code');
    }

    public function member(){
        return $this->belongsTo('Member','member_id','id');
    }

    public function subject(){
        return $this->belongsTo('Subject','subject_id','id');
    }

    public function user(){
        return $this->belongsTo('Users','user_id','id');
    }



    /**
     * Notes:查询选手
     * User: jackin.chen
     * Date: 2021/3/8 下午4:20
     * function: getPlayer
     * @param $status
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPlayer($status,$id){

        $this->where('id', $id)->setInc('views', 1); //自增
        $data = $this->whereIn(['status'=>$status])
            ->field(['cur_order','nickname','number','intro','content','thumb','votes','views','subject_id'])->find($id);
        return $data;
    }


    /**
     * Notes: 获取选手数据
     * User: jackin.chen
     * Date: 2021/3/8 下午4:25
     * function: pagePlayer
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pagePlayer($params){

        //近一年的数据
        $time = time() - 31536000;  //24*60*60 * 365
        $keyWord = $params['goods'];
        $current = $params['p'];
        $size = $params['cnt'];

        $field = 'id,thumb,subject_id,cur_order,number,nickname,votes';
        $search_field = 'number|nickname';

        //查询结果
        $where =[
            $search_field=>['like','%'.$keyWord.'%'],
            'createtime'=>['>',$time],
            'status'=>['=',1]
        ];
        //查询指定项目
        if(!empty($params['subjectid'])){
            $where = array_merge($where,['subject_id'=>['=',$params['subjectid']]]);
        }


        $result = $this->with('subject')->field($field)
            ->where($where)->order('votes', 'desc')->select();
        $visible = explode(',',$field.',subject.title');
        if(!empty($result)){
            collection($result)->visible($visible)->each(function ($item){
                $thumb = explode(',', $item->thumb);
                $item->thumb =  current($thumb);
                $item->thumbs =  $thumb;
            });
        }
        //查询记录数
        $count = $this->with('subject')->field($field)->where($where)->count();

        //!empty($params['subjectid'])  && var_dump($this->getLastSql());

        $totalPage = ceil($count/$size);//总页数
        $currPage = ($current > $totalPage ? $totalPage : $current);
        return ['result'=>$result,'totalPage'=>$totalPage,'currPage'=>intval($currPage)];

    }



}