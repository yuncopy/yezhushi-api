<?php

// 公共助手函数

if (!function_exists('__'))
{

    /**
     * 获取语言变量值
     * @param string    $name 语言变量名
     * @param array     $vars 动态变量值
     * @param string    $lang 语言
     * @return mixed
     */
    function __($name, $vars = [], $lang = '')
    {
        if (is_numeric($name) || !$name)
            return $name;
        if (!is_array($vars))
        {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }
        return think\Lang::get($name, $vars, $lang);
    }

}

if (!function_exists('format_bytes'))
{

    /**
     * 将字节转换为可读文本
     * @param int $size 大小
     * @param string $delimiter 分隔符
     * @return string
     */
    function format_bytes($size, $delimiter = '')
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 6; $i++)
            $size /= 1024;
        return round($size, 2) . $delimiter . $units[$i];
    }

}

if (!function_exists('datetime'))
{

    /**
     * 将时间戳转换为日期时间
     * @param int $time 时间戳
     * @param string $format 日期时间格式
     * @return string
     */
    function datetime($time, $format = 'Y-m-d H:i:s')
    {
        if (!$time) {
            return '';
        }
        $time = is_numeric($time) ? $time : strtotime($time);
        return date($format, $time);
    }

}

if (!function_exists('human_date'))
{

    /**
     * 获取语义化时间
     * @param int $time 时间
     * @param int $local 本地时间
     * @return string
     */
    function human_date($time, $local = null)
    {
        return \fast\Date::human($time, $local);
    }

}

if (!function_exists('cdnurl'))
{

    /**
     * 获取CDN的地址
     * @param int $time 时间戳
     * @param string $format 日期时间格式
     * @return string
     */
    function cdnurl($url)
    {
        return preg_match("/^https?:\/\/(.*)/i", $url) ? $url : think\Config::get('site.cdnurl') . $url;
    }

}


if (!function_exists('is_really_writable'))
{

    /**
     * 判断文件或文件夹是否可写
     * @param	string $file 文件或目录
     * @return	bool
     */
    function is_really_writable($file)
    {
        if (DIRECTORY_SEPARATOR === '/')
        {
            return is_writable($file);
        }
        if (is_dir($file))
        {
            $file = rtrim($file, '/') . '/' . md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === FALSE)
            {
                return FALSE;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return TRUE;
        }
        elseif (!is_file($file) OR ( $fp = @fopen($file, 'ab')) === FALSE)
        {
            return FALSE;
        }
        fclose($fp);
        return TRUE;
    }

}

if (!function_exists('rmdirs'))
{

    /**
     * 删除文件夹
     * @param string $dirname 目录
     * @param bool $withself 是否删除自身
     * @return boolean
     */
    function rmdirs($dirname, $withself = true)
    {
        if (!is_dir($dirname))
            return false;
        $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo)
        {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself)
        {
            @rmdir($dirname);
        }
        return true;
    }

}

if (!function_exists('copydirs'))
{

    /**
     * 复制文件夹
     * @param string $source 源文件夹
     * @param string $dest 目标文件夹
     */
    function copydirs($source, $dest)
    {
        if (!is_dir($dest))
        {
            mkdir($dest, 0755);
        }
        foreach (
        $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item
        )
        {
            if ($item->isDir())
            {
                $sontDir = $dest . DS . $iterator->getSubPathName();
                if (!is_dir($sontDir))
                {
                    mkdir($sontDir);
                }
            }
            else
            {
                copy($item, $dest . DS . $iterator->getSubPathName());
            }
        }
    }

}

if (!function_exists('mb_ucfirst'))
{

    function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_strtolower(mb_substr($string, 1));
    }

}



if (!function_exists('to_array')) {
    /**
     * Notes: 对象转数组
     * User: jackin.chen
     * Date: 2020/6/1 下午10:16
     * function: to_array
     * @param array $array
     * @return mixed
     */
    function to_array($array =[])
    {
        if ($array) {
            return json_decode(json_encode($array),true);
        }
    }
}

if(!function_exists('curl_request')){

    /**
     * Notes: CURL请求
     * User: jackin.chen
     * Date: 2020/6/2 下午10:06
     * function: curl_request
     * @param string $url 请求的地址
     * @param bool $post 请求的方式
     * @param array $params 请求的参数
     * @param bool $https 是否验证http证书  默认不验证http证书
     * @return mixed|string
     */
    function curl_request($url,$post=false,$params=[],$https=false){

        #初始化请求的参数
        $curl=curl_init();

        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);

        // 超时设置,以秒为单位
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        // 设置请求头
        $header = array(
            'Content-Type: application/json',
            'Accept: application/json',
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);


        #设置请求选项
        if($post){
            #设置发送post请求
            curl_setopt($curl,CURLOPT_POST,true);
            #设置post请求的参数
            curl_setopt($curl,CURLOPT_POSTFIELDS,$params);
        }
        #是否https协议的验证
        if($https){
            #禁止从服务器验证客户端本地的数据
            curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        }
        #发送请求
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);  // 获取的信息以文件流的形式返回
        $res=curl_exec($curl);
        $error = curl_error($curl);
        $errno = curl_errno($curl);
        $info = curl_getinfo($curl);

        //记录日志
        \think\Log::info([
            'url'=>$url,
            'out'=>$res,
            'error'=>$error,
            'errno'=>$errno,
            'http_code'=>$info['http_code'],
            'total_time'=>$info['total_time'],
        ]);

        if($res===false){
            $msg = 'error:' .$error;
            return $msg;
        }
        #关闭请求
        curl_close($curl);
        return $res;
    }
}



if(!function_exists('get_rand_char')){
    /**
     * Notes: 生成随机数
     * User: jackin.chen
     * Date: 2020/6/6 下午9:12
     * function: get_rand_char
     * @param $length
     * @return null|string
     */
    function get_rand_char($length)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for ($i = 0;
             $i < $length;
             $i++) {
            $str .= $strPol[rand(0, $max)];
        }
        return $str;
    }
}

if(!function_exists('ksort_key')){

    /**
     * Notes: 按键排序
     * User: jackin.chen
     * Date: 2020/6/11 下午10:26
     * function: ksort_key
     * @param $people
     * @param array $sort
     * @return array
     */
    function ksort_key($people,$sort = [
        'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
        'field'     => 'age',       //排序字段
    ]){
        $sortArray = array();
        foreach($people as $person){
            foreach($person as $key=>$value){
                if(!isset($sortArray[$key])){
                    $sortArray[$key] = array();
                }
                $sortArray[$key][] = $value;
            }
        }
        if($sort['direction']){
            array_multisort($sortArray[$sort['field']],$sort['direction'],$people);
        }
        return $people;
    }

}

if(!function_exists('format_time')){

    function format_time(){
        return time();
    }
}

if(!function_exists('get_client_ip')){

    function get_client_ip($type = 0) {
        $type       =  $type ? 1 : 0;
        static $ip  =   NULL;
        if ($ip !== NULL) return $ip[$type];
        if($_SERVER['HTTP_X_REAL_IP']){//nginx 代理模式下，获取客户端真实IP
            $ip=$_SERVER['HTTP_X_REAL_IP'];
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {//客户端的ip
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {//浏览当前页面的用户计算机的网关
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];//浏览当前页面的用户计算机的ip地址
        }else{
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u",ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }
}

