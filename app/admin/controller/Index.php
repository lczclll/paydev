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

namespace app\admin\controller;

/**
 * 首页控制器
 */
class Index extends AdminBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        // 执行父类构造方法
        parent::__construct();
        //谷歌登陆验证
        $this->googleauth();

    }
    /**
     * 首页方法
     */
    public function index()
    {

        
        // 获取首页数据
//        $index_data = $this->logicAdminBase->getIndexData();
//        dump($index_data);
//        $this->assign('info', $index_data);

        //账户信息
        $memInfo=Db("member")
            ->where(array("id"=>MEMBER_ID))
            ->field("username,merchant,balance,status")
            ->find();


        $PayWhere["uid"]=MEMBER_ID;
        //付款笔数
        $PayWhere["state"]=2;
        $payNum=Db("collection_income")->where($PayWhere)->count();

        //未付款笔数
        $PayWhere["state"]=1;
        $NoPpayNum=Db("collection_income")->where($PayWhere)->count();


        #统计数据最近三十天
        $ends=0;
        $starts=0;
        $days=30;
        $lenData=$days*86400;
        $setdateInput=date("Y/m/d")."-".date("Y/m/d");

        if(!empty(input("setdate"))){
            $setdateInput=input("setdate");
            $SetDataObj=explode("-",$setdateInput);
            $ends=strtotime($SetDataObj[1])+86400;
            $starts=strtotime($SetDataObj[0])-86400;
            $days=(int)(($ends-$starts)/86400);
        }else{
            $payStart=Db("collection_income")->order("addtime desc")->value("addtime");
            $RechargStart=Db("recharge")->order("addtime desc")->value("addtime");
            $payStart=$payStart?$payStart:0;
            $RechargStart=$RechargStart?$RechargStart:0;

            if($payStart>$RechargStart){
                $ends=$payStart;
            }else{
                $ends=$RechargStart;
            }
            $starts=$ends-$lenData;
        }





        $TjData=[];
        $TjWhere=[];
        if(!IS_ROOT){
            /*$UidArr=Db("member")->where(array("staff_id"=>MEMBER_ID))->column("id");
            $TjWhere["uid"]=["in",$UidArr];*/

            $groupId=Db("member")->where(array("id"=>MEMBER_ID))->value("group_id");
            //商户
            if($groupId==config("MERCHANTTYPEID")){
                $TjWhere["merchant_id"]=MEMBER_ID;
            }
            //员工
            if($groupId==config("YGUSERTYPEID")){
                $midArr=Db("member")->where(array("staff_id"=>MEMBER_ID))->column("id");
                $TjWhere["uid"]=["in",$midArr];
            }
            //用户
            if($groupId==config("YHUSERTYPEID")){
                $TjWhere["uid"]=MEMBER_ID;
            }

        }


        $payTimestr=$starts.",".$ends;
        $TjWhere["addtime"]=['between',$payTimestr];
        //代付总额  代付笔数
        $TjData["paymoneys"]=Db("collection_income")->where($TjWhere)->sum("realmoney");
        $TjData["paynum"]=Db("collection_income")->where($TjWhere)->count();
        //充值总额 充值笔数
        $TjData["remoneys"]=Db("recharge")->where($TjWhere)->sum("pay_money");
        $TjData["renum"]=Db("recharge")->where($TjWhere)->count();





        $DataList=[];
        $memList=[];
        $memWhere=[];

       /* if(IS_YH){
            $memList=[MEMBER_ID];
        }else{*/

                if(!IS_ROOT){
                    /*$UidArr=Db("member")->where(array("staff_id"=>MEMBER_ID))->column("id");
                    $memList=$UidArr;*/


                    $groupId=Db("member")->where(array("id"=>MEMBER_ID))->value("group_id");
                    $memWhere=[];
                    //商户
                    if($groupId==config("MERCHANTTYPEID")){
                        $memWhere["merchant_id"]=MEMBER_ID;
                    }
                    //员工
                    if($groupId==config("YGUSERTYPEID")){
                        $midArr=Db("member")->where(array("staff_id"=>MEMBER_ID))->column("id");
                        $memWhere["id"]=["in",$midArr];
                    }
                    //用户
                    if($groupId==config("YHUSERTYPEID")){
                        $memWhere["id"]=MEMBER_ID;
                    }
                    $memList=Db("member")->where($memWhere)->column("id");

                }else{
                    $memWhere=[];
                    if(!empty(input("uname"))){
                        $unWhere["merchant"]=trim(input("uname"));
                        $memIdArr=Db("member")->where($unWhere)->column("id");
                        $memWhere["member_id"]=["in",$memIdArr];
                    }
//                    $memWhere["merchant_id"]=MEMBER_ID;
                    $memList=Db("auth_group_access")->where($memWhere)->column("member_id");
                }


//        }



        if($ends>0){
            $ends=strtotime(date("Y-m-d",$ends))+86400;
            unset($TjWhere["addtime"]);
            $payWhere=$TjWhere;
//            $starts=$ends-$lenData;
            for ($i=0;$i<$days;$i++){
                $endSet=$ends-$i*86400;
                $statSet=$endSet-86400;
                $bjTimestr=$statSet.",".$endSet;
                $DaKey=0;
                foreach ($memList as $k=>$v){

                    $bjTimestr=$statSet.",".$endSet;
                    $payWhere["addtime"]=['between',$bjTimestr];
                    //统计代付金额 笔数
                    $payWhere["uid"]=$v;
                    $payMoney=Db("collection_income")->where($payWhere)->sum("realmoney");
                    $payNum=Db("collection_income")->where($payWhere)->count();

                    //统计充值金额 笔数
                    $reMoney=Db("recharge")->where($payWhere)->sum("pay_money");
                    $reNum=Db("recharge")->where($payWhere)->count();

                    if($payMoney>0 || $payNum>0 || $reMoney>0 || $reNum>0){
                        $DataList[$i][$DaKey]["paymoney"]=$payMoney?$payMoney:0;
                        $DataList[$i][$DaKey]["paynum"]=$payNum?$payNum:0;
                        $DataList[$i][$DaKey]["remoney"]=$reMoney?$reMoney:0;
                        $DataList[$i][$DaKey]["renum"]=$reNum?$reNum:0;
                        $DataList[$i][$DaKey]["uname"]=Db("member")->where(array("id"=>$v))->value("username");
                        $setdate=$endSet-86400;
                        $DataList[$i][$DaKey]["date"]=date("Y-m-d",$setdate);
                        $DaKey+=1;
                    }
                }
            }
        }




        $this->assign("setdate",$setdateInput);
        $this->assign("datalist",$DataList);
        $this->assign("tjdata",$TjData);
        $this->assign("paynum",$payNum);
        $this->assign("nopaynum",$NoPpayNum);
        $this->assign("meminfo",$memInfo);
        return $this->fetch('index');
    }

    /**
     *
     */
    public function postset(){
//post数据

        $post_data = array(

            'access_token'   => '5b7f60aca7e7f6f8c680b1b219ad3ec6',                                 // 用户ID

            'list_rows'    => 1,
            'page'  =>1,
            'uid'=>976,
            'starttime'=>0,
            'endtime'=>0,
            'searchword'=>'',
            'auth'=>0,
            'orderno'  =>'20200618232951382',

            'secret'    => '6651cab444bbf5285df3228a8bf879ea',
            'key'=>'43011592466582',

//            'income_money'=>'100',
//            'income_name'=>'陈金宝',
//            'income_bank'=>'建设银行',
//            'income_bank_no'=>'324534534565432',
//            'passpwd'=>'123456'

        );

        ksort($post_data);
        $snstrs="";
        $keystrs="";
        foreach ($post_data as $k=>$v){
            $snstrs.=$v;
            $keystrs.=$k;
        }

        echo $keystrs;

        $signs=md5($snstrs);

        $post_data["sign"]=$signs;
        $url = 'http://api.pay.xmjqdl.com/devpay/payment';

        $res = posturl($url, $post_data);
//        dump($res);
        echo $res;
        $readd=json_decode($res,true);
        dump($readd["data"]);
    }


    public function mediomsg(){
        $groupId=Db("member")->where(array("id"=>MEMBER_ID))->value("group_id");

        //商户
        if($groupId==config("MERCHANTTYPEID")){
            $where["merchant_id"]=MEMBER_ID;
        }
        //员工
        if($groupId==config("YGUSERTYPEID")){
            $midArr=Db("member")->where(array("staff_id"=>MEMBER_ID))->column("id");
            $where["uid"]=["in",$midArr];
        }
        //用户
        if($groupId==config("YHUSERTYPEID")){
            $where["uid"]=MEMBER_ID;

        }

        $where["state"]=["in","1"];

        $autPay=Db("collection_income")->where($where)->count();
        $autRcharge=Db("recharge")->where($where)->count();

        $info["pay"]=0;
        $info["recharge"]=0;
        if($autPay>0){
            $info["pay"]=1;
        }
        if($autRcharge>0){
            $info["recharge"]=1;
        }
        $result['code']=1;
        $result['data'] =$info;
        return json($result) ;
    }
}
