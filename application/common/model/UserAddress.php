<?php

namespace app\common\model;


use think\Model;

class UserAddress extends Model
{
   //protected $hidden =['id', 'delete_time', 'user_id'];

    // 表名,不含前缀
    public $name = 'user_address';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    public function community(){
        return $this->belongsTo('Community','community_id','id');
    }

    public function user(){
        return $this->belongsTo('Users','user_id','id');
    }


    /**
     * Notes: 隐藏手机号中间四位读取器
     * User: jackin.chen
     * Date: 2020/6/26 下午8:15
     * function: getPhoneAttr
     * @param $value
     * @return mixed
     */

    public function getPhoneAttr($value)
    {
        return substr_replace($value,'****',3,4);
    }

}


