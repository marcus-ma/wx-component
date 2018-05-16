# wx-component （拿来即用的常用微信开发组件库）

#使用说明
--
该组件库意在提供一些已封装好的常用微信H5、公众号等开发业务逻辑的功能，开发者可以根据实际业务场景导入相关的业务组件，减少理解成本，降低开发难度，拿来即用。

#1起步（接入微信服务）
--
在要使用的微信功能的业务文件中，先引入基类组件Base.php(使用use引入),定义好属性token、appId、appSecret
```php
<?php
namespace controller;
use wx\Base;

class demo
{
    //引入基类组件Base
    use Base;
    //跟微信服务器对接校验的Token
    private $token = 'weixin';
    //微信公众号的唯一appId
    private $appId = '';
    //微信公众号的唯一appSecret
    private $appSecret = '';

    //微信接入检验方法
    public function wxInit()
    {
        //valid()为Base组件中对接微信服务器的方法
        $this->valid();
    }
}

```

#2入门（组件介绍）
--
**#wx-component**
       根目录文件

      **#Base.php**
          微信功能基类，所有其他业务类使用前必需先引入它。
          使用前先确定属性token、appId、appSecret和accessTokenFile已定义

      **#UserInfo.php**
          获取用户身份信息业务组件。使用前先确定属性appId和appSecret已定义

      **#JsSdk.php**
          调用jssdk(微信朋友圈分享、好友分享等js功能)前要获取的配置项业务组件。使用前先确定属性appId和jsApiTicketFile已定义

      **#AccountsPlatform.php**
          公众号相关功能开发业务组件。使用前先确定属性appId、appSecret和msgType已定义

      **##AccountsPlatform**
          公众号具体功能业务文件，里面包含具体事件响应功能开发业务组件。

<br/>

#3具体使用（组件功能实例）
--

### 1.UserInfo组件（处理获取用户身份信息业务）
```php
<?php
namespace controller;
use wx\Base;
use wx\UserInfo;
class demo
{
    //引入基类组件Base和授权获取用户信息组件UserInfo
    use Base,UserInfo;
    //微信公众号的唯一appId
    private $appId = '';
    //微信公众号的唯一appSecret
    private $appSecret = '';
    //定义accesstoken文件处在路径
    private $accessTokenFile = 'xxx.json';

     public function startIn()
        {
            /**
             * oauth2授权连接
             *
             * @param $redirect_uri (要跳转到URL)
             * @param bool $snsapi  (授权模式，默认为base型：获取openid；填写true为user_info型：获取用户的openid,头像，呢称，地区)
             * @param int $state  （重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节）
             * @return string 生成拼接完的URL
             */
            // http://xxxxx/getUseStatus 为项目服务器地址
            $url = $this->oauth2URL('http://xxxxx/getUseStatus',true);
            echo "<script language='javascript'>";
            echo " location='".$url."';";
            echo "</script>";
        }

        public function getUseStatus()
        {
            /**
             * user_info型授权（获取用户的openid,头像，呢称，地区）
             *
             * @return array|bool|string  (返回数组[info]为二维数组,若调用oauth2时带了参数则有[state])
             */
            $res = $this->getUserInfo();
            //获取头像
            $img = $res['info']['headimgurl'];
            //获取openid
            $openid = $res['info']['openid'];
            //获取过滤特殊字符后的微信昵称
            $nickname = $this->filter($res['info']['nickname']);
            //获取该用户是否关注的公众号（有则为true）
            $subscribe = $res['info']['subscribe'];
        }
}

```

### 2.JsSdk组件（调用jssdk(微信朋友圈分享、好友分享等js功能)前要获取的配置项）
```php
<?php
namespace a;
use wx\Base;
use wx\JsSdk;
class demo
{
    use Base,JsSdk;
    private $appId = '';
    private $appSecret = '';
    private $accessTokenFile = 'xxx.json';
    private $jsApiTicketFile = 'xxx.json';

    public function getJsInit()
    {
        $res = $this->getSignPackage();
        $appId = $res['appId'];
        $timestamp = $res['timestamp'];
        $nonceStr = $res['nonceStr'];
        $signature = $res['signature'];
    }
}

```

### 3.AccountsPlatform组件（处理公众号相关功能开发业务）
```php
<?php
namespace a;
use wx\AccountsPlatform;
use wx\Base;

class demo
{
    //引入基类组件Base和公众号相关功能开发业务组件AccountsPlatform
    use Base,AccountsPlatform,

        //引入要处理的消息类型
        //（处理关注事件HandleSubscribe）
        //（处理关键词回复事件HandleSubscribe）
        AccountsPlatform\HandleSubscribe,
        \HandleText;

    private $token = 'weixin';
    private $appId = '';
    private $appSecret = '';
    private $accessTokenFile = 'xxx.json';

    //定义要处理的消息业务类型,配置项从以下选
    //现暂时只提供“关注事件”和“关键词回复事件"
    /**
     * @var array
     *
     *
            （事件类型：关注事件）'event' => 'receiveEvent',
            （文本类型：关键字回复）'text' => 'receiveText',
            （位置类型：调用LBS地理信息）'location' => 'receiveLocation',
            （图片类型：图片信息）'image' => 'receiveImage',
            （声音类型：声音信息）'voice' => 'receiveVoice',
     */
    private $msgType = [
        'event' => 'receiveEvent',
        'text' => 'receiveText',
    ];

    //此处可进行处理用户关注后的一些业务逻辑，默认参数为用户的openid
    private function subscribeHook($openid){
        //Todo
        //例如关注后将该用户的openid记录在数据库
    }


    public function wxInitForSubscribe()
    {
        //_setSubscribe为设置用户关注公众号后收到的信息
        $this->_setSubscribe('欢迎关注我的公众号')
            ->valid()
            ->responseMsg();
    }

    public function wxInitForKeyWord()
    {
        //_setKeywords为设置关键词回复
        $this->_setKeywords([
            'hello'=>"hello world! thanks your subscribe",
            "openid" => "您的openid是:",
        ])
            ->valid()
            ->responseMsg();
    }


    //创建菜单
    public function menuCreate()
    {
        //创建菜单 页面显示true则菜单创建成功
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
                               "key":"LAMBDA"
                            }]
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
        $res = $this->createMenu($postmenu);
        var_dump($res);
    }
    //删除菜单
    public function menuDelete(){
        $res = $this->deleteMenu();
        var_dump($res);
    }
    //查询菜单
    public function menuSelect(){
        $res = $this->selectMenu();
        var_dump($res);
    }
}

```

### 4.wxjs（调用微信分享功能）

```html
<script src="//res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
const vConfig = {
    debug:true,
    appId:'',
    timestamp:'',
    nonceStr:'',
    signature:'',
    jsApiList:[
        'onMenuShareTimeline', //分享到朋友圈
        'onMenuShareAppMessage', //分享给朋友
        'onMenuShareQQ' //分享到QQ
    ]
};

const sConfig = {
    title:'',
    link:'',
    imgUrl:'',
    desc:'',
    success:function () {
        alert('分享成功');
    },
    cancel: function () {
        alert('分享失败');
    }
};
</script>
<script src="wxjs.js"></script>
```
