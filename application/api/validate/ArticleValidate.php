<?php


namespace app\api\validate;


class ArticleValidate extends Validator{

    protected $rule = [
        'id' => ['require','integer'],
    ];


    //定义规则
    protected $message = [
        'id.require'=>'文章ID必传',
        'id.integer'=>'文章ID类型有误'
    ];

    //定义验证场景
}