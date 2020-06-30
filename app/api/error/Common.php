<?php
// +---------------------------------------------------------------------+
// | OneBase    | [ WE CAN DO IT JUST THINK ]                            |
// +---------------------------------------------------------------------+
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )           |
// +---------------------------------------------------------------------+
// | Author     | Bigotry <3162875@qq.com>                               |
// +---------------------------------------------------------------------+
// | Repository | https://gitee.com/Bigotry/OneBase                      |
// +---------------------------------------------------------------------+

namespace app\api\error;

class Common
{
    
    /*public static $passwordError            = [API_CODE_NAME => 1010001, API_MSG_NAME => '登录密码错误'];
    
    public static $usernameOrPasswordEmpty  = [API_CODE_NAME => 1010002, API_MSG_NAME => '用户名或密码不能为空'];
    
    public static $registerFail             = [API_CODE_NAME => 1010003, API_MSG_NAME => '注册失败'];
    
    public static $oldOrNewPassword         = [API_CODE_NAME => 1010004, API_MSG_NAME => '旧密码或新密码不能为空'];
    
    public static $changePasswordFail       = [API_CODE_NAME => 1010005, API_MSG_NAME => '密码修改失败'];*/

    public static $ApisecretFail    = [API_CODE_NAME => 2000001, API_MSG_NAME => '秘钥错误'];
    public static $ApisecretNull    = [API_CODE_NAME => 2000002, API_MSG_NAME => '秘钥不能空'];
    public static $SignError    = [API_CODE_NAME => 2000003, API_MSG_NAME => 'Sign错误'];
    public static $SignNull    = [API_CODE_NAME => 2000004, API_MSG_NAME => 'Sign不能空'];
    public static $PayNameError    = [API_CODE_NAME => 2000005, API_MSG_NAME => '请填写转款人'];
    public static $PayBankNoError    = [API_CODE_NAME => 2000006, API_MSG_NAME => '请填写转款账号'];
    public static $PayBankError    = [API_CODE_NAME => 2000007, API_MSG_NAME => '请填写转款银行'];
    public static $PayMoneyError    = [API_CODE_NAME => 2000008, API_MSG_NAME => '转款金额必须大于0'];
    public static $RecharheRateError    = [API_CODE_NAME => 2000009, API_MSG_NAME => '您还未设置充值手续费比例'];
    public static $RechargeError    = [API_CODE_NAME => 2000010, API_MSG_NAME => '充值失败'];
    public static $MerchantuidError    = [API_CODE_NAME => 2000011, API_MSG_NAME => '此用户并非您的用户，非法操作'];
    public static $PaycCollectionError    = [API_CODE_NAME => 2000012, API_MSG_NAME => '您未设置充值收款银行账户'];

    public static $PaypassNull    = [API_CODE_NAME => 2000013, API_MSG_NAME => '请填写支付密码'];
    public static $PaypassNullError    = [API_CODE_NAME => 2000013, API_MSG_NAME => '还未设置支付密码'];
    public static $PaypassError    = [API_CODE_NAME => 2000014, API_MSG_NAME => '支付密码错误'];
    public static $PayNotBanlnce    = [API_CODE_NAME => 2000015, API_MSG_NAME => '余额不足'];
    public static $UserNullError    = [API_CODE_NAME => 2000016, API_MSG_NAME => '用户不存在'];

}
