<?php
namespace wx;

/**
 * Class AccountsPlatform
 * @package wx
 */
trait AccountsPlatform
{


    //消息的回复
    private function responseMsg()
    {
        $postArr = $GLOBALS['HTTP_RAW_POST_DATA'];  //预定义变量，获取原生POST数据
        if (!empty($postArr))
        {
            $postObj = simplexml_load_string($postArr);  //把xml字符串载入到对象中，如果失败，则返回false
            $msgType = strtolower($postObj->MsgType);//消息类型
            $result = null;
            //根据消息类型进行业务处理
            if(!array_key_exists($msgType,$this->msgType)) {
                $result = $this->responseText($postObj,"没有该服务");
            }else{
                $value = $this->msgType[$msgType];
                $result = $this->$value($postObj);
            }
            echo $result;
        }else
        {
            echo '';
            exit;
        }

    }

    //回复文本类型的微信消息
    protected function responseText($postObj,$content){

        $template ="<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>";

        $toUser   =$postObj->FromUserName;
        $fromUser =$postObj->ToUserName;
        $time     =time();
        $msgType  ='text';
        $info     =sprintf($template,$toUser,$fromUser,$time,$msgType,$content);
        return $info;
    }
    
    




    /**
     * 设置菜单
     *
     * @param null $postmenu (请按照微信菜单的格式填写菜单，
     * 定义type为CLICK类型事，如果要响应图文事件，key一定得含有"TUWEN"字眼)
     * @return bool
     */

    private function createMenu($postmenu = null){
        $access_token=$this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";
        if (!$postmenu)
        {
            $postmenu='{
                    "button":[
                     {    
                          "name":"关于我们",
                          "sub_button":[
                           {    
                               "type":"view",
                               "name":"官网地址",
                               "url":"http://www.lambdass.com"
                            },
                            {
                               "type":"click",
                               "name":"澜达网络",
                               "key":"LAMBDATUWEN"
                            },
                            {
                                "type":"click",
                                "name":"在线客服",
                                "key":"staffs"
                            }
                            ]
                      },
                      {
                           "name":"百度",
                           "type":"view",
                           "url":"http://www.baidu.com"
                      },
                      {
                           "name":"成功项目",
                           "sub_button":[
                           {    
                               "type":"view",
                               "name":"原本佛山",
                               "url":"http://www.ybfoshan.com/"
                            },
                            {
                               "type":"view",
                               "name":"佛大官网",
                               "url":"http://web.fosu.edu.cn/"
                            },
                            {
                               "type":"view",
                               "name":"凌达工作室",
                               "url":"http://web.fosu.edu.cn/lambda/"
                            }]
                       }]
                  }';
        }

        $result=$this->https_request($url,$postmenu);
        if (!$result['errcode']){
            $res=true;
        }else{
            $res=$result['errmsg'];
        }
        return $res;
    }

    /**
     * 查询菜单
     *
     * @return mixed|string
     */

    private function selectMenu(){
        $access_token=$this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$access_token}";
        return $this->https_request($url);
    }

    /**
     * 删除菜单
     *
     * @return bool
     */

    private function deleteMenu(){
        $access_token=$this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token={$access_token}";
        $result=$this->https_request($url);
        if (!$result['errcode']){
            $res=true;
        }else{
            $res=$result['errmsg'];
        }
        return $res;
    }
}