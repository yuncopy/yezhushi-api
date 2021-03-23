<?php


namespace app\api\validate;


class UserAddressValidate extends Validator{

    protected $rule = [
        'addressId' => ['require','integer'],
        'userName' => ['require'],
        'userPhone' => ['require'],
        'areaId1' => ['require'],
        'areaId' => ['require','integer'],
        'address' => ['require'],
        'isDefault' => ['require','in'=>[0,1]],
    ];


    //定义规则
    protected $message = [
        'addressId.require'=>'地址标记必传',
        'addressId.integer'=>'地址标记类型有误',
        'areaId.require'=>'小区标记必传',
        'areaId.integer'=>'小区标记类型有误',
        'userName.require'=>'用户昵称必传',
        'userPhoto.require'=>'用户手机必传',
        'areaId1.require'=>'小区地址必传',
        'address.require'=>'详细必传',
        'isDefault.require'=>'是否默认必传',
    ];

    //定义验证场景
}