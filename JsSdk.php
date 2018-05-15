<?php
namespace wx;
/**
 * Class JsSdk
 * 调用jssdk前要获取的配置项
 * 使用前先确定属性appId和jsApiTicketFile已定义
 * @package wx
 */
trait JsSdk
{

    /**
     * 调用JSSDK前要获取的配置项
     * 
     * @return array  （返回数组：["appId"],["timestamp"],["nonceStr"],["signature"]）
     * @throws \Exception  属性appId未定义就异常抛出
     */
    private function getSignPackage() {
        if (!isset($this->appId)){
            throw new \Exception('属性appId未定义', 404);
        }
        $jsapiTicket = $this->getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $wxConfig = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $wxConfig;
    }

    /**
     * 创建加密字符串
     *
     * @param int $length
     * @return string
     */
    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    
    
    /**
     * 获取jsapi_ticket
     * 
     * @return mixed
     * @throws \Exception  属性jsApiTicketFile未定义就异常抛出
     */
    private function getJsApiTicket() {
        if (!isset($this->jsApiTicketFile)){
            throw new \Exception('属性jsApiTicketFile未定义（jsapi_ticket文件存放路径）', 404);
        }
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode(file_get_contents($this->jsApiTicketFile));
        if (empty($data) ||$data->expire_time < time()) {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = $this->https_request($url);;
            $ticket = $res['ticket'];
            if ($ticket) {
                $data->expire_time = time() + 7000;
                $data->jsapi_ticket = $ticket;
                $fp = fopen($this->jsApiTicketFile, "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        } else {
            $ticket = $data->jsapi_ticket;
        }

        return $ticket;
    }
}