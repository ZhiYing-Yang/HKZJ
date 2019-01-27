<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/13
 * Time: 16:26
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Source_model extends CI_model {
    /**
     * 获取所有货物信息
     */
    public function get_list($where_arr, $offset, $per_page = 10, $order_str = 'uptime DESC'){
        $status = $this->db->order_by($order_str)->get_where('resource', $where_arr, $per_page, $offset)->result_array();
        //echo $this->db->last_query();
        return $status;
    }
    //获取用户信息
    public function get_user_info($where_arr){
        $status = $this->db->get_where('source_user', $where_arr)->result_array();
        return $status;
    }

    //获取搜索到的信息
    public function get_search_list($keywords, $offset, $per_page = 10){
        $this->db->like('departure', $keywords)->or_like('destination', $keywords)->or_like('model', $keywords)->or_like('path', $keywords)->
        or_like('contact', $keywords)->or_like('tel', $keywords);
        $status = $this->db->order_by('uptime DESC')->get_where('resource', array(), $per_page, $offset)->result_array();
        return $status;
    }

    //获取收藏信息
    public function get_collect_list($where_in, $offset, $perpage = 10){
      $this->db->where_in('id', $where_in);
      $status = $this->db->order_by('uptime DESC')->get_where('resource', array(), 10, $offset)->result_array();
      return $status;
    }

    //用户列表
    public function get_user_list($where_arr, $offset, $per_page = 10,$type){
        $status = $this->db->order_by("$type DESC")->get_where('source_user', $where_arr, $per_page, $offset)->result_array();
        return $status;

    }

    //搜索用户
    public function get_user_search($keywords){
        $keywords = addslashes($keywords);
        $status = $this->db->like('nickname', $keywords)->or_like('realname', $keywords)->get_where('source_user')->result_array();
        return $status;
    }

    // //认证申请
    // public function get_apply_list($where_arr, $offset, $per_page = 10){
    //     $status = $this->db->order_by('create_time DESC')->get_where('used-car_apply', $where_arr, $per_page, $offset)->result_array();
    //     return $status;
    // }
    //
    // //获取弹跳认证信息
    // public function get_apply_info($where_arr){
    //     return $this->db->get_where('used-car_apply', $where_arr)->result_array();
    // }

    //获取所有数据条数
    public function get_total_rows($where_arr, $table){
        $status = $this->db->where($where_arr)->count_all_results($table);
        return $status;
    }
}
