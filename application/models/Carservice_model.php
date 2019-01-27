<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Carservice_model extends CI_Model {
  //获取所有信息
  public function get_all_service($order_str = 'create_time DESC',$where_arr=array()){
      $status = $this->db->order_by($order_str)->get_where('carservice',$where_arr)->result_array();
      //echo $this->db->last_query();
      return $status;
  }

  public function get_user_info($where_arr){
      $status = $this->db->get_where('carservice_user', $where_arr)->result_array();
      return $status;
  }


  //获取筛选新闻
  public function get_news_list($where_arr, $offset, $per_page = 10, $order_str = 'create_time DESC'){
      $status = $this->db->order_by($order_str)->get_where('carservice', $where_arr, $per_page, $offset)->result_array();
      //echo $this->db->last_query();
      return $status;
  }

  //获取所有新闻
  public function get_all_news($order_str = 'create_time DESC',$where_arr=array()){
      $status = $this->db->order_by($order_str)->get_where('carservice',$where_arr)->result_array();
      //echo $this->db->last_query();
      return $status;
  }

  //获取搜索到的信息
  public function get_search_list($keywords, $offset, $per_page = 10){
      $this->db->like('name', $keywords)->or_like('username', $keywords)->or_like('position2', $keywords);
      $status = $this->db->order_by('create_time DESC')->get_where('carservice', array(), $per_page, $offset)->result_array();
      return $status;
  }

  //获取所有数据条数
  public function get_total_rows($where_arr, $table){
      $status = $this->db->where($where_arr)->count_all_results($table);
      return $status;
  }

  //获取搜索到的信息(全部)
  public function get_news_search($keywords,$where_arr=array()){
    $this->db->like('name', $keywords)->or_like('username', $keywords)->or_like('position2', $keywords);
      $status = $this->db->order_by('create_time DESC')->get_where('carservice',$where_arr)->result_array();
      return $status;
  }
}
?>
