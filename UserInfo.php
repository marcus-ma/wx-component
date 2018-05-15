<?php
namespace wx;

/**
 * Class UserInfo
 * 获取用户身份信息
 * 使用前先确定属性appId和appSecret已定义
 * @package wx
 */
trait UserInfo
{
    /**
     * 拼接oauth2授权的连接
     *
     * @param $redirect_uri(要跳转到URL)
     * @param bool $snsapi (授权模式，默认为base型：获取openid；填写true为user_info型：获取用户的openid,头像，呢称，地区)
     * @param int $state （重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节）
     * @return string 生成拼接完的URL
     * @throws \Exception 属性appId未定义就异常抛出
     */
    private function oauth2URL($redirect_uri, $snsapi = false, $state=123)//重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节
    {
        if (!isset($this->appId)){
            throw new \Exception('属性appId未定义', 404);
        }
        $snsapi = $snsapi ? "snsapi_userinfo":"snsapi_base";
        $appID=$this->appId;
        $redirect_uri=urlencode($redirect_uri);
        //准备Scope为snsapi的网页授权页面URL
        $snsapi_url="https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appID}&redirect_uri={$redirect_uri}&response_type=code&scope={$snsapi}&state={$state}#wechat_redirect";
        return $snsapi_url;
    }

    /**
     * base型授权（只获取用户的openid）
     *
     * @return array|bool (返回数组[openid],若调用oauth2时带了参数则有[state])
     * @throws \Exception 属性appId和appSecret未定义就异常抛出
     */
    private function getUserOpenid(){
        if (!isset($this->appId) && !isset($this->appSecret)){
            throw new \Exception('属性appId或者appSecret未定义', 404);
        }

        $appID=$this->appId;
        $appSecret=$this->appSecret;
        if (!isset($_GET['code']))//如果获取不到，提示获取不到code
        {
            $res = ['error' => 404, 'info' => 'code catch failure'];
            return $res;
        }
        if (isset($_GET['state']))
        {
            $res['state'] = $_GET['state'];
        }
        $code=$_GET['code'];
        //通过code获取网页授权access_token
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appID}&secret={$appSecret}&code={$code}&grant_type=authorization_code";
        if ($this->https_request($url)){
            $result=$this->https_request($url);
            $res['openid'] = $result['openid'];
        }else{
            $res=false;
        }
        return $res;
    }

    /**
     * user_info型授权（获取用户的
     * openid['openid'],
     * 头像['headimgurl']，
     * 呢称['nickname']，
     * 性别['sex']，
     * 国家['country']，省['province']，城市['city']，
     * 是否关注该公众号['subscribe']）
     *
     * @return array|bool|string (返回数组[info]为二维数组,若调用oauth2时带了参数则有[state])
     * @throws \Exception 属性appId和appSecret未定义就异常抛出
     */
    private function getUserInfo(){
        if (!isset($this->appId) && !isset($this->appSecret)){
            throw new \Exception('属性appId或者appSecret未定义', 404);
        }

        $appID=$this->appId;
        $appSecret=$this->appSecret;
        if (!isset($_GET['code']))//如果获取不到，提示获取不到code
        {
            $res = ['error' => 404, 'info' => 'code catch failure'];
            return $res;
        }
        if (isset($_GET['state']))
        {
            $res['state'] = $_GET['state'];
        }
        $code=$_GET['code'];
        //通过code获取网页授权access_token
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appID}&secret={$appSecret}&code={$code}&grant_type=authorization_code";
        $result=$this->https_request($url);
        if (!$result){
            $res = false;
        }elseif ($result['errcode']){
            $res='get access_token false:'.$result['errmsg'].' error:'.$result['errcode'];
        }else{
            $access_token=$result['access_token'];
            $open_id=$result['openid'];
            //根据上一步获取的access_token和openid拉取用户信息
            $info_url="https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$open_id}&lang=zh_CN";
            $result=$this->https_request($info_url);
            if (!$result){
                $res=false;
            }elseif ($result['errcode']){
                $res='get user_info false:'.$result['errmsg'];
            }else{
                $res['info'] =  $result ;
            }
        }
        return $res;
    }

    /**
     * 过滤微信昵称特殊字符
     * @param $str
     * @return mixed
     */
    private function filter($str) {
        $str = preg_replace_callback( '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);
        $str=trim($str);
        return $str;

    }
}