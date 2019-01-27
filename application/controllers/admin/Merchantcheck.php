<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *商家入驻控制器
 *
 */
class Merchantcheck extends CI_Controller {

  public function __construct(){
      parent::__construct();

      $this->load->model('merchant_model' , "merchant");
  }

    //审核列表
  public function check($module , $offset=0){

      $module = urldecode($module);


      //条件拼接
      $where = array("check " => 0);
      $module == "全部"?  : $where["module"] = $module  ;

      //分页
      $per_page=10;
      $page_url = site_url("admin/merchantcheck/check/".$module);
      $total_rows = $this->merchant->count_total_rows($where);
      $offset_uri_segment = 5;
      $this->load->library('myclass');
      $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);
      //查数据库
      $result = $this->merchant->get_check_list($where ,$offset ,$per_page);
      $data['merchant' ] = $result;

      $this->load->view("admin/merchantcheck/check.html"  ,$data);

  }
  //审核通过
  public function pass_check($id)
  {//更改用户字段的商人
      $userid = $this->db->get_where("merchant",array("id"=>$id))->result_array()[0]["user_id"];
      if($this->db->update("merchant" ,array('check' =>"1" ) , array('id'=>$id) ) && $this->db->update("user", array('is_merchant' =>1 ),array('user_id'=>$userid) )) {
        take_json("okay" , "审核提交成功");
      }else{
        take_json("fail" ,"审核提交失败");
      }
  }
  public function del_check($id)
  {
    $merchant = $this->merchant->get_info_byID($id);
    if($this->db->delete("merchant" , array("id"=>$id)) && $this->db->update("user", array('is_merchant' =>0),array('user_id'=>$merchant["user_id"]) ) ){
      take_json("okay" ,"删除成功");
    }else{
      take_json("okay" ,"删除失败");
    }
  }

  public function see_apply_message($id)
  {
      $result = $this->merchant->get_info_byID($id);
      $data = array('m' =>$result  );
      $this->load->view("admin/merchantcheck/see_apply_message.html" ,$data);
  }
  public function merchant($module="全部" ,$offset=0){

    $module = urldecode($module);


          //条件拼接
    $where = array("check !=" => 0);
    $module == "全部"?  : $where["module"] = $module  ;
          //分页
    $per_page=10;
    $page_url = site_url("admin/merchantcheck/merchant/".$module);
    $total_rows = $this->merchant->count_total_rows($where);
    $offset_uri_segment = 5;
    $this->load->library('myclass');
    $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);
          //查数据库
    $result = $this->merchant->get_check_list($where ,$offset ,$per_page);
    $data['merchant' ] = $result;

    $this->load->view("admin/merchantcheck/merchant.html"  ,$data);

  }

  /**
   * 冻结商家
   */
  public function merchant_freeze($id)
  {
    if($this->db->update("merchant" , array("nature"=>2)  ,array("id" =>$id)  )  ){
      take_json("okay" ,"冻结商家成功");
    }else{
      take_json("okay" ,"冻结商家成功");
    }
  }




}
