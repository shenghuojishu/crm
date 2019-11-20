<?php
namespace Shenghuo\request;

/**
 * author:czk
 * time:2019-06-18
 */
use think\Model;
use Shenghuo\request\ShSdkValidate;
class ShSdkUserApi extends ShSdkBase
{
	public $valid;
	public function __construct()
	{
		parent::__construct();
		$this->valid = new ShSdkValidate();
	}

	/**
	 * 注册
	 */
	public function sdkUserRegister($data){
		// 数据验证
		$checkRes = $this->valid->check($data,'','register');
		if ($checkRes===false) {
			return sdkReturnArr(FAIL,[],$this->valid->getError());
		}
		if (empty($data['tk_open_id']) && empty($data['referrer_mobile'])) {
			return sdkReturnArr(FAIL,[],'上级手机号不能为空或者上级openid不能为空');
		}
		// 密码加密
		$encryPsd = $this->encryption($data['password']);
		if ($encryPsd['code']==FAIL) {
			return $encryPsd;
		}
		$data['password'] = $encryPsd['data'];
		// 密码加密
		$encryPyd = $this->encryption($data['payword']);
		if ($encryPyd['code']==FAIL) {
			return $encryPyd;
		}
		$data['payword'] = $encryPyd['data'];
		// 请求会员管理系统接口
		$url = API_HOST.SDK_REGISTER;
		$res = $this->sdkApiCommomRequest($data,$url);
		return $res;
	}

	/**
	 * 用户登录
	 */
	public function sdkUserLogin($data,$interFaceType=SDK_LOGIN){
		$checkRes = $this->valid->check($data,'','loginByMobile');
		if ($checkRes===false) {
			return sdkReturnArr(FAIL,[],$this->valid->getError());
		}
		// 密码加密
		$encryPsd = $this->encryption($data['password']);
		if ($encryPsd['code']==FAIL) {
			return $encryPsd;
		}
		$data['password'] = $encryPsd['data'];
		// 请求会员管理系统接口
		$url = API_HOST.$interFaceType;
		$res = $this->sdkApiCommomRequest($data,$url);
		return $res;
	}

	/**
	 * 忘记密码
	 */
	public function sdkForgetPsd($data){
		$res = $this->sdkUserLogin($data,SDK_FORGET_PSD);
		return $res;
	}

	/**
	 * 通用接口
	 * interFaceType 接口名称
	 * isAdmin 是否后台操作，后台操作免验证token
	 */
	public function skdCommonInterface($data,$interFaceType,$isAdmin=false,$scene='normal'){
		
		// $data = array_filter($data);		//有些参数会等于0，会被过滤掉不能用
		if (count($data)<1) {
			return sdkReturnArr(FAIL,[],'编辑内容不能为空');
		}
		
		if (!$isAdmin) {
			$checkRes = $this->valid->check($data,'',$scene);
			if ($checkRes===false) {
				return sdkReturnArr(FAIL,[],$this->valid->getError());
			}
		}
		
		// 需要加密的字段
		$encryArr = ['password','payword','card_no','open_id','tk_open_id'];
		// 密码加密
		foreach ($encryArr as $key => $value) {
			if (isset($data[$value])) {
				$encryPsd = $this->encryption($data[$value]);
				if ($encryPsd['code']==FAIL) {
					return $encryPsd;
				}
				$data[$value] = $encryPsd['data'];
			}
		}
		
		// 请求会员管理系统接口
		$url = API_HOST.$interFaceType;
		$res = $this->sdkApiCommomRequest($data,$url);
		
		return $res;
	}

	/**
	 * 通过openId判断用户是否授权
	 * 通过OpenId登录的接口
	 * scene 场景：1判断是否授权；2登录
	 */
	public function sdkOpenidOperate($data,$scene=1){
		if (!$data['open_id']) {
            header('Location: http://'.$_SERVER['SERVER_NAME']);
            exit;
        }

        $checkScene = $scene==1?"authByOpenId":"loginByOpenId";
		$checkRes = $this->valid->check($data,'',$checkScene);
		if ($checkRes===false) {
			return sdkReturnArr(FAIL,[],$this->valid->getError());
		}
		// openId加密
		$encryPsd = $this->encryption($data['open_id']);
		if ($encryPsd['code']==FAIL) {
			return $encryPsd;
		}
		$data['open_id'] = $encryPsd['data'];		
		// 请求会员管理系统接口
		switch ($scene) {
            case 1:
                $interFaceType = SDK_SYS_AUTHOR;
                break;
            case 2:
                $interFaceType = SDK_OPENID_LOGIN;
				$data['port_type'] = input('client','h5');		
                break;
            
            default:
                $interFaceType = SDK_SYS_AUTHOR;
                break;
        }

        // 请求会员管理系统接口
		$url = API_HOST.$interFaceType;
		$res = $this->sdkApiCommomRequest($data,$url);

		if ($res['code']=='SUCCESS' && $scene==1) {
			// 已授权，继续访问当前页面，使用openid登录，场景scene=2;
			if ($res['data']['active'] ==1) {
				$nextUrl   = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'&scene=2';
				header('Location: '.$nextUrl);
				exit();
			}else{
			// 未授权，跳到crm公共页
				$this->sdkCommonAlert($data);
				exit();
			}
		}
		return $res;
	}

