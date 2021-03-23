<?php


namespace app\api\validate;


class RepairOrderIdValidate extends Validator{

    protected $rule = [
        'orderId' => ['require','integer'],
    ];

    //定义规则
    protected $message = [
        'orderId.require'=>'报修订单标记必传',
        'orderId.integer'=>'报修订单标记类型有误'
    ];

    //定义验证场景
}