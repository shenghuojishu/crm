<?php
/**
 * kitnote
 * ============================================================================
 * 版权所有 2015-2027 株洲清拓科技有限公司，并保留所有权利。
 * 网站地址: http://www.mall.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 采用TP5助手函数可实现单字母函数M D U等,也可db::name方式,可双向兼容
 * ============================================================================
 * Author: czk
 * Date: 2019-06-20
 */

namespace app\mobile\controller;

use think\Model;
use think\Page;
use think\db;
use Shenghuo\request\ShSdkUserApi;

/**
 * 分类逻辑定义
 * Class CatsLogic
 * @package Home\Logic
 */
require_once './vendor/shenghuo/shh-sdk/php-core/ShSdkInterFaceName.php';
class Examples extends Model{

	public $crmConfig;

	public function __construct(){
        // 在crm系统生成的配置信息，不能为空
        $this->crmConfig['app_id'] = '';
        $this->crmConfig['app_secret']  = '';
        $this->crmConfig['private_key'] = '';
        $this->crmConfig['public_key']  = '';
	}
	/**
     * 同步注册到管理系统
     */
    public function crmRegister($user,$password,$tkMobile="",$openId=""){
        // $config = tpCache('alone');
        $data['mobile']   = $user['mobile'];
        $data['password'] = $password;
        $data['payword']  = substr($data['mobile'], -6,6);
        $data['nickname'] = $user['nickname']?:$data['mobile'];
        $data['reg_type'] = input('client','h5');
        $user['name'] && $data['name']   = $user['name']?:"";
        $user['wechat'] && $data['wechat'] = $user['wechat']?:"";
        $user['qq'] && $data['qq']  = $user['qq']?:"";
        $user['sex'] && $data['sex'] = $user['sex']?:0;

        // 推荐人手机号
        
        $data['referrer_mobile'] = $tkMobile;
        $data['tk_open_id'] = $openId;

        // $client = judge_client();
        // $data['reg_type'] = $client!='Other'?:'h5';
        $crmModel = new ShSdkUserApi($this->crmConfig);
        $res = $crmModel->sdkUserRegister($data);
        if ($res['code']=='SUCCESS') {
            // model('Common/Users')->where(['mobile'=>$user['mobile']])->update(['crm_uid'=>$res['data']['user_id']]);
            $this->crmLogin($data['mobile'],$password);
        }
        return $res;
    }

    /**
     * crm用户登录
     */
    public function crmLogin($mobile,$password){
        $data['mobile']    = $mobile;
        $data['password']  = $password;
        $data['port_type'] = input('client','h5');
        $crmModel = new ShSdkUserApi($this->crmConfig);
        $res = $crmModel->sdkUserLogin($data);
        if ($res['code']=='SUCCESS') {
            session('crm_user', $res['data']);
            session('crm_token', $res['data']['token']);
            session('crm_openid', $res['data']['open_id']);
        }
        return $res;
    }

    /**
     * crm忘记密码
     */
    public function crmForgetPassword($mobile,$password){
        $data['mobile']    = $mobile;
        $data['password']  = $password;
        $data['port_type'] = input('client','h5');
        $crmModel = new ShSdkUserApi($this->crmConfig);
        $res = $crmModel->sdkForgetPsd($data);
        return $res;
    }

    /**
     * 同步修改crm用户信息
     */
    public function crmEditUser($data,$interFaceType){
        $data['token'] = session('crm_token')?:input('loginToken');
        $crmModel = new ShSdkUserApi($this->crmConfig);
        $res = $crmModel->skdCommonInterface($data,$interFaceType);
        if ($res['code']=='NOLOGIN') {
            $this->logout();
        }
        return $res;
    }

    /**
     * 后台管理员修改用户信息，同步到crm
     */
    public function crmAdminEditUser($data){
        $crmModel = new ShSdkUserApi($this->crmConfig);
        $res = $crmModel->skdCommonInterface($data,SDK_ADMIN_EDIT_USER,true);
        if ($res['code']=='NOLOGIN') {
            $this->logout();
        }
        return $res;
    }

    /**
     * 后台管理员把用户加入黑名单和解封
     */
    public function crmAdminBlackList($openIds,$isLock=1){
        $data['open_id_str'] = $openIds;
        $data['is_lock']     = $isLock;
        $crmModel = new ShSdkUserApi($this->crmConfig);
        $res = $crmModel->skdCommonInterface($data,SDK_BLACK_LIST,true);
        if ($res['code']=='NOLOGIN') {
            $this->logout();
        }
        return $res;
    }