	/**
	 * 通过手机号获取openid
	 */
	public function sdkGetOpenid($mobile){
		// 生成签名串
		$res = $this->valid->checkMobile($mobile);
		if (!$res) {
			return sdkReturnArr(FAIL,[],'手机号格式错误');
		}
		$data['mobile'] = $mobile;
		// 请求会员管理系统接口
		$url = API_HOST.SDK_GET_OPENID;
		$res = $this->sdkApiCommomRequest($data,$url);
		return $res;
	}

	/**
	 * 跳转未授权公共页
	 */
	public function sdkCommonAlert($param){
		// 未授权，跳到crm公共页
		$nextUrl   = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'&scene=2');
		$data['returnUrl'] = $param['returnUrl'];
		$data['curUrl']    = $nextUrl;
		$data['head_pic']  = $param['logo'];
		$data['name']   = $param['siteName'];
		$data['app_id'] = $this->appId;
		// 生成签名串
		$this->setValue($data);
		$data['sign']   = $this->MakeSign();
		$data = http_build_query($data);
		// dump($param);
		// p(API_HOST.SDK_COMMON_ALERT.'?'.$param);
		header('Location: '.API_HOST.SDK_COMMON_ALERT.'?'.$data);
		exit();
	}

	/**
	 * 退出crm接口
	 */
	public function sdkLogout($data){
		$checkRes = $this->valid->check($data,'','normal');
		if ($checkRes===false) {
			return sdkReturnArr(FAIL,[],$this->valid->getError());
		}

		// 请求会员管理系统接口
		$url = API_HOST.SDK_LOGOUT;
		$res = $this->sdkApiCommomRequest($data,$url);
		return $res;
	}

	 // * 获取我的团队
	public function crmUserTeam($data){
		if(isset($data['open_id'])){
			// openId加密
			$encryPsd = $this->encryption($data['open_id']);
			if ($encryPsd['code']==FAIL) {
				return $encryPsd;
			}
			$data['open_id'] = $encryPsd['data'];	
		}

		// 请求会员管理系统接口
		$url = API_HOST.SDK_USER_TEAM;
		$res = $this->sdkApiCommomRequest($data,$url);
		return $res;
	}

	 // * 获取我的上级
	public function crmUserSuperior($data){
		if(isset($data['open_id'])){
			// openId加密
			$encryPsd = $this->encryption($data['open_id']);
			if ($encryPsd['code']==FAIL) {
				return $encryPsd;
			}
			$data['open_id'] = $encryPsd['data'];	
		}

		// 请求会员管理系统接口
		$url = API_HOST.SDK_USER_SUPERIOR;
		$res = $this->sdkApiCommomRequest($data,$url);
		return $res;
	}

	/**
	 * 通过user_id获取会员基本信息
	 */
	public function sdkUserBase($data){
		// 生成签名串
		$res = $this->valid->check($data,'','userBase');
		if (!$res) {
			return sdkReturnArr(FAIL,[],$this->valid->getError());
		}

		if(isset($data['open_id'])){
			// openId加密
			$encryPsd = $this->encryption($data['open_id']);
			if ($encryPsd['code']==FAIL) {
				return $encryPsd;
			}
			$data['open_id'] = $encryPsd['data'];	
		}
		$url = API_HOST.SDK_USER_BASE;
		$res = $this->sdkApiCommomRequest($data,$url);
		return $res;
	}

	
	/**
	 * 公共请求部分
	 */
	public function sdkApiCommomRequest($data,$url){
		// 生成签名串
		$this->setValue($data);
		$this->setDataAppid();
		$this->setDataSign();
		// p($this->values);
		$res = sdk_https_request($url, 'POST', $this->values);		
		// dump($url);
		// dump($this->values);
		// dump($res);
		// p($res);
		$res = json_decode($res,true);
		return $res;
	}
	public function test(){
		$model = new ShSdkValidate();
		$res = $model->check();
	}


}