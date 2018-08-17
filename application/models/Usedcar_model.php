<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/13
 * Time: 16:26
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Usedcar_model extends CI_model {
    public function get_user_info($where_arr){
        $status = $this->db->get_where('used-car_user', $where_arr)->result_array();
        return $status;
    }

    //获取卖车列表信息
    public function get_sale_list($where_arr, $offset, $per_page = 10){
        $status = $this->db->get_where('used-car_sale', $where_arr, $per_page, $offset)->result_array();
        return $status;
    }
}