    /**
     * 修改用户上级，同步到crm
     */
    public function crmEditToker($open_id,$tk_open_id){
        $data['open_id']    = $open_id;
        $data['tk_open_id'] = $tk_open_id;
        $crmModel = new ShSdkUserApi($this->crmConfig);
        $res = $crmModel->skdCommonInterface($data,SDK_EDIT_TOKER,true);
        if ($res['code']=='NOLOGIN') {
            $this->logout();
        }
        return $res;
    }

    /**
     * 实名认证
     */
    public function crmRealName($post){
        $data['token'] = session('crm_token')?:input('loginToken');
        $data['name']    = $post['true_name'];
        $data['card_no'] = $post['card_number'];
        
        $crmModel = new ShSdkUserApi($this->crmConfig);
        $res = $crmModel->skdCommonInterface($data,SDK_REAL_NAME_CERT,false,'realName');
        if ($res['code']=='NOLOGIN') {
            $this->logout();
        }
        return $res;
    }

    /**
     * 通过openId判断用户是否授权
     * 通过OpenId登录的接口
     * scene 场景：1判断是否授权；2登录
     */
    public function crmOpenidOperate($openId="",$scene=1){
        $crmModel = new ShSdkUserApi($this->crmConfig);    
        if ($scene==1) {
            $data['logo']      = SITE_URL.tpCache('shop_info.store_logo');
            $data['siteName']  = tpCache('shop_info.store_title');
            $data['returnUrl'] = input('returnUrl');
        }
        $data['open_id'] = $openId;
        $res = $crmModel->sdkOpenidOperate($data,$scene);
        if ($scene==2 && $res['code']=='SUCCESS') {
            $userMdl = model('Common/Users');
            // 登录后查询users表是否有这条openid的记录
            $userMdl->addUserLog($res['data'],$res['data']['refereer']);
            session('crm_user', $res['data']);
            session('crm_token', $res['data']['token']);
            session('crm_openid', $res['data']['open_id']);
        }
        return $res;
    }

    /**
     * 退出登录
     */
    public function crmLogout(){
        $token = session('crm_token')?:input('loginToken');
        $data['port_type'] = input('client','h5');
        $data['token'] = $token;
        $crmModel = new ShSdkUserApi($this->crmConfig);
        $res = $crmModel->sdkLogout($data);
        if ($res['code']=='NOLOGIN') {
            $this->logout();
        }
        return $res;
        
    }

    /**
     * 通过手机号获取openid
     */
    public function crmGetOpenid($mobile){
        $crmModel = new ShSdkUserApi($this->crmConfig);
        $res = $crmModel->sdkGetOpenid($mobile);
        if ($res['code']=='NOLOGIN') {
            $this->logout();
        }
        return $res;
    }

    /**
     * 分红批量插入
     * reward   分红奖励数组
     * type   钱包类型：stock-内部股权；owner-商家；personal-个人
     * source_type   操作类型：1 消费 2提现 3 充值 4 奖励
     * source_id   来源表的主键或Order_sn订单号
     * source_table   来源表：order-订单；recharge-充值；withdraw-提现
     * description   余额变动说明
     */
    public function crmAddReward($reward,$type='personal',$source_table='order',$source_type='4'){
        $data['reward']    = $reward;
//         $data['source_id'] = $source_id;
        $data['type'] = $type;
        $data['source_table'] = $source_table;
        $data['source_type'] = $source_type;
//         $data['description'] = $description;
        $crmModel = new ShSdkWalletApi($this->crmConfig);
        $res = $crmModel->skdCommonInterface($data,SDK_ADD_REWARD,false,'rewardRule');
        if ($res['code']=='NOLOGIN') {
            $this->logout();
        }
        return $res;
    }

    /**
     * 钱包操作，单个用户
     * 个体会员钱包变更
     * type 分红类型 :stock-内部股权；owner-商家；personal-个人
     * source_table 来源表：order-订单；recharge-充值；withdraw-提现
     * source_id 来源表的主键或Order_sn订单号
     * source_type 1 消费 2提现 3 充值 4 奖励 
     * description 描述
     * extra_info 额外信息
     * open_id crm用户id，没登录状态的时候传
     * is_ti_xian 是否提现，提现的时候$money是数组
     */
    public function crmWalletOperate($money,$source_id,$source_table='order',$type='personal',$source_type='',$description='',$extra_info='',$open_id="",$is_ti_xian=false){
        $crmSourceType        = $this->transCrmSourceType($source_type);
        if ($is_ti_xian) {
            $data['money']        = json_encode($money);
            $data['is_ti_xian']     = $is_ti_xian;
        }else{
            $data['money']        = $money;
        }
        $data['source_id']    = $source_id;
        $data['type'] = $type;
        $data['source_table'] = $source_table;
        $data['source_type']  = $crmSourceType;
        $data['description']  = $description;
        $data['extra_info']   = $extra_info?:"";
        $crmModel = new ShSdkWalletApi($this->crmConfig);
//         dump($data);exit;
        if (!$open_id) {
            $data['token'] = session('crm_token')?:input('loginToken');
            $res = $crmModel->skdCommonInterface($data,SDK_WALLET_OPERATE,false,'walletOperate');
        }else{
            $data['open_id']  = $open_id;
            $res = $crmModel->skdCommonInterface($data,SDK_PREMONEY_INCOME,true,'premoneyOperate');
        }
        if ($res['code']=='NOLOGIN') {
            $this->logout();
        }
        return $res;
    }

