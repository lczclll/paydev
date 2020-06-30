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

namespace app\api\controller;

/**
 * 代付接口
 */
class Devpay extends ApiBase
{


    /**
     * 获取员工用户信息列表
     */
    public function user()
    {
        return $this->apiReturn($this->logicDevpay->user($this->param));
    }


    /**
     * 充值接口
     */
    public function rechargeadd(){
        return $this->apiReturn($this->logicDevpay->rechargeadd($this->param));
    }


    /*
     * 支付宝代付接口
     * */

    public function alipay(){
        return $this->apiReturn($this->logicDevpay->alipay($this->param));
    }


    /**
     * 银行代付接口
     */
    public function bank(){
        return $this->apiReturn($this->logicDevpay->bank($this->param));
    }

    /**
     * 获取用户基本信息
     */
    public function userinfo(){
        return $this->apiReturn($this->logicDevpay->userinfo($this->param));
    }


    /**
     * 充值信息查询
     */
    public function rechargelist(){
        return $this->apiReturn($this->logicDevpay->rechargelist($this->param));
    }


    /**
     * 代付记录
     */
    public function payment(){
        return $this->apiReturn($this->logicDevpay->payment($this->param));
    }



}
