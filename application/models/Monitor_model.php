<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/4
 * Time: 19:45
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Monitor_model extends CI_model {
    //获取车辆监控模块用户信息
    public function get_user_info($where_arr){
        $status = $this->db->get_where('monitor_user', $where_arr)->result_array();
        return $status;
    }

    public function get_user_list($where_arr, $order_str = 'id DESC', $offset=0, $per_page = 10){
        $status = $this->db->order_by($order_str)->get_where('monitor_user', $where_arr, $per_page, $offset)->result_array();
        return $status;
    }
}