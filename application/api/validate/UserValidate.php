<?php


namespace app\api\validate;


class UserValidate extends Validator{

    protected $rule = [
        'userName' => ['require'],
        'userPhoto' => ['require'],
        'city' => ['require'],
        'country' => ['require'],
        'language' => ['require'],
        'province' => ['require'],
        'gender' => ['require'],
    ];


    //定义规则
    protected $message = [
        'userName.require'=>'用户昵称必传',
        'userPhoto.require'=>'用户头像必传',
        'city.require'=>'用户所在城市',
        'country.require'=>'用户所在国家',
        'province.require'=>'用户所在国家省份',
        'gender.require'=>'用户性别必传',
    ];

    //定义验证场景
}