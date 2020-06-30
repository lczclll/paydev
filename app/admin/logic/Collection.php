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
 * 收款管理
 */
class Collection extends AdminBase
{

    /**
     * 收款账户管理
     */
    public function account($data = [])
    {
        $where=[];
        if(!empty($data["search_data"])){
            $where["collection_name"]=$data["search_data"];
        }
        $where['uid']=MEMBER_ID;
        return $this->modelCollectionAccount->getList($where,"*", "collection_id desc", 10);

    }

    /**
     * 收款账户管理添加
     */
    public function accountadd($data = [])
    {
        $url = url('account');
        if(empty($data["collection_name"])){
            return [RESULT_ERROR,"请填写收款人"];
            exit();
        }
        if(empty($data["collection_no"])){
            return [RESULT_ERROR,"请填写收款账号"];
            exit();
        }
        if(empty($data["collection_bank"])){
            return [RESULT_ERROR,"请填写收款银行"];
            exit();
        }
        $data['uid']=MEMBER_ID;
        $saveState=Db("collection_account")->insert($data);
        if($saveState){
            return [RESULT_SUCCESS, '添加收款账户成功', $url];
        }else{
            return [RESULT_ERROR,"添加失败"];
        }



    }

    /**
     * 收款账户管理编辑
     */
    public function accountedit($data = [])
    {
        $url = url('account');
        if(empty($data["collection_name"])){
            return [RESULT_ERROR,"请填写收款人"];
            exit();
        }
        if(empty($data["collection_no"])){
            return [RESULT_ERROR,"请填写收款账号"];
            exit();
        }
        if(empty($data["collection_bank"])){
            return [RESULT_ERROR,"请填写收款银行"];
            exit();
        }
        $saveState=Db("collection_account")->update($data);
        return [RESULT_SUCCESS, '编辑收款账户成功', $url];

    }


}
