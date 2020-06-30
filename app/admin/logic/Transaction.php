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

namespace app\admin\logic;

/**
 * 交易记录
 */
class Transaction extends AdminBase
{

    /**
     * 交易记录
     */
    public function index($data = [])
    {
        $myurls = url();
        cookie("myurl", $myurls);
        $where = [];
        $where["uid"] = MEMBER_ID;

        if(!empty($data["setdate"])){
            $dataobj=explode("-",$data["setdate"]);
            $startTime=strtotime($dataobj[0]);
            $endTime=strtotime($dataobj[1])+86400;
            $bjTimestr=$startTime.",".$endTime;
            $where["addtime"]=['between',$bjTimestr];
        }





        $list=$this->modelTransaction->getList($where, "*", "addtime desc", 10);
        foreach ($list as $k=>$v){
            $lastmoney=0;
            if($v["type"]==1){
                $lastmoney=$v["balance"]-$v["money"];
            }else{
                $lastmoney=$v["balance"]+$v["money"];
            }


            $list[$k]["balance"]=floatval($v['balance']);
            $list[$k]["money"]=floatval($v['money']);
            $list[$k]["lastmoney"]=floatval($lastmoney);
            $list[$k]["addtimetxt"]=date("Y-m-d H:i:s",$v["addtime"]);
        }

        $info["list"]=$list;
        return $info;

    }


}
