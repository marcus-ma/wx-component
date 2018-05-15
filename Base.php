<?php
namespace wx;
/**
 * Class Base
 * 微信功能基类，所有其他业务类使用前必需先引入它
 * 使用前先确定属性token、appId、appSecret和accessTokenFile已定义
 * @package wx
 */
trait Base
{
    //接入微信
    public function valid(){
        if (!isset($this->token)){
            throw new \Exception('属性对接微信服务器的属性token未定义', 404);
        }
        if ($this->checkSignature($this->token))
        {
            return $this;
        }
        else
        {
            return 'invalid!';
        }
    }

    //验证微信签名
    private function checkSignature($validToken)
    {
        $timestamp = $_GET['timestamp'];//时间戳
        $nonce = $_GET['nonce'];//随机数
        $token = $validToken;//口令
        $signature = $_GET['signature'];//微信加密签名
        $echostr = $_GET['echostr'];//随机字符串

        //开始加密校验
        //1.将$timestamp,$nonce,$token三个参数进行字典序排序
        $array = array($timestamp, $nonce, $token);
        sort($array);
        //2.将排序后的三个参数拼接之后用sha1加密
        $tmpstr = implode('', $array);
        $tmpstr = sha1($tmpstr);
        //3.将加密后的字符串与signature进行对比，判断该请求是否来自微信
        if ($tmpstr == $signature && $echostr) {
            //第一次接入微信api接口的时候
            echo $echostr;
            exit();
        } else {
            return true;
        }
    }

    /**
     * http请求（发起HTTPS请求，返回数组）
     *
     * @param $url （传入要发起请求的URL）
     * @param null $data （发起请求的post数据）
     * @return mixed|string  （返回数组）
     */
    protected function https_request($url,$data=null){
        $curl=curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);//用于验证第三方服务器与微信服务器的安全性，若在SAE，BAE平台则不需要
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);//用于验证第三方服务器与微信服务器的安全性，若在SAE，BAE平台则不需要
        if(!empty($data)){
            curl_setopt($curl,CURLOPT_POST,1);//模拟post请求
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//post提交数据
        }
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);//将页面以文件流的形式保存，1为不显示数据在页面上
        $output= curl_exec($curl);
        if(curl_error($curl)){ return 'ERROR'.curl_error($curl); }//输出错误信息
        curl_close($curl);
        return json_decode($output,true);//将json转换成数组格式返回，第二个参数默认返回为对象，true为返回数组
    }



    /**
     * 获取access_token（接口调用凭证,最好把token缓存起来）
     *
     * @return bool (获取成功则返回token，否则返回false)
     * @throws \Exception 属性accessTokenFile、appId和appSecret未定义就异常抛出
     */
    protected function getAccessToken() {
        if (!isset($this->accessTokenFile)){
            throw new \Exception('属性accessTokenFile未定义（accessToken文件存放路径）', 404);
        }

        if (!isset($this->appId) && !isset($this->appSecret)){
            throw new \Exception('属性appId或者appSecret未定义', 404);
        }
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode(file_get_contents($this->accessTokenFile));
        if (empty($data) || $data->expire_time < time()) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/tokm en?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $res = $this->https_request($url);
            $access_token = $res['access_token'];
            if ($access_token) {
                $data->expire_time = time() + 7000;
                $data->access_token = $access_token;
                $fp = fopen($this->accessTokenFile, "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        } else {
            $access_token = $data->access_token;
        }
        return $access_token;
    }

    /**
     * 判断是否从微信中打开链接
     * 
     * @return bool
     */
    protected function isFromWeChat(){
        return strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger');
    }



    
}