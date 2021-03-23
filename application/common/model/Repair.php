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

class Repair extends Model {

    // 表名,不含前缀
    public $name = 'repair';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';



    public function community(){
        return $this->belongsTo('Community','community_code','code');
    }

    /**
     * Notes: 业主信息
     * User: jackin.chen
     * Date: 2020/6/27 下午1:59
     * function: member
     * @return \think\model\relation\BelongsTo
     */
    public function member(){
        return $this->belongsTo('Member','member_id','id');
    }


    /**
     * Notes: 前台会员列表
     * User: jackin.chen
     * Date: 2021/3/5 下午2:04
     * function: user
     * @return \think\model\relation\BelongsTo
     */
    public function user(){
        return $this->belongsTo('Users','user_id','id');
    }

    /**
     * Notes: 后台人员
     * User: jackin.chen
     * Date: 2020/6/27 下午2:00
     * function: admin
     * @return \think\model\relation\BelongsTo
     */
    public function admin(){
        return $this->belongsTo('Admin','member_id','id');
    }

    /**
     * Notes: 前端查询使用
     * User: jackin.chen
     * Date: 2020/6/26 下午7:49
     * function: address
     * @return \think\model\relation\BelongsTo
     */
    public function address(){
        return $this->belongsTo('UserAddress','consignee_id','id');
    }

    /**
     * Notes: 定义时间获取器
     * User: jackin.chen
     * Date: 2020/6/21 下午3:40
     * function: getCreateTimeAttr
     * @param $value
     * @return false|string
     */
    public function getCreateTimeAttr($value)
    {
        return date('Y-m-d H:i:s',$value);
    }

    /**
     * Notes: 状态读取器
     * User: jackin.chen
     * Date: 2020/6/26 下午8:12
     * function: getStatusAttr
     * @param $value
     * @return mixed
     */
    public function getOrderStatusAttr($value)
    {
        //状态 0 待受理 1 已受理 2 已维修  3 确认维修
        $status = [0=>'待受理',1=>'已受理',2=>'正在维修',3=>'确认维修'];
        return $status[$value];
    }


    /**
     * Notes: 保修查询列表
     * User: jackin.chen
     * Date: 2021/3/5 下午5:40
     * function: getRepairList
     * @param int $id
     * @param array $page
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function getRepairList($id=0,$page=[1,10]){

         $query1 = $this->field(['id','community_code','device_id','desc','consignee_id','device_name','thumb','status','create_time'])
            ->whereIn('status','1,2,3') // 状态 0 待受理 1 已受理 2 已维修  3 确认维修
            ->whereIn('from_srv','1,2'); // 报修来源  1 前端业主 2 后台管理人员
        if(!empty($id)){
            return $query1->find($id);
         }else{
            list($current,$size,$device_id) = $page;
            $device_id && $query1->where('device_id','=',$device_id);
            $result = $query1->page($current,$size)->select();
            //$device_id && var_dump( $this->getLastSql());

           //不能直接使用  $query1->count(); 有BUG

            $query2 = $this->field(['id','community_code','device_id','desc','consignee_id','device_name','thumb','status','create_time'])
                ->whereIn('status','1,2,3') // 状态 0 待受理 1 已受理 2 已维修  3 确认维修
                ->whereIn('from_srv','1,2');

            $device_id && $query2->where('device_id','=',$device_id);
            $count = $query2->count();
           // $device_id && var_dump( $count);

            $totalPage = ceil($count/$size);//总页数
            $currPage = ($current > $totalPage ? $totalPage : $current);
            return ['result'=>$result,'totalPage'=>$totalPage,'currPage'=>intval($currPage)];
        }
    }

}