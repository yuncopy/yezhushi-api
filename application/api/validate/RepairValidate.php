<?php


namespace app\api\validate;


class RepairValidate extends Validator{

    protected $rule = [
        'consigneeId' => ['require','integer'],
        'orderRemarks' => ['require'],
        'fid' => ['require','integer']
    ];


    //定义规则
    protected $message = [
        'consigneeId.require'=>'用户地址必传',
        'consigneeId.integer'=>'用户地址类型有误',
        'orderRemarks.require'=>'报修描述必填',
        'fid.integer'=>'报修设备类型有误',
        'fid.require'=>'报修设备必传'
    ];

    //定义验证场景
}