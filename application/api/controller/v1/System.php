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
use app\api\library\ConstStatus;
use app\common\library\CacheKey;
use app\common\controller\BaseApi;
use app\common\model\Config as ConfigModel;

class System extends BaseApi {

    private $configModel = null;
    protected $allowMethod = array('get');

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->configModel = new ConfigModel();
    }

    /**
     * Notes: 获取系统配置信息
     * User: jackin.chen
     * Date: 2020/6/3 上午12:28
     * function: getInfo
     * @param Request $request
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getInfo(Request $request) {

        //获取小程序配置信息
        $configList = $this->getOrSetCache(CacheKey::SYSTEM_INFO,function (){
            return $this->configModel->field('name,type,value')->where(['group'=>'system'])->select();
        });

        $result = [];
        foreach ($configList as  $item){
            if($item['name'] == 'system_hot'){
                $result[$item['name']] = explode(',',$item['value']);
            }else{
                $result[$item['name']] = $item['value'];
            }
        }
        return parent::response($result,ConstStatus::CODE_SUCCESS);
    }

}