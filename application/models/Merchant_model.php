<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Merchant_model extends CI_Model {

  //获取卖车列表信息
  public function get_check_list($where , $offset, $per_page = 10, $order_str = 'create_time DESC'){
      $status = $this->db->order_by($order_str)->get_where('merchant', $where, $per_page, $offset)->result_array();
      //echo $this->db->last_query();
      return $status;
  }

  //获取所有数据条数
  public function count_total_rows($where_arr, $table="merchant"){
      $status = $this->db->where($where_arr)->count_all_results($table);
      return $status;
  }

  public function get_info_byID($id)
  {
    return $this->db->get_where("merchant" ,array("id"=>$id))->row_array();
  }

  public function get_merchant_id_by_userid($user_id){
    return $this->db->get_where("merchant",array("user_id"=>$user_id))->row_array();
  }
  //获取商家
  public function get_merchant($type='', $limit)
  {
    if(empty($type)){
      return $this->db->select("*")->from("merchant")->where(array("check"=>1))->limit($limit,0)->get()->result_array();
    }else{
      return $this->db->select("*")->from("merchant")->where(array("module"=>$type , "check"=>1))->limit($limit , 0 )->get()->result_array();
    }
  }


}
