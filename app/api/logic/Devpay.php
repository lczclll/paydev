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

namespace app\api\logic;

use app\api\error\CodeBase;
use app\api\error\Common as CommonError;
use \Firebase\JWT\JWT;

/**
 * 代付接口
 */
class Devpay extends ApiBase
{


    /**
     * 签名审核
     */
    public function checksign($data){
        $signStr="";
        if(empty($data["secret"])){
            return CommonError::$ApisecretNull;
        }
        if(empty($data["sign"])){
            return CommonError::$SignNull;
        }

        $keys=Db("secret")->where(array("secret"=>$data["secret"]))->value("key");

        $sign=$data["sign"];
        if(!$keys){
            return CommonError::$ApisecretFail;
        }

        unset($data["sign"]);
        $setkey=$keys;
        $sinData=$data;
        $sinData["key"]=$setkey;
        ksort($sinData);
        $signStr="";
        $keysin="";
        foreach ($sinData as $k=>$v){
            $keysin.="String {$k} +";
            $signStr.=$v;
        }
//        return $keysin;
        $signbj=md5($signStr);
//        return $signbj;
        if($signbj!=$sign){
            return  CommonError::$SignError;
        }
    }


    /**
     * 获取员工用户信息列表
     */
    public function user($data = [])
    {
        //签名审核
        $checkSign=$this->checksign($data);
        if(!empty($checkSign)){
            if($checkSign["code"]>0){
                return $checkSign;
            }
        }

        $uid=Db("secret")->where(array("secret"=>$data["secret"]))->value("uid");
        $where["merchant_id"]=$uid;
        $where["status"]=1;
        $field="id,username,email,create_time,balance,recharge_rate,recharge_rate_type,payment_rate,payment_rate_type,tel";
        $order="id desc";
        return $this->modelMember->getListApi($where, $field, $order);
    }


    /**
     * 充值申请接口
     */
    public function rechargeadd($data = []){
        //签名审核
        $checkSign=$this->checksign($data);
        if(!empty($checkSign)){
            if($checkSign["code"]>0){
                return $checkSign;
            }
        }

        $uid=$data["uid"];

        //检查是否该商户底下的用户
        $merchantuid=Db("secret")->where(array("secret"=>$data["secret"]))->value("uid");
        $setuid=Db("member")->where(array("id"=>$uid))->value("merchant_id");
        if($merchantuid!=$setuid){
            return  CommonError::$MerchantuidError;
        }

        if($data["pay_money"]<=0){
            return  CommonError::$PayMoneyError;
        }

        $data["subkey"]=md5(time().rand(0,1000));

        if (empty($data["pay_name"])) {
            return  CommonError::$PayNameError;
            exit();
        }
        if (empty($data["bank_no"])) {
            return  CommonError::$PayBankNoError;
            exit();
        }
        if (empty($data["pay_bank"])) {
            return  CommonError::$PayBankError;
            exit();
        }
        if (empty($data["pay_money"])) {
            return  CommonError::$PayMoneyError;
            exit();
        }

        $data["collection_id"]=Db("member")->where(array("id"=>$uid))->value("collection_id");
        if(!$data["collection_id"]){
            return  CommonError::$PaycCollectionError;
        }


        //查询佣金比例
        $rateInfo = Db("member")
            ->where(array("id" => $uid))
            ->field("recharge_rate,recharge_rate_type")
            ->find();

        if ($rateInfo["recharge_rate"] <= 0) {
           return  CommonError::$RecharheRateError;
        }

        $rechargeRate = 0;
        if ($rateInfo["recharge_rate_type"] == 1) {
            $rechargeRate = $rateInfo["recharge_rate"];
        } else {
            $rechargeRate = $data["pay_money"] * $rateInfo["recharge_rate"] / 100;
        }

        $memInfo=Db("member")->where(array("id" => $uid))->field("username,merchant_id,balance")->find();
        $data["commission"] = $rechargeRate;
        $data["realmoney"] = $data["pay_money"] - $rechargeRate;
        $data["addtime"] = time();
        $data["uid"] = $uid;
        $data["username"] = $memInfo["username"];
        $data["merchant_id"]= $memInfo["merchant_id"];
        $blance = $memInfo["balance"];
        $data["balance"] = $blance ? $blance : 0;
        $saveState = Db("recharge")->insert($data);
        if (!$saveState) {
            return  CommonError::$RechargeError;
        }

    }


     /*
      * 支付宝代付接口
      * */

     public function alipay($data = []){
         return $this->payout($data,2);
     }


     /**
      * 银行代付接口
      */
    public function bank($data = []){
        return $this->payout($data,1);
    }


