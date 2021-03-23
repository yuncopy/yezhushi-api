<?php


namespace app\api\validate;


class CodeValidate extends Validator{

    protected $rule = [
        'code' => ['require'],
    ];
    //定义规则


    //定义提示参数
    protected $message = [
        'code.require'=>'CODE码必填'
    ];

    //定义验证场景
}