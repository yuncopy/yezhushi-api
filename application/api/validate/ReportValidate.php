<?php


namespace app\api\validate;


class ReportValidate extends Validator{

    protected $rule = [
        'userName' => ['require'],
        'userPhone' => ['require'],
        'areaId1' => ['require'],
        'areaId' => ['require','integer'],
        'address' => ['require'],
        'visiter' => ['require'],
        'temperature' => ['require'],
        'health' => ['require'],
    ];

    //定义规则
    protected $message = [
        'userName.require'=>'用户昵称必传',
        'areaId.require'=>'小区标记必传',
        'areaId.integer'=>'小区标记类型有误',
        'userPhoto.require'=>'用户手机必传',
        'areaId1.require'=>'小区地址必传',
        'address.require'=>'详细必传',
        'visiter.require'=>'来访身份必传',
        'temperature.require'=>'今日体温必传',
        'health.require'=>'今日健康状态必传',
    ];

    //定义验证场景
}