    /**
     * 获取用户基本信息
     */
    public function userinfo($data = []){
        //签名审核
        $checkSign=$this->checksign($data);
        if(!empty($checkSign)){
            if($checkSign["code"]>0){
                return $checkSign;
            }
        }

        //检查是否该商户底下的用户
        $merchantuid=Db("secret")->where(array("secret"=>$data["secret"]))->value("uid");
        $setuid=Db("member")->where(array("id"=>$data["uid"]))->value("merchant_id");
        if($merchantuid!=$setuid){
            return  CommonError::$MerchantuidError;
        }
        
        $field="username,email,create_time,balance,recharge_rate,recharge_rate_type,payment_rate,payment_rate_type,tel";
        if(!empty($data["uid"])){
            $info=Db("member")
                ->where(array("id"=>$data["uid"]))
                ->field($field)
                ->find();
            return $info;
        }else{
            return CommonError::$UserNullError;
        }
    }


    /**
     * 充值信息查询
     */
    public function rechargelist($data = []){
        //签名审核
        $checkSign=$this->checksign($data);
        if(!empty($checkSign)){
            if($checkSign["code"]>0){
                return $checkSign;
            }
        }


        if(empty($data["uid"])){
            return CommonError::$UserNullError;
        }

        //检查是否该商户底下的用户
        $merchantuid=Db("secret")->where(array("secret"=>$data["secret"]))->value("uid");
        $setuid=Db("member")->where(array("id"=>$data["uid"]))->value("merchant_id");
        if($merchantuid!=$setuid){
            return  CommonError::$MerchantuidError;
        }

        $where["uid"] = $data["uid"];


        if (!empty($data["searchword"])) {
            $where["pay_name"] = $data["searchword"];
        }

        if (!empty($data["auth"])) {
            $where["state"] = $data["auth"];
        }


        if(!empty($data["starttime"]) &&  !empty($data["endtime"])){
            $startTime=$data["starttime"];
            $endTime=$data["endtime"];
            $bjTimestr=$startTime.",".$endTime;
            $where["addtime"]=['between',$bjTimestr];
        }
//        return $where;

        $field="recharge_id,pay_name,bank_no,pay_bank,pay_money,addtime,state,commission,realmoney,auth_time";
        $list=$this->modelRecharge->getListApi($where,$field, "recharge_id desc",['page' => 2]);
        foreach ($list as $k=>$v){
            $operation_uname="--";
            if(!empty($v["operation_uid"])){
                $operation_uname=Db("member")->where(array("id"=>$v["operation_uid"]))->value("username");
            }
            $list[$k]["operation_uname"]=$operation_uname;
            $list[$k]["addtime"]=date("Y-m-d H:i:s",$v["addtime"]);
            $list[$k]["auth_time"]=date("Y-m-d H:i:s",$v["auth_time"]);
        }
        return $list;
    }


    /**
     * 代付记录
     */
    public function payment($data = []){
        //签名审核
        $checkSign=$this->checksign($data);
        if(!empty($checkSign)){
            if($checkSign["code"]>0){
                return $checkSign;
            }
        }

        $where = [];
        if (!empty($data["type"])) {
            $where["income_type"] = $data["type"];
        }

        if (!empty($data["auth"])) {
            $where["state"] = $data["auth"];
        }

        if (!empty($data["orderno"])) {
            $where["income_no"]=trim($data["orderno"]);
        }

        //检查是否该商户底下的用户
        $merchantuid=Db("secret")->where(array("secret"=>$data["secret"]))->value("uid");
        $setuid=Db("member")->where(array("id"=>$data["uid"]))->value("merchant_id");
        if($merchantuid!=$setuid){
            return  CommonError::$MerchantuidError;
        }

        $where["uid"]=$data["uid"];

        if(!empty($data["starttime"]) &&  !empty($data["endtime"])){
            $startTime=$data["starttime"];
            $endTime=$data["endtime"];
            $bjTimestr=$startTime.",".$endTime;
            $where["addtime"]=['between',$bjTimestr];
        }


        $field="income_id,income_name,income_banke,income_bank_no,income_money,income_no,income_account,income_type,commission,realmoney,addtime,state,auth_time";
        $list=$this->modelCollectionIncome->getListApi($where, $field, "income_id desc");
        foreach ($list as $k=>$v){
            $operation_uname="--";
            if(!empty($v["operation_uid"])){
                $operation_uname=Db("member")->where(array("id"=>$v["operation_uid"]))->value("username");
            }
            $list[$k]["operation_uname"]=$operation_uname;
            $list[$k]["addtime"]=date("Y-m-d H:i:s",$v["addtime"]);
            $list[$k]["auth_time"]=date("Y-m-d H:i:s",$v["auth_time"]);
        }


        return $list;
    }



