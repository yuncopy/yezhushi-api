<?php
/**
 * Created by PhpStorm.
 * FileName: Article.php
 * User: Administrator
 * Date: 2017/10/20
 * Time: 13:51
 */

namespace app\common\model;


use think\Model;

class Admin extends Model {

    // 表名,不含前缀
    public $name = 'admin';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


}