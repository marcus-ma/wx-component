<?php

trait HandleText
{
    private $keywords = [
        "openid" => "您的openid是:",
        "voice"=>"",
        "photo"=>"",
        "你好" => '你好，谢谢您的订阅',
        "hello" => 'hello world! thanks your subscribe',
        "百度" => "<a href='http://www.baidu.com'>点击跳转到百度页面</a>",
        '单图文' => [
            [
                'Title'=>'lambda官网',
                'Description'=>'澜达网络公司介绍',
                'PicUrl'=>'http://www.lambdass.com/index_files/640.jpg',
                'Url'=>'http://www.lambdass.com/'
            ]
        ],
        '多图文' => [
            [
                'Title'=>'lambda官网',
                'Description'=>'澜达网络公司介绍',
                'PicUrl'=>'http://www.lambdass.com/index_files/640.jpg',
                'Url'=>'http://www.lambdass.com/',
            ],
            [
                'Title'=>'百度',
                'Description'=>'百度一下，你就知道',
                'PicUrl'=>'https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png',
                'Url'=>'http://www.baidu.com/',
            ],
        ]
    ];
    //设置关键字及相对应的内容
    public function _setKeywords($keywords = [])
    {
        if (!empty($keywords)){
            $this->keywords = $keywords;
        }
        return $this;
    }
    //关键字回复处理函数
    protected function receiveText($object)
    {
        //微信收到的关键词
        $content = trim($object->Content);
        //先前设定好的关键词
        $keywords = $this->keywords;
        //响应回复的内容
        $reply = null;

        //判断是否有符合的关键词
        if(!array_key_exists($content,$keywords)) {
            $reply = '抱歉，请求的资源不允许';
            return $this->responseText($object,$reply);
        }

        //根据关键字眼决定响应类型
        switch (true){
            case strchr($content,'图文'):
                return $this->responseNews($object,$keywords[$content]);
                break;
            case strchr($content,'图片'):
                return $this->responseImage($object,$keywords[$content]);
                break;
            case strchr($content,'语音'):
                return $this->responseVoice($object,$keywords[$content]);
                break;
            case strchr($content,'openid'):
                $reply = $keywords[$content]." ".$object->FromUserName;
                return $this->responseText($object,$reply);
                break;
            default:
                $reply = '暂不提供相关功能';
                return $this->responseText($object,$reply);
        }


    }
}