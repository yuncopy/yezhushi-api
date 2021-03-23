<?php


namespace app\api\validate;


class AddressIdValidate extends Validator{

    protected $rule = [
        'id' => ['require','integer'],
    ];


    //定义规则
    protected $message = [
        'id.require'=>'用户地址标记必传',
        'id.integer'=>'用户地址标记类型有误'
    ];

    //定义验证场景
}