    /**
     * 代付申请
     * type 1、银行代付  2支付宝代付
     */
    public function payout($data,$type)
    {
        //签名审核
//        return $checkSign=$this->checksign($data);
        $checkSign=$this->checksign($data);
        if(!empty($checkSign)){
            if($checkSign["code"]>0){
                return $checkSign;
            }
        }

//检查是否该商户底下的用户
        $merchantuid=Db("secret")->where(array("secret"=>$data["secret"]))->value("uid");
        $setuid=Db("member")->where(array("id"=>$data["uid"]))->value("merchant_id");
        if($merchantuid!=$setuid){
            return  CommonError::$MerchantuidError;
        }


        $uid=$data["uid"];
        //检查支付密码
        $passpwd=$data["passpwd"];
        $cheWhere["id"]=$uid;
        $setUrl=url("merchant/index");
        $Setpasspwd=Db("member")->where($cheWhere)->value("pay_password");
        if(empty($Setpasspwd)){
            return  CommonError::$PaypassNullError;
        }
        if(empty($data["passpwd"])){
            return  CommonError::$PaypassNull;
        }
        if($Setpasspwd!=md5($passpwd)){
            return  CommonError::$PaypassError;
        }


        $memsInfo=Db("member")
            ->where(array("id"=>$uid))
            ->field("balance,payment_rate,payment_rate_type,merchant_id")
            ->find();
        $balance=$memsInfo["balance"];
        $paymentRate=$memsInfo["payment_rate"];
        $paymentRateType=$memsInfo["payment_rate_type"];
        $merchant_id=$memsInfo["merchant_id"];
        //计算总金额费率是否够
        $AllMoney=0;
        $RateMoney=0;
            if($data["income_money"]>0){
                $AllMoney=$data["income_money"];
                if($paymentRateType==1){
                    $RateMoney=$paymentRate;
                }else{
                    $RateMoney=$paymentRate*$data["income_money"]/100;
                }
            }

        $sumMoney=$AllMoney+$RateMoney;
        if($balance<$sumMoney){
            return  CommonError::$PayNotBanlnce;
        }


        $subkey=md5(time().rand(0,100));

        //支付宝付款
        if($type==2){
                $account=$data["income_account"];
                $money=$data["income_money"];
                $name=$data["income_name"];
                //检测subkey
                $chekey = Db("collection_income")
                    ->where(array("subkey" => $subkey))
                    ->value("subkey");
                if (!$chekey && $paymentRate>0 && $money>0) {
                    if (!empty($account) && !empty($money) && !empty($name) && $money>0) {
                        $commission=0;
                        if($paymentRateType==1){
                            $commission=$paymentRate;
                        }else{
                            $commission=$paymentRate*$money/100;
                        }

                        //判断余额
                        $realmoney=$money+$commission;
                        $reaBbalance=$balance-$realmoney;
                        if($reaBbalance>=0){
                            $inerData["income_name"]=$name;
                            $inerData["income_money"]=$money;
                            $inerData["income_no"]=setOrderSn("");
                            $inerData["addtime"]=time();
                            $inerData["uid"]=$uid;
                            $inerData["income_account"]=$account;
                            $inerData["income_type"]=2;
                            $inerData["blance"]=$reaBbalance;
                            $inerData["commission"]=$commission;
                            $inerData["realmoney"]=$realmoney;
                            $inerData["subkey"]=$subkey;
                            $inerData["merchant_id"]=$merchant_id;
                            $saveDa=Db("collection_income")->insert($inerData);
                            if($saveDa){
                                //更新用余额
                                Db("member")
                                    ->where(array("id"=>$uid))
                                    ->setDec('balance',$realmoney);

                                #员工账户余额自增
                                /* $uids=Db("member")->where(array("id"=>MEMBER_ID))->value("staff_id");
                                 Db("member")->where(array("id"=>$uids))->setInc('balance',$realmoney);*/
                            }



                    }
                }
            }

        }

        //银行付款
        if($type==1){

                $banke=$data["income_bank"];
                $bankno=$data["income_bank_no"];
                $money=$data["income_money"];
                $name=$data["income_name"];
                //检测subkey
                $chekey = Db("collection_income")
                    ->where(array("subkey" => $subkey))
                    ->value("subkey");
                if (!$chekey && $paymentRate>0 && $money>0) {
                    if (!empty($bankno) && !empty($money) && !empty($name) && $money>0) {

                        $commission=0;
                        if($paymentRateType==1){
                            $commission=$paymentRate;
                        }else{
                            $commission=$paymentRate*$money/100;
                        }

                        //判断余额
                        $realmoney=$money+$commission;
                        $reaBbalance=$balance-$realmoney;
                        if($reaBbalance>=0){
                            $inerData["income_name"]=$name;
                            $inerData["income_money"]=$money;
                            $inerData["income_banke"]=$banke;
                            $inerData["income_bank_no"]=$bankno;
                            $inerData["income_no"]=setOrderSn("");
                            $inerData["addtime"]=time();
                            $inerData["uid"]=$uid;
//                            $inerData["income_account"]=$account;
                            $inerData["income_type"]=1;
                            $inerData["blance"]=$reaBbalance;
                            $inerData["commission"]=$commission;
                            $inerData["realmoney"]=$realmoney;
                            $inerData["subkey"]=$subkey;
                            $inerData["merchant_id"]=$merchant_id;
                            $saveDa=Db("collection_income")->insert($inerData);
                            if($saveDa){
                                //更新用余额
                                Db("member")
                                    ->where(array("id"=>$uid))
                                    ->setDec('balance',$realmoney);

                                #员工账户余额自增
                                /*$uids=Db("member")->where(array("id"=>MEMBER_ID))->value("staff_id");
                                Db("member")->where(array("id"=>$uids))->setInc('balance',$realmoney);*/

                            }
                        }



                }
            }
        }


    }

}
