<?php
/**
 * User: Noel
 * Date: 2018/8/13
 * Time: 16:26
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Partssell_model extends CI_model {

    //获取用户信息
    public function get_user_info($where_arr){
        $status = $this->db->get_where('parts-sell_user', $where_arr)->result_array();
        return $status;
    }

    //获取搜索到的信息
    public function get_search_list($keywords, $offset, $per_page = 10){
      $this->db->like('name', $keywords)->or_like('content', $keywords)->or_like('detail', $keywords)->or_like('class', $keywords);
      $status = $this->db->order_by('uptime DESC')->get_where('parts-sell_source', array(), $per_page, $offset)->result_array();
      return $status;
    }

    //获取货物信息
    public function get_list($where_arr, $offset, $per_page = 10, $order_str = 'uptime DESC'){
        $status = $this->db->order_by($order_str)->get_where('parts-sell_source', $where_arr, $per_page, $offset)->result_array();
        //echo $this->db->last_query();
        return $status;
    }

    //获取所有数据条数
    public function get_total_rows($where_arr, $table){
        $status = $this->db->where($where_arr)->count_all_results($table);
        return $status;
    }

    //where in
    public function get_where($column_name,$data){
      $data = $this->db->get_where('parts-sell_source',array($column_name=>$data))->result_array();
      return $data;
    }
}
?>
