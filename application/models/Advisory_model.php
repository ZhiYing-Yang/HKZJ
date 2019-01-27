<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Advisory_model extends CI_Model {
  //获取收藏信息
  public function get_collect_list($where_in, $offset, $perpage = 10){
      $this->db->where_in('id', $where_in);
      $status = $this->db->order_by('create_time DESC')->get_where('advisory', array(), 10, $offset)->result_array();
      return $status;
  }

  //获取用户信息
  public function get_user_info($where_arr){
      $status = $this->db->get_where('advisory_user', $where_arr)->result_array();
      return $status;
  }

  //获取筛选新闻
  public function get_news_list($where_arr, $offset, $per_page = 10, $order_str = 'create_time DESC'){
      $status = $this->db->order_by($order_str)->get_where('advisory', $where_arr, $per_page, $offset)->result_array();
      //echo $this->db->last_query();
      return $status;
  }

  //获取所有新闻
  public function get_all_news($order_str = 'create_time DESC',$where_arr=array()){
      $status = $this->db->order_by($order_str)->get_where('advisory',$where_arr)->result_array();
      //echo $this->db->last_query();
      return $status;
  }

  //获取搜索到的信息
  public function get_search_list($keywords, $offset, $per_page = 10){
      $this->db->like('title', $keywords)->or_like('id', $keywords);
      $status = $this->db->order_by('create_time DESC')->get_where('advisory', array(), $per_page, $offset)->result_array();
      return $status;
  }

  //获取所有数据条数
  public function get_total_rows($where_arr, $table){
      $status = $this->db->where($where_arr)->count_all_results($table);
      return $status;
  }

  //获取搜索到的信息(全部)
  public function get_news_search($keywords){
      $this->db->like('title', $keywords);
      $status = $this->db->order_by('create_time DESC')->get_where('advisory')->result_array();
      return $status;
  }
}
