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

class Record extends Model {

    // 表名,不含前缀
    public $name = 'vote_record';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function community(){
        return $this->belongsTo('Community','community_code','code');
    }

    public function subject(){
        return $this->belongsTo('Subject','subject_id','id');
    }

    public function user(){
        return $this->belongsTo('Users','user_id','id');
    }

    public function player(){
        return $this->belongsTo('Player','player_id','id');
    }


    /**
     * Notes: 保存数据
     * User: jackin.chen
     * Date: 2021/3/2 下午7:57
     * function: saveVote
     * @param $data
     * @return false|int
     */
    public function saveVote($data){

        return $this->save([
            'user_id'=>$data['user_id'],
            'subject_id'=>$data['subject_id'],
            'player_id'=>$data['player_id'],
            'status'=>1,
            'ipaddr'=>request()->ip(),
        ]);
    }

    /**
     * Notes: 每天统计数据
     * User: jackin.chen
     * Date: 2021/3/3 上午9:42
     * function: getEveryRecord
     * @param array $where
     * @param array $between
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getVoteRecord($where=[],$between=[]){

        $where['status'] = 1;
        $where['ipaddr'] = request()->ip();
        $data = $this->field(['id','subject_id','player_id','user_id'])->where($where)
            ->where('createtime','between',$between)->select();
        $data = collection($data)->toArray();
        $pervotelimit = array_count_values(array_column($data,'player_id'));
        return [
            'voteplayers'=>count($pervotelimit),
            'votenums'=>array_sum($pervotelimit),
            'votelimit'=>$pervotelimit,
        ];
    }





}