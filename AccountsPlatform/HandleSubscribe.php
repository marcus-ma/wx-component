<?php
namespace wx\AccountsPlatform;

trait HandleSubscribe
{
    private $subscribeContent = "hello！欢迎关注我的公众号";

    //设置关注后自动回复的信息
    public function _setSubscribe($content = '')
    {
        if (!empty($content)){
            $this->subscribeContent = $content;
        }
        return $this;
    }

    //接收事件处理函数
    protected function receiveEvent($object)
    {
        $result = '';
        switch (trim($object->Event)) {
            case 'subscribe':
                if (method_exists($this,'subscribeHook')){
                    $this->subscribeHook($object->FromUserName);
                }
                
                if (substr($object->EventKey, 0, 8) == 'qrscene_') {//之前未关注，现通过带参数的扫码关注
                    $content = 'hello！欢迎您通过扫码关注我的公众号';
                    $result = $this->responseText($object, $content);
                    /*
                        此处你可以进行数据库操作
                        插入  value值=$postObj->EventKey=qrscene_生成参数
                      */
                } else {
                    $content = $this->subscribeContent;
                    $result = $this->responseText($object, $content);
                }
                break;
            case 'unsubscribe':
                if (method_exists($this,'unsubscribeHook')){
                    $this->unsubscribeHook($object->FromUserName);
                }
                $content = "取消关注";
                $result = $this->responseText($object, $content);
                //账号解绑
                break;

        }
        return $result;
    }
}