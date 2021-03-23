<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * 路由注册
 *
 * 以下代码为了尽量简单，没有使用路由分组
 * 实际上，使用路由分组可以简化定义
 * 并在一定程度上提高路由匹配的效率
 */

// 写完代码后对着路由表看，能否不看注释就知道这个接口的意义
use think\Route;

//如果有定义绑定后台模块则禁用路由规则 
if (\think\Route::getBind('module') == 'admin')
    return [];


//获取系统配置信息
Route::get('api/:version/system/load_config', 'api/:version.System/getInfo');

//Token
Route::post('api/:version/token/user', 'api/:version.User/getToken');
Route::post('api/:version/token/verify', 'api/:version.User/verifyToken');
Route::post('api/:version/token/modify', 'api/:version.User/modifyUser');

Route::post('api/:version/token/app', 'api/:version.User/getAppToken');


//我的
Route::post('api/:version/user/get_my_info', 'api/:version.User/getMyInfo');
Route::post('api/:version/user/save_report', 'api/:version.Report/saveReport');
Route::get('api/:version/user/get_report', 'api/:version.Report/getReport');


//首页
Route::post('api/:version/index/get_article_list', 'api/:version.Index/getArticleList');
Route::post('api/:version/repair/get_property_list', 'api/:version.Repair/getRepairList');
Route::post('api/:version/index/recom_shops_list', 'api/:version.Index/recomShopsList');
Route::post('api/:version/index/get_repair_list', 'api/:version.Index/getRepairList');
Route::post('api/:version/index/get_activity_list', 'api/:version.Activity/getActivityList');
Route::get('api/:version/index/get_banner', 'api/:version.Index/getBanner');


//报修页面
Route::get('api/:version/repair/get_repair_type', 'api/:version.Repair/getRepairType');
Route::get('api/:version/repair/get_estate_list', 'api/:version.Repair/getEstateList');
Route::post('api/:version/repair/repair_image', 'api/:version.Repair/repairImage');
Route::post('api/:version/repair/submit_repair', 'api/:version.Repair/submitRepairOrder');
Route::post('api/:version/repair/finish_order', 'api/:version.Repair/finishRepairOrder');


//投票页面
Route::get('api/:version/vote/get_vote_banner', 'api/:version.Vote/getBanner');
Route::get('api/:version/vote/get_vote_subject', 'api/:version.Vote/getSubject');
Route::post('api/:version/vote/get_vote_player', 'api/:version.Vote/getSubjectPlayer');
Route::post('api/:version/vote/get_subject_list', 'api/:version.Vote/getSubjectList');
Route::post('api/:version/vote/get_subject_player_list', 'api/:version.Vote/getSubjectPlayerList');
Route::post('api/:version/vote/get_player_info', 'api/:version.Vote/getPlayerInfo');
Route::post('api/:version/vote/to_submit_vote', 'api/:version.Vote/submitVote');

//查看文章
Route::get('api/:version/article/:id', 'api/:version.Article/read',[], ['id'=>'\d+']);

//用户地址
Route::post('api/:version/address/save_address', 'api/:version.Address/userAddressOption');
Route::post('api/:version/address/set_default', 'api/:version.Address/setDefaultAddress');
Route::get('api/:version/address/get_address', 'api/:version.Address/getUserAddress');
Route::post('api/:version/address/delete_address', 'api/:version.Address/deleteAddress');



return [
    //别名配置,别名只能是映射到控制器且访问时必须加上请求的方法
//    '__alias__'   => [
//        'demo' => 'admin/Test',
//    ],
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
//        域名绑定到模块
//        '__domain__'  => [
//            'admin' => 'admin',
//            'api'   => 'api',
//        ],
    // 定义资源路由
    '__rest__' => [ //使用 资源路由 具体查看手册
        'article' => 'api/general.article',
        'category' => 'api/general.category',
        'frontnav' => 'api/general.frontNav',
        'slide' => 'api/general.slide',
        'friendlylink' => 'api/general.friendlyLink',
    ]
];

