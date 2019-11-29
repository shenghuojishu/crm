# shenghuosdkpublic
SDK目录结构和文档使用说明：
一、目录结构

|-shenghuo 应用目录（整个SDK的内容都写在这里）

    |-shh-sdk

        |-examples(调用sdk案例)

        	|-examples.php(调用sdk的例子)

        |-php-core(php核心代码)

	        |-request(接口核心代码)

		        |-ShSdkBase.php(接口加密文件)

		        |-ShSdkUserApi.php(调用crm系统接口的封装方法)

		        |-ShSdkWalletApi.php(调用crm系统接口的封装方法)

		        |-ShSdkValidate.php(接口数据验证规则)

	        |-index.php(SDK包的入口文件，有基本限制，例如：php版本要求)

	        |-ShSdkCommon.php(SDK包公共方法)

	        |-ShSdkConfig-copy.php(SDK配置文件的副本，当开发者使用composer下载包后，
	        	复制shh-sdk/ShSdkConfig-copy.php，命名shh-sdk/ShSdkConfig.php，并修改
	        	#常量 API_HOST的值并以“/”结尾 http://crm.tkkkc.com/ 是线上域名，http://192.168.1.43:8089/ 是测试域名)
	        |-ShSdkInterFaceName.php(SDK指向crm系统的接口路径)

        |-composer.json，composer配置说明

        |-README.md，圣火crm系统sdk使用文档

注：已登录用户请求crm系统接口的时候一定要带crm系统登录返回给用户的token

注：以下是ShSdkUserApi.php文件对接crm接口的重组：
sdkUserRegister:注册
sdkUserLogin:登录
sdkForgetPsd:忘记密码
skdCommonInterface:通用接口包括（修改用户信息（前后台），实名认证接口，修改用户上级，锁定会员），批量钱包操作，单个钱包操作，获取钱包余额，用户信息
sdkOpenidOperate:通过openId判断用户是否授权和通过OpenId登录的接口接口
sdkGetOpenid:通过手机号获取用户信息
sdkCommonAlert:跳转未授权公共页(授权弹窗接口)
sdkLogout:退出crm接口
crmUserTeam:获取我的团队
crmUserSuperior:获取我的上级

#ok
