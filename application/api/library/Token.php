<?php


namespace app\api\library;



class Token
{

    /**
     * Notes: 生成令牌
     * User: jackin.chen
     * Date: 2020/6/6 下午9:20
     * function: generateToken
     * @return string
     * @static
     */
    public static function generateToken()
    {
        $randChar = get_rand_char(32);
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        $tokenSalt = config('token_salt');
        return md5($randChar . $timestamp . $tokenSalt);
    }
}