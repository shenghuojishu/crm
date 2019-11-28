<?php
namespace Shenghuojishu\request;
/**
 * author:czk
 * time:2019-06-18
 */
require_once dirname(dirname(__FILE__))."/index.php";
require_once dirname(dirname(__FILE__))."/ShSdkInterFaceName.php";
require_once dirname(dirname(__FILE__))."/ShSdkConfig.php";
require_once dirname(dirname(__FILE__))."/ShSdkCommon.php";

class ShSdkBase
{
    public $appId,$appSecret,$privateKey,$publicKey,$sign,$value;
    public function __construct(){

        // if (empty($config)) {
        //  return sdkReturnArr(FAIL,[],"获取项目app配置失败");
        // }
        // $this->appId      = $config['app_id'];
        // $this->appSecret  = $config['app_secret'];
        // $this->privateKey = $config['private_key'];
        // $this->publicKey  = $config['public_key'];

        $this->appId      = CRM_APP_ID;
        $this->appSecret  = CRM_APP_SECRET;
        $this->privateKey = CRM_PRIVATE_KEY;
        $this->publicKey  = CRM_PUBLIC_KEY;

        $this->channelCode  = CHANNEL_CODE;
    }

    public function setValue($data){
        $this->values = $data;
    }

    public function setDataAppid(){
        $this->values['app_id'] = $this->appId;
        $this->values['channelCode'] = $this->channelCode;
    }

    public function setDataSign(){
        $this->values['sign'] = $this->MakeSign();
    }

    /**
     * 加密
     */
    public function encryption($data){
        $key   = openssl_pkey_get_public($this->publicKey);     //获取公钥
        if (!$key) {
            return sdkReturnArr(FAIL,[],"获取公钥失败");
        }
        $jdata = json_encode($data);
        $flag  = openssl_public_encrypt($jdata, $crypted, $key);
        if (!$flag) {
            return sdkReturnArr(FAIL,[],"加密失败");
        }
        $encryData  = base64_encode($crypted);
        return sdkReturnArr(SUCCESS,$encryData,"加密成功");
    }

    /**
     * RSA解密规则
     * @return 解密密后数据
     */
    public function opensslDecrypt($data){
        //获取私钥
        $privateKey = $this->privateKey;
        
        $key = openssl_pkey_get_private($privateKey);
        if (!$key) {
            return false;
        }
        $return_de = openssl_private_decrypt(base64_decode($data), $decrypted, $key);

        if (!$return_de){
            return false;
        }
        return trim($decrypted,'"');
    }

    /**
     * 排序
     */
    public function ToUrlParams()
    {
        $buff = "";
        foreach ($this->values as $k => $v)
        {
            if($k != "sign" && $v !== "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
    
        $buff = trim($buff, "&");
        return $buff;
    }
    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function MakeSign()
    {
        //签名步骤一：按字典序排序参数
        ksort($this->values);
        $string = $this->ToUrlParams();
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$this->appSecret;
        // dump($string);
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    
}