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

class Users extends Model {

    // 表名,不含前缀
    public $name = 'user';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    //protected $createTime = 'create_time';
   // protected $updateTime = 'update_time';

    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


    /**
     * Notes: 定义一对多模型 ； 在外健的模型中使用belongsTo定义关联关系
     * User: jackin.chen
     * Date: 2020/6/13 下午10:35
     * function: address
     * @return \think\model\relation\HasMany
     */
    public function address()
    {
        return $this->hasMany('UserAddress', 'user_id', 'id');
    }


    /**
     * Notes: 查询用户是否存在（每一个用户在一个小程序中openid是固定不变的）
     * User: jackin.chen
     * Date: 2020/6/6 下午9:38
     * function: getByOpenID
     * @param $openid
     * @return array|false|\PDOStatement|string|Model
     * @static
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function getByOpenID($openid)
    {
        $result = $this->where('openid', '=', $openid)->find();
        return  $result;// $result->toArray();
    }

    /**
     * Notes: 创建新用户
     * User: jackin.chen
     * Date: 2020/6/6 下午6:24
     * function: newUser
     * @param $openid
     * @return mixed
     * @static
     */
    public  function newUser($openid)
    {
        // 有可能会有异常，如果没有特别处理
        // 这里不需要try——catch
        // 全局异常处理会记录日志
        // 并且这样的异常属于服务器异常
        // 也不应该定义BaseException返回到客户端
        $user = self::create([
            'openid' => $openid,
            'joinip' => get_client_ip(),
            'createtime'=>format_time(), //创建时间
            'jointime'=>format_time(), //加入时间
        ]);
        return $user->id;
    }


    /**
     * Notes: 修改用户信息
     * User: jackin.chen
     * Date: 2020/6/6 下午11:15
     * function: modifyUser
     * @param $data
     * @return bool|mixed
     * @static
     * @throws \think\exception\DbException
     */
    public  function modifyUser($data){
        $id = $data['userId'];
        $userInfo = [
            'nickname'=>$data['userName'],
            'username'=>$data['userName'],
            'avatar'=>$data['userPhoto'],
            //'city'=>$data['city'],
            //'country'=>$data['country'],
            //'language'=>$data['language'],
            //'province'=>$data['province'],
            'gender'=>$data['gender'],
            'status'=>'normal', //正常状态
            'loginip'=>get_client_ip(), //正常状态
            'updatetime'=>format_time()
        ];
        if($user = self::get($id)) {
            foreach ($userInfo as $key => $item) {
                $user->$key = $item;
            }
            $user->save();
            return $user->id;
        }
        return false;
    }




}