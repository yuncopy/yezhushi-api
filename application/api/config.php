<?php
//配置文件
return [

    // +----------------------------------------------------------------------
    // | 异常及错误设置
    // +----------------------------------------------------------------------
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle'       => '\app\api\exception\ExceptionHandler',


    //  +---------------------------------
    //  微信相关配置
    //  +---------------------------------
    // 小程序app_id
    'app_id' => 'wx5db95f429f375736',
    // 小程序app_secret
    'app_secret' => '7789f6d9772e762d4a687380ecb04e49',

    // 微信使用code换取用户openid及session_key的url地址
    'login_url' => "https://api.weixin.qq.com/sns/jscode2session?" .
        "appid=%s&secret=%s&js_code=%s&grant_type=authorization_code",

    // 微信获取access_token的url地址
    'access_token_url' => "https://api.weixin.qq.com/cgi-bin/token?" .
        "grant_type=client_credential&appid=%s&secret=%s",

    //加密时盐
    'token_salt'=>'6512bd43d',

    //TOKEN有效期
    'token_expire_in'=>3600

];