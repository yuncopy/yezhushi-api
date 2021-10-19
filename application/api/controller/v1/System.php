<?php
/**
 * Created by PhpStorm.
 * FileName: Article.php
 * User: Administrator
 * Date: 2017/10/28
 * Time: 10:18
 */

namespace app\api\controller\v1;

use think\Request;
use app\common\library\CacheKey;
use app\common\controller\WxsApi;

class System extends WxsApi {

    protected $allowMethod = array('get');

    //前置方法
    protected $beforeActionList = [


    ];

    /**
     * Notes: 获取系统配置信息
     * User: jackin.chen
     * Date: 2021/7/31 3:27 下午
     * function: getInfo
     * @param Request $request
     * @return \think\response\Json
     */
    public function getInfo(Request $request) {
        //获取小程序配置信息
        $configList = $this->getOrSetCache(CacheKey::SYSTEM_INFO,function (){
            return (new \app\common\model\Config)->getConfigInfo();
        });
        $result = [];
        foreach ($configList as  $item){
            if($item['name'] == 'system_hot'){ //热词处理成数组形式
                $result[$item['name']] = explode(',',$item['value']);
            }else{
                $result[$item['name']] = $item['value'];
            }
        }
        return self::json2($result);
    }

}