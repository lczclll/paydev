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
 * 交易记录
 */
class Transaction extends AdminBase
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
     * 交易记录
     */
    public function index()
    {
        
//        IS_POST && $this->jump($this->logicCollection->account($this->param));

        $loginInfo=$this->logicTransaction->index($this->param);

        $setdate=date("Y/m/d")."-".date("Y/m/d");
        if(!empty(input("setdate"))){
            $setdate=input("setdate");
        }


        $this->assign('setdate',$setdate);

        $this->assign('acclist',$loginInfo["list"]);
        $this->assign('isroot',IS_ROOT);
        return $this->fetch('index');
    }


}
