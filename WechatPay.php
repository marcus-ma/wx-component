<?php

trait WechatPay
{

    public $secret;

    //统一下单参数，详细参数具体参考官方文档：https://pay.weixin.qq.com/wiki/doc/api/H5.php?chapter=9_20&index=1
    public $pay_params = [
        'appid' => "公众账号ID",
        'mch_id' => "商户ID",
        'nonce_str' => "随机字符串，长度要求在32位以内",
        'body' => '商品描述',
        'out_trade_no' => "商户系统内部订单号，要求32个字符内，只能是数字、大小写字母_-|* 且在同一个商户号下唯一",
        'total_fee' => "金额,单位为分",
        'spbill_create_ip' => "用户的客户端IP,支持IPV4和IPV6两种格式的IP地址",
        'notify_url' => "回调地址",
        'trade_type' => "支付类型：【MWEB:h5外链支付】，【NATIVE:给用户扫码支付】",
        'scene_info'=>"场景信息"
    ];

    //查询订单参数，详细参数具体参考官方文档：https://pay.weixin.qq.com/wiki/doc/api/H5.php?chapter=9_2&index=2
    public $check_params = [
        'appid' => "公众账号ID",
        'mch_id' => "商户ID",//商户id
        'out_trade_no'=>"商户系统内部订单号，要求32个字符内，只能是数字、大小写字母_-|* 且在同一个商户号下唯一",
        'nonce_str' => "随机字符串，长度要求在32位以内",
    ];

    //微信下单网关
    static $payEndPoint = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    //微信查单网关
    static $checkEndPoint = 'https://api.mch.weixin.qq.com/pay/orderquery';

    //下单接口
    public function order()
    {
        //<xml>
        //<appid>wx2421b1c4370ec43b</appid>
        //<attach>支付测试</attach>
        //<body>H5支付测试</body>
        //<mch_id>10000100</mch_id>
        //<nonce_str>1add1a30ac87aa2db72f57a2375d8fec</nonce_str>
        //<notify_url>http://wxpay.wxutil.com/pub_v2/pay/notify.v2.php</notify_url>
        //<openid>oUpF8uMuAJO_M2pxb1Q9zNjWeS6o</openid>
        //<out_trade_no>1415659990</out_trade_no>
        //<spbill_create_ip>14.23.150.211</spbill_create_ip>
        //<total_fee>1</total_fee>
        //<trade_type>MWEB</trade_type>
        //<scene_info>{"h5_info": {"type":"Wap","wap_url": "https://pay.qq.com","wap_name": "腾讯充值"}}</scene_info>
        //<sign>0CB01533B8C1EF103065174F50BCA001</sign>
        //</xml>
        $this->pay_params['sign']=$this->makeSign($this->pay_params,'key='.$this->secret,null, false);
        $xml = new SimpleXMLElement('<?xml version="1.0"?><root></root>');
        foreach ($this->pay_params as $key => $value) {
            $xml->addChild($key, $value);
        }



        //如果是h5支付的话，只要获取对象的mweb_url属性
        //如果是扫码支付的话，只要获取对象的code_url属性,最好是将该url信息生成二维码让用户扫
        return $this->https_request(self::$payEndPoint,$xml->asXML());
        //<xml>
        //   <return_code><![CDATA[SUCCESS]]></return_code>
        //   <return_msg><![CDATA[OK]]></return_msg>
        //   <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
        //   <mch_id><![CDATA[10000100]]></mch_id>
        //   <nonce_str><![CDATA[IITRi8Iabbblz1Jc]]></nonce_str>
        //   <sign><![CDATA[7921E432F65EB8ED0CE9755F0E86D72F]]></sign>
        //   <result_code><![CDATA[SUCCESS]]></result_code>
        //   预支付交易会话标识<prepay_id><![CDATA[wx201411101639507cbf6ffd8b0779950874]]></prepay_id>
        //   交易类型<trade_type><![CDATA[MWEB]]></trade_type>
        //   支付跳转链接，为期为5分钟。<mweb_url><![CDATA[https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx2016121516420242444321ca0631331346&package=1405458241]]></mweb_url>
        //</xml>
    }

