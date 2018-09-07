<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/4
 * Time: 19:45
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Monitor_model extends CI_model {
    public function get_user_info($where_arr){
        $status = $this->db->get_where('monitor_user', $where_arr)->result_array();
        return $status;
    }
}