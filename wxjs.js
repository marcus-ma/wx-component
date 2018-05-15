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


/**
 * 配置验证项
 * @constructor
 */
const ValidConfig = function(target){
    // 是否开启调试模式
    this.debug = target.debug;
    // 必填，微信号AppID
    this.appId = target.appId;
    // 必填，生成签名的时间戳
    this.timestamp = target.timestamp;
    // 必填，生成签名的随机串
    this.nonceStr = target.nonceStr;
    // 必填，签名，见附录1
    this.signature = target.signature;
    // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
    this.jsApiList = target.jsApiList
};

/**
 * 配置分享项
 * @constructor
 */
const ShareConfig = function (target) {
    // 分享标题
    this.title = target.title;
    // 分享链接，记得使用绝对路径，不能用document.URL
    this.link = target.link;
    // 分享缩略图链接
    this.imgUrl = target.imgUrl;
    // 分享描述
    this.desc = target.desc;
    // 分享成功后执行的函数
    this.success = target.success();
    // 分享失败后执行的函数
    this.cancel = target.cancel();
};


let validConfig = new ValidConfig(vConfig);

let shareConfig = new ShareConfig(sConfig);


wx.config({
    debug: true,
    appId: validConfig.appId,
    timestamp: validConfig.timestamp,
    nonceStr: validConfig.nonceStr,
    signature: validConfig.signature,
    jsApiList: validConfig.jsApiList
});


wx.ready(function(){
    var options = {
        title: shareConfig.title,
        link: shareConfig.link,
        imgUrl:shareConfig.imgUrl,
        desc: shareConfig.desc,
        success: function () {
            alert("分享成功");
            // 用户确认分享后执行的回调函数
        },
        cancel: function () {
            //console.info('取消分享！');
            // 用户取消分享后执行的回调函数
        }
    };
    wx.onMenuShareTimeline(options); // 分享到朋友圈
    wx.onMenuShareAppMessage(options); // 分享给朋友
    wx.onMenuShareQQ(options); // 分享到QQ
});



wx.ready(function () {

    //分享到朋友圈
    wx.onMenuShareTimeline({
        title: shareConfig.title,
        link: shareConfig.link,
        imgUrl:shareConfig.imgUrl,
        desc: shareConfig.desc,
        success: function () {
            shareConfig.success();
        },
        cancel: function () {
            shareConfig.cancel();
        }
    });

    //分享给朋友
    wx.onMenuShareAppMessage({
        title: shareConfig.title,
        link: shareConfig.link,
        imgUrl:shareConfig.imgUrl,
        desc: shareConfig.desc,
        success: function () {
            shareConfig.success();
        },
        cancel: function () {
            shareConfig.cancel();
        }
    });

    wx.onMenuShareQQ({
        title: shareConfig.title,
        link: shareConfig.link,
        imgUrl:shareConfig.imgUrl,
        desc: shareConfig.desc,
        success: function () {
            shareConfig.success();
        },
        cancel: function () {
            shareConfig.cancel();
        }
    });
});
