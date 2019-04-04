<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-10-15
 * Time: 11:29
 */

namespace App\Tool\HttpSet;


class HttpCurl
{
    const API='https://restapi.amap.com/v3/geocode/geo';
    const AK='c455334eab153814a523b971e7064ec7';
    public function __construct()
    {
        //
    }

    /**
     * @param $url 请求网址
     * @param bool $params 请求参数
     * @param int $ispost 请求方式
     * @param int $https https协议
     * @return bool|mixed
     */
    public static function curl($url, $params = false, $ispost = 0, $https = 0)
    {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        else {
            if ($params) {
                if (is_array($params)) {
                    $params = http_build_query($params);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            }
            else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }
    //baidu
    public static function BaiDuApi($address){
        $res=[];
        //$address='北京市海淀区上地十街10号';
        $url=self::API;
        $result['key']=self::AK;
        $result['address']=$address;
        $result['output']='json';
        $data=self::curl($url,$result,0,1);
        $data=json_decode($data);
        if($data->status==1&&!empty($data->geocodes)){
            $XYdata=$data->geocodes[0]->location;
            $datas=explode(',',$XYdata);
            $res['Xdata']=$datas[0];
            $res['Ydata']=$datas[1];
        }else{
            $res['Xdata']=0;
            $res['Ydata']=0;
        }
        return (array)$res;
    }
}