    //查单接口
    public function checkOrder()
    {
        //<xml>
        //   <appid>wx2421b1c4370ec43b</appid>
        //   <mch_id>10000100</mch_id>
        //   <nonce_str>ec2316275641faa3aacf3cc599e8730f</nonce_str>
        //   <transaction_id>1008450740201411110005820873</transaction_id>
        //   <sign>FDD167FAA73459FD921B144BAF4F4CA2</sign>
        //</xml>
        $this->check_params['sign']=$this->makeSign($this->check_params,'key='.$this->secret,null, false);
        $xml = new SimpleXMLElement('<?xml version="1.0"?><root></root>');
        foreach ($this->check_params as $key => $value) {
            $xml->addChild($key, $value);
        }

        return $this->https_request(self::$checkEndPoint,$xml->asXML());
        //<xml>
        //   <return_code><![CDATA[SUCCESS]]></return_code>
        //   <return_msg><![CDATA[OK]]></return_msg>
        //   <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
        //   <mch_id><![CDATA[10000100]]></mch_id>
        //   <device_info><![CDATA[1000]]></device_info>
        //   <nonce_str><![CDATA[TN55wO9Pba5yENl8]]></nonce_str>
        //   <sign><![CDATA[BDF0099C15FF7BC6B1585FBB110AB635]]></sign>
        //   <result_code><![CDATA[SUCCESS]]></result_code>
        //   <openid><![CDATA[oUpF8uN95-Ptaags6E_roPHg7AG0]]></openid>
        //   <is_subscribe><![CDATA[Y]]></is_subscribe>
        //   <trade_type><![CDATA[MICROPAY]]></trade_type>
        //   <bank_type><![CDATA[CCB_DEBIT]]></bank_type>
        //   <total_fee>1</total_fee>
        //   <fee_type><![CDATA[CNY]]></fee_type>
        //   <transaction_id><![CDATA[1008450740201411110005820873]]></transaction_id>
        //   <out_trade_no><![CDATA[1415757673]]></out_trade_no>
        //   <attach><![CDATA[订单额外描述]]></attach>
        //   <time_end><![CDATA[20141111170043]]></time_end>
        //   <trade_state><![CDATA[SUCCESS]]></trade_state>
        //</xml>
    }


    /**
     * @param string $body 从微信回调回来接受到的信息
     * @param callable $func 检查函数(
     * 查找订单信息是否存在->
     * 验证支付的金额是否一致->
     * 验证订单支付状态是否为未支付->
     * 验证微信的订单流水号是否存在->
     * 验证支付人和下单人一致(看具体场景是否需要验证)->
     * 开始更新订单的信息
     * )
     * @return string
     * https://pay.weixin.qq.com/wiki/doc/api/H5.php?chapter=9_7&index=8
     */
    public function notify($body,$func)
    {
        $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
        $data = json_decode(json_encode($xml), true);
        if (!isset($data['return_code']) || $data['return_code'] != 'SUCCESS')
            return self::_getXml(false);


        //检查签名是否正确
        $sign = strtoupper($this->makeSign($data, 'key='.$this->secret, 'sign', false));
        if ($sign != $data['sign'])
            return self::_getXml(false);

        //支付金额
        $money = $data['total_fee'] ? $data['total_fee'] / 100 : null;

        //检查订单信息和微信回调的信息是否一致
        //out_trade_no我方的订单号
        //transaction_id微信的流水号码
        //trade_type支付形式
        $completed = $func($data['out_trade_no'], $data['transaction_id'], $money, null,$data['trade_type']);

        //继续回调
        if (!$completed)return self::_getXml(false);

        //通知微信订单已经处理，不需要再回调回来了
        return self::_getXml();


    }

    //组成回应微信的xml格式
    protected static function _getXml($success = true)
    {
        if ($success) {
            $code = 'SUCCESS';
            $msg = 'OK';
        } else {
            $code = 'FAIL';
            $msg = 'FAIL';
        }
        return '<xml>
  <return_code><![CDATA[' . $code . ']]></return_code>
  <return_msg><![CDATA[' . $msg . ']]></return_msg>
</xml>';
    }


    /**
     * http请求（发起HTTPS请求，返回数组）
     *
     * @param string $url （传入要发起请求的URL）
     * @param null $data （发起请求的post数据）
     * @return mixed|string  （返回对象）
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

        //返回xml格式，要将xml格式的数据最后转成对象返回
        $xml = simplexml_load_string($output, 'SimpleXMLElement', LIBXML_NOCDATA);
        return json_decode(json_encode($xml), true);//
        //return json_decode($output,true);//将json转换成数组格式返回，第二个参数默认返回为对象，true为返回数组
    }


    /**
     * 生成签名
     * @param array $data  参数
     * @param string $secret  密钥
     * @param null $exclude 不包括
     * @param bool $salt 盐值
     * @return string
     */
    protected function makeSign($data, $secret, $exclude = null, $salt = true)
    {
        $string = '';
        if($data){
            if($exclude){
                unset($data[$exclude]);
            }
            ksort($data);

            foreach($data as $key => $value){
                $string .= $key . '=' . $value . '&';
            }
        }

        $string .= $salt ? md5($secret) : $secret;

        return md5($string);
    }


}
