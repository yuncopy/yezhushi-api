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

class Article extends Model {

    // 表名,不含前缀
    public $name = 'article';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'publish_time';

    // 增删改查时必需的字段，为空则不作限制
    public $requiredField = array(
        'find' => array('id' => 'id'),
        'list' => array(),
        'add' => array('cid' => '所属分类','title' => '标题'),
        'update' => array('cid' => '所属分类','title' => '标题'),
        'delete' => array('id' => 'id')
    );

    /**
     * Notes: 定义一对一模型
     * User: jackin.chen
     * Date: 2020/6/21 下午3:42
     * function: category
     * @return \think\model\relation\BelongsTo
     */
    public function category(){
        return $this->belongsTo('Category','cid','id');
    }


    /**
     * Notes: 获取文章分页数据
     * User: jackin.chen
     * Date: 2020/6/11 下午10:32
     * function: pageList
     * @param int $currPage
     * @param int $pageSize
     * @param int $weight
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pageList($currPage=1,$pageSize=6,$weight=null){

        //获取首页的类型
        $cate = Category::getCategoryArray('article','normal','index',$weight);

        //按照权重升序
        array_multisort (array_column($cate, 'weigh'), SORT_ASC, $cate);
        $cateName = array_column($cate,'name','id');
        $category = array_column($cate,'id');
        $data = [];
        foreach ($category as $cid){
            $list = $this->where([
                'cid'	=>	$cid,
                'status'=>	1 //状态 0 待审核  1 审核
            ])->field('id as articleId,create_time as createTime,title as articleTitle,thumb as articleImg,introduction as articleKey')
                ->order(['is_top'=>'desc','is_recommend'=>'desc','id'=>'desc'])
                ->paginate($pageSize,false,['page'=>$currPage]);
            $data[] = [
                'catId'=>$cid,
                'catName'=>$cateName[$cid],
                'articleChildList'=>[
                    'currPage'=>$currPage,
                    'totalPage'=>ceil($list->total()/$pageSize),
                    'pageSize'=>$pageSize,
                    'root'=>$list->items()
                ]
            ];
        }
        return $data;
    }

    /**
     * Notes: 定义时间获取器 - 统一在前端处理
     * User: jackin.chen
     * Date: 2020/6/21 下午3:40
     * function: getCreateTimeAttr
     * @param $value
     * @return false|string
     */
    /*
    public function getCreateTimeAttr($value)
    {
        return date('Y-m-d H:i:s',$value);
    }*/

    /**
     * Notes:获取文件详情
     * User: jackin.chen
     * Date: 2020/6/27 下午8:22
     * function: getArticleInfo
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getArticleInfo($id){
        $query = $this->with('category')->field('id,cid,reading,title as articleTitle,create_time as createTime,content as articleContent,cid as catId')
            ->where(['id'=>$id, 'status'=>1]);
        $articleInfo = [];
        if($article = $query->find()){
            //记录阅读量
            $articleInfo['id'] = $article['id'];
            $articleInfo['cid'] = $article['cid'];
            $articleInfo['catId'] = $article['catId'];
            $articleInfo['cidName'] = $article['category']['name'];
            $articleInfo['articleTitle'] = $article['articleTitle'];
            $articleInfo['createTime'] = $article['createTime'];
            $articleInfo['articleContent'] = $article['articleContent'];

            $article->setInc('reading',1);
        }
        return $articleInfo;
    }

    /**
     * Notes: 获取文章分页数据
     * User: jackin.chen
     * Date: 2021/3/9 下午4:43
     * function: pageArticle
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pageArticle($params){

        //近一年的数据
        $time = time() - 31536000;  //24*60*60 * 365
        $keyWord = $params['brands'];
        $current = $params['p'];
        $size = isset($params['cnt']) ? $params['cnt'] : 6;

        $field = 'id,cid,thumb,title,introduction,reading,create_time';
        $search_field = 'title|introduction';

        //查询结果
        $where =[
            $search_field=>['like','%'.$keyWord.'%'],
            'create_time'=>['>',$time],
            'status'=>['=',1]
        ];

        //查询记录数
        $count = $this->field($field)->where($where)->count();

        $result = $this->with('category')->field($field)->where($where)
            ->order(['sort'=>'desc','is_top'=>'desc','is_recommend'=>'desc'])
            ->page($current,$size)->select();
        if($result){
            collection($result)->visible(explode(',',$field.',category.name'))->each(function ($item){
                $item->createTime =  date('Y-m-d H:i:s',$item->create_time);
            });
        }

        //!empty($params['brands'])  && var_dump($this->getLastSql());

        $totalPage = ceil($count/$size);//总页数
        $currPage = ($current > $totalPage ? $totalPage : $current);
        return ['result'=>$result,'totalPage'=>$totalPage,'currPage'=>intval($currPage)];

    }
}