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

class Subject extends Model {

    // 表名,不含前缀
    public $name = 'vote_subject';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function community(){
        return $this->belongsTo('Community','community_code','code');
    }


    /**
     * Notes: 获取投票活动
     * User: jackin.chen
     * Date: 2021/2/27 下午4:06
     * function: getDataList
     * @param array $status
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDataList($status=[],$limit) {
        $result = $this->where(['status'=>$status])
            ->field('id,players,votes,title,thumb,intro')
            ->order(['intop'=>'desc','status'=>'asc',])
            ->limit($limit)
            ->select();
        return $result;
    }

    public function player()
    {
        return $this->hasMany('Player','subject_id');
    }



    /**
     * Notes:查询投票选手
     * User: jackin.chen
     * Date: 2021/2/27 下午6:18
     * function: subjectPlayerList
     * @param array $status
     * @param array $where
     * @param array $field
     * @param array $page
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function subjectPlayerList($status,$page=['current'=>1,'size'=>10],$where=[],$field=['id','title','thumb']) {

        $current = $page['current'];
        $size = $page['size'];
        $result = $this->with(['player'=>function($query)use($current,$size){
            $query->field(['id','subject_id','number','nickname','intro','content','thumb','views','votes','cur_order'])
                ->where(['status'=>1])->order(['number'=>'asc'])->page($current,$size);
        }])->where(function ($query) use($status,$where){
             $query->where(['status'=>$status]);
             if($where) $query->where($where);
        })->field($field)->order(['intop'=>'desc','status'=>'asc',])
            ->limit(1)
            ->select();

        //\Think\Log::record(var_export($result,true),'notice');

        //查询选手总数
        $count = $this->player()->where(['subject_id'=>$result[0]->id,'status'=>1])->count();
        //处理总页数
        $totalPage = ceil($count/$size);//总页数
        $result[0]->currPage = ($current > $totalPage ? intval($totalPage) : $current);
        $result[0]->totalPage = $totalPage;
        return  $result;
    }


    /**
     * Notes: 获取所有项目
     * User: jackin.chen
     * Date: 2021/3/2 上午10:42
     * function: subjectList
     * @param array $status
     * @param array $page
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function subjectList($status=[],$page=['current'=>1,'size'=>10]) {

        $current = $page['current'];
        $size = $page['size'];

        //记录数
        $count = $this->whereIn(['status'=>$status])->order(['intop'=>'desc','status'=>'asc'])->count();
        //查询结果
        $query1= $this->field(['id','title','thumb','players','votes','voters','views'])
            ->whereIn(['status'=>$status]);
        $result= $query1->page($current, $size)->select();

        //!empty($where)  && var_dump($where,$this->getLastSql());

        //处理总页数
        $totalPage = ceil($count/$size);//总页数
        $currPage = ($current > $totalPage ? intval($totalPage) : $current);
        return ['result'=>$result,'totalPage'=>$totalPage,'currPage'=>$currPage];
    }

    /**
     * Notes: 获取项目配置
     * User: jackin.chen
     * Date: 2021/3/2 下午8:56
     * function: subjectLimit
     * @param $subject_id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function subjectLimit($subject_id){

        return $this->field([
            'begin_time',
            'begin_time',
            'end_time',
            'pervoteplayers',
            'pervotenums',
            'pervotelimit',
            'maxvoteplayers',
            'maxvotenums',
            'maxvotelimit',
        ])->find($subject_id);
    }


    /**
     * Notes: 按照条件获取项目
     * User: jackin.chen
     * Date: 2021/3/9 上午10:36
     * function: pageSubject
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pageSubject($params){
        $search_field = 'title|intro';
        $keyWord = $params['shops'];
        $current = $params['p'];
        $size = 6;
        $status = '1,2';  //状态
        $time = time() - 31536000;  //24*60*60 * 365
        $where =[
            $search_field=>['like','%'.$keyWord.'%'],
            'createtime'=>['>',$time]
        ];
        //记录数
        $query = $this->where($where)->whereIn(['status'=>$status])->order(['intop'=>'desc','status'=>'asc']);
        $count = $query->count();

        //查询结果
        $field = 'id,title,thumb,players,votes,voters,views';
        $query1= $this->where($where)->field($field)->whereIn(['status'=>$status]);
        $result= $query1->page($current, $size)->select();


        //获取选手
        $playerColumn = [];
        $collection = collection($result);
        $subject = $collection->column('id');
        $player = model('Player')->field('subject_id,id,thumb,votes,nickname,number')
            ->whereIn('subject_id',$subject)->select();
        if($player){
            foreach ($player as $item){
                $playerColumn[$item['subject_id']][] = $item->toArray();
            }
        }

        //处理结果
        $getRand = function ($data){
            $rand = array_keys($data);
            shuffle($rand);
            $data1 = array_slice($rand,0,3);
            $data2 = [];
            foreach ($data1 as $value){
                $data2[$value] = $data[$value];
            }
            return $data2;
        };
        if($result){
            collection($result)->each(function ($item)use($playerColumn,$getRand){
                $thumb = explode(',', $item->thumb);
                $item->thumb =  current($thumb);
                $item->thumbs =  $thumb;
                $item->player =  isset($playerColumn[$item->id]) ? $getRand($playerColumn[$item->id]) : [];
            });
        }


        //!empty($keyWord)  && var_dump($where,$this->getLastSql());

        //处理总页数
        $totalPage = ceil($count/$size);//总页数
        $currPage = ($current > $totalPage ? intval($totalPage) : $current);
        return ['result'=>$result,'totalPage'=>$totalPage,'currPage'=>$currPage];
    }





}