<?php
namespace Shenghuojishu\request;

/**
 * author:czk
 * time:2019-06-18
 */
use think\Validate;
require_once dirname(dirname(__FILE__))."/ShSdkCommon.php";
// require_once './vendor/shenghuo/shh-sdk/php-core/ShSdkCommon.php';
class ShSdkValidate extends Validate
{

	protected $rule = [
		['port_type','require|sdkCheckPort'],		
		['mobile','require|checkMobile'],
		['password','require'],
		['payword','require|number|length:6'],
		['referrer_mobile','require|checkMobile'],
		['reg_type','require|sdkCheckPort'],		
		['token','require'],
		['name','require'],
		['card_no','require'],
		['open_id','require'],
		['returnUrl','require'],
		['logo','require'],
		['siteName','require'],
		['reward','require'],
		['source_id','require'],
	];
	protected $message = [
		'port_type.require'      => '客户端必须',
		'port_type.sdkCheckPort' => '客户端不支持',
		'mobile.require'       => '手机号必须',
		'mobile.checkMobile'   => '手机号格式错误',
		'phone.require'       => '手机号必须',
		'phone.checkMobile'   => '手机号格式错误',
		'password.require'     => '密码必须',
		'payword.require' 	   => '支付密码必须',
		'payword.number' 	   => '支付密码必须是数字',
		'payword.length'       => '支付密码长度必须是6',
		'referrer_mobile.require'     => '推荐人手机号必须',
		'referrer_mobile.checkMobile' => '推荐人手机号格式错误',
		'reg_type.require'      => '客户端必须',
		'reg_type.sdkCheckPort' => '客户端不支持',
		'token.require'		=> '登录凭证必须',
		'name.require'		=> '真实姓名必须',
		'card_no.require'	=> '身份证号必须',
		'returnUrl.require' => '返回地址必须',
		'logo.require'		=> '授权网站logo必须',
		'siteName.require'	=> '授权网站名称必须',
		'open_id.require' => 'open_id必须',
		'reward.require' => '分红数据不能为空',
		'source_id.require' => '来源id不能为空',
		'user_id.require' => 'user_id不能为空',
	];

	protected $scene=[
		'normal'   =>['token'=>'require'],
		'userBase' =>['open_id'=>'require'],
		'register' =>[
			'mobile'=>'require|checkMobile',
			'password'=>'require',
			'payword'=>'require|number|length:6',
			'reg_type'=>'require|sdkCheckPort',
		],
		'loginByMobile' =>[
			'mobile'=>'require|checkMobile',
			'password'=>'require',
		],
		'realName' =>[
			'token'=>'require',
			'phone'=>'require|checkMobile',
			'card_no'=>'require',
			'name'=>'require',
		],
		'realNameInfo' =>['open_id'=>'require'],
		'authByOpenId' => [
			'open_id'=>'require',
			'returnUrl'=>'require',
			'logo'=>'require',
			'siteName'=>'require',
		],
		'loginByOpenId' => [
			'open_id'=>'require',
		],
		'rewardRule' => [
			'reward'=>'require',
		],
		'walletOperate' => [
			'token'=>'require',
			'source_id'=>'require',
			'money'=>'require|number',
			'source_type'=>'require',
		],
		'premoneyOperate' => [
			'user_id'=>'require',
			'source_id'=>'require',
			'money'=>'require|number',
			'source_type'=>'require',
		],
	];

	/**
	 * 通过openid操作验证规则
	 * scene 场景：1判断是否授权；2登录
	 */
	public function openidOprRule($scene=1){
		$this->rule = [
			['open_id','require'],
		];
		if ($scene==1) {
			$rule = [
				['returnUrl','require'],
				['logo','require'],
				['siteName','require'],
			];
			$this->rule = array_merge($this->rule,$rule);
		}
	}

	

	/**
	 * 检查手机号码格式
	 * @param $mobile 手机号码
	 */
	public function checkMobile($mobile)
	{
	    if (preg_match('/^1[3456789]\d{9}$/', $mobile)) {
	        return true;
	    }
	    return false;
	}

	public function sdkCheckPort($port){
		$clientPort = ['h5','ios','android','wxapp'];
		if (in_array(strtolower($port), $clientPort)) {
			return true;
		}
	    return false;
	}
	

}