<?php
/**
 * Created by PhpStorm.
 * FileName: ConstStatus.php
 * User: Administrator
 * Date: 2017/10/28
 * Time: 13:38
 */

namespace app\api\library;

/**
 * 接口访问权限枚举
 * 这种权限涉及是逐级式
 * 简单，但不够灵活
 * 最完整的权限控制方式是作用域列表式权限
 * 给每个令牌划分到一个SCOPE作用域，每个作用域
 * 可访问多个接口
 */
class ScopeEnum
{
    //用户权限
    const User = 16;

    const Super = 32;



    const UID_KEY = 'uid';
    const SCOPE_KEY = 'scope';
}