    public function transCrmSourceType($source_type){

        switch ($source_type) {
            case 'withdraw':
                return 2;
                break;
            case 'recharge':
                return 3;
                break;
            case 'buy':
                return 1;
                break;
            
            default:
                return 4;
                break;
        }
    }

    /**
     * 钱包余额
     */
    public function crmWallet($type=''){
        $type && $data['type'] = $type;
        $data['token'] = session('crm_token')?:input('loginToken');
        $crmModel = new ShSdkWalletApi($this->crmConfig);
        $res = $crmModel->skdCommonInterface($data,SDK_WALLET);
        if ($res['code']=='SUCCESS') {
            return $res['data'];
        }elseif($res['code']=='NOLOGIN') {
            $this->logout();
        }else{
            return false;
        }
        return $res;
    }

    /**
     * 获取余额流水
     */
    public function crmWalletLogs(){
        $data['token'] = session('crm_token')?:input('loginToken');
        $data['page'] = input('page',1);
        $data['size'] = input('size',15);
        $crmModel = new ShSdkWalletApi($this->crmConfig);
        $res = $crmModel->skdCommonInterface($data,SDK_WALLET_LOGS);
        if ($res['code']=='NOLOGIN') {
            $this->logout();
        }
        return $res;
    }

    /**
     * 获取会员信息
     */
    public function crmGetUserInfo(){
        $data['token'] = session('crm_token')?:input('loginToken','');
        $crmModel = new ShSdkWalletApi($this->crmConfig);
        $res = $crmModel->skdCommonInterface($data,SDK_GET_USER);
        if ($res['code']=='NOLOGIN') {
            $this->logout();
        }

        return $res;
    }

    // 获取我的团队
    public function crmUserTeam($is_direct=0,$open_id=0,$isGetId=0){
        if($open_id){
            //根据用户id获取上级
            $data['open_id'] = $open_id;
        }else{
            //根据token获取上级
            $data['token'] = session('crm_token')?:input('loginToken');
        }
        $data['is_direct'] = $is_direct;
        $data['is_get_id'] =$isGetId;
        $crmModel = new ShSdkUserApi($this->crmConfig);
        $res = $crmModel->crmUserTeam($data);
        if ($res['code']=='NOLOGIN') {
            $this->logout();
        }
        return $res;
    }

    // 获取我的上级
    public function crmUserSuperior($open_id=0){
        if($open_id){
            //根据用户open_id获取上级
            $data['open_id'] = $open_id;
        }else{
            //根据token获取上级
            $data['token'] = session('crm_token')?:input('loginToken');
        }
        $crmModel = new ShSdkUserApi($this->crmConfig);
        $res = $crmModel->crmUserSuperior($data);
        if ($res['code']=='NOLOGIN') {
            $this->logout();
        }
        return $res;
    }

    /**
     * 通过user_id获取会员基本信息
     */
    public function crmUserBaseByUid($open_id){
        $data['open_id'] = $open_id;
        $crmModel = new ShSdkUserApi($this->crmConfig);
        $res = $crmModel->sdkUserBase($data);
        if($res['code']=='SUCCESS'){
            return $res['data'];
        }elseif ($res['code']=='NOLOGIN') {
            $this->logout();
        }else{
            return false;
        }
     
    }


    public function logout(){
        session_unset();
        session_destroy();
        setcookie('uname', '', time() - 3600, '/');
        setcookie('cn', '', time() - 3600, '/');
        setcookie('user_id', '', time() - 3600, '/');
        setcookie('tk_id', '', time() - 3600, '/');
        setcookie('PHPSESSID', '', time() - 3600, '/');
        cookie('cpsChannelId', null);

        if (in_array(input('client'), ['Android','iOS'])) {
            echo api_ajaxReturn(999,"请先登录");exit;
        }
        $tk_id = I('tk_id', 0, 'intval');
        if (!empty($tk_id)) {
            $url = U('Distribut/distributToker', ['uid' => $tk_id]);
        }else{
            $url = withNextURL(U('User/login'), base64_encode($_SERVER['REQUEST_URI']), 'next_base64');
        }
        header('Location:'.$url);
        exit();
        
    }
}