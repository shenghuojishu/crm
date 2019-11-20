<?php

// v_0.1
const SDK_REGISTER   = 'user/Users/register';		//注册
const SDK_LOGIN 	 = 'user/Users/passwordLogin';	//登录
const SDK_EDIT_USER  = 'user/MemberInfo/updateUserInfo';	//修改用户信息
const SDK_FORGET_PSD = 'user/Users/forgetPassword';	//忘记密码
const SDK_REAL_NAME_CERT = 'user/MemberInfo/authentication';	//实名认证

// v_0.2
const SDK_LOGOUT	      = 'user/MemberInfo/Logout';	//退出登录
const SDK_SYS_AUTHOR      = 'user/Users/judgeUserAuthorization';	//判断用户是否授权
const SDK_OPENID_LOGIN    = 'user/Users/openIdLogin';	//通过OpenId登录的接口
const SDK_ADMIN_EDIT_USER = 'user/Users/updateUserInfo';	//修改用户信息（不用登录，后台修改）
const SDK_GET_OPENID      = 'user/Users/mobileCrmOpenId';	//通过手机号获取crm系统openId
const SDK_EDIT_TOKER      = 'user/Users/updUserSuperior';	//修改用户上级
const SDK_BLACK_LIST      = 'user/Users/userLock';	//加入黑名单
const SDK_COMMON_ALERT    = 'user/Users/sdkCommonAlert';	//公共弹窗页

// v_0.3
const SDK_USER_TEAM       = 'user/Users/getUserTeam';	//获取用户团队
const SDK_ADD_REWARD      = 'user/Users/rewardLog';	//分红奖励批量插入crm
const SDK_WALLET_LOGS     = 'user/Wallet/getWalletLogs';	//子钱包流水账单，暂未开放
const SDK_WALLET_OPERATE  = 'user/Wallet/walletOperate';	//钱包操作,个人,已登录
const SDK_PREMONEY_INCOME = 'user/Users/premoneyOperate';	//钱包操作,个人，免登录，主要用户预计收益到账
const SDK_WALLET          = 'user/Wallet/getWallets';	//钱包余额
const SDK_GET_USER 	 	  = 'user/MemberInfo/getUser';	//获取用户信息
const SDK_USER_SUPERIOR   = 'user/Users/getUserSuperior';
const SDK_USER_BASE       = 'user/Users/getUserByCrmUid';	//通过crmuid获取用户基本信息

// v_0.6
const SDK_USER_LIST       = 'user/Users/getMultUsers';  //后台通过openid批量获取用户信息
const SDK_USER_IDENTIFY   = 'user/Users/userIdentify';	//获取用户实名认证信息
// pub_0.3
const SDK_LOGIN_BY_TOKEN  = 'user/MemberInfo/loginByToken';	//通过token登录其他系统
const SDK_REAL_NAME_INFO = 'user/Users/userIdentify';    //获取用户实名认证信息