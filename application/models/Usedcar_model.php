<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/13
 * Time: 16:26
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Usedcar_model extends CI_model {

    //获取用户信息
    public function get_user_info($where_arr){
        $status = $this->db->get_where('used-car_user', $where_arr)->result_array();
        return $status;
    }
    //获取收藏信息
    public function get_collect_list($where_in, $offset, $perpage = 10){
        $this->db->where_in('id', $where_in);
        $status = $this->db->order_by('create_time DESC')->get_where('used-car_sale', array(), 10, $offset)->result_array();
        return $status;
    }
    //获取卖车列表信息
    public function get_sale_list($where_arr, $offset, $per_page = 10, $order_str = 'create_time DESC'){
        $status = $this->db->order_by($order_str)->get_where('used-car_sale', $where_arr, $per_page, $offset)->result_array();
        //echo $this->db->last_query();
        return $status;
    }
    //获取搜索到的卖车信息
    public function get_search_list($keywords, $offset, $per_page = 10){
        $this->db->like('title', $keywords)->or_like('postscript', $keywords)->or_like('brand', $keywords);
        $status = $this->db->order_by('create_time DESC')->get_where('used-car_sale', array(), $per_page, $offset)->result_array();
        return $status;
    }
    //格式化车辆参数
    public function get_format_parameter($data){
        $parameter = array(
            '牵引车'=>array('排放标准'=>'', '变速箱挡位'=>'', '驱动形式'=>'', '马力'=>'马力', '货箱长度'=>'米', '后桥速比'=>''),
            '载货车'=>array('排放标准'=>'', '变速箱挡位'=>'', '驱动形式'=>'', '马力'=>'马力', '货箱形式'=>'', '货箱长度'=>'米', '准载质量'=>'吨', '自重'=>'吨'),
            '自卸车'=>array('排放标准'=>'', '变速箱挡位'=>'', '驱动形式'=>'', '马力'=>'马力', '货箱长度'=>'米', '后桥速比'=>''),
            '轻车' => array('排放标准'=>'', '变速箱挡位'=>'', '马力'=>'马力', '货箱长度'=>'米', '准载质量'=>'吨'),
            '挂车' => array('挂车形式'=>'', '轴数'=>'', '悬挂形式'=>'', '挂车长度'=>'米'),
            '搅拌车'=>array('排放标准'=>'', '变速箱挡位'=>'', '驱动形式'=>'', '马力'=>'马力', '后桥速比'=>'', '上装机品牌'=>'', '减速机品牌'=>'', '方量'=>''),
            '专用车'=>array('排放标准'=>'', '变速箱挡位'=>'', '驱动形式'=>'', '马力'=>'马力')
        );

        $i = 0;
        $parameter_arr = array();
        foreach($parameter[$data['sale']['car_type']] as $k => $v){
            $parameter_arr[$k] = $data['sale']['parameter'.$i].$v;
            $i++;
        }
        return $parameter_arr;
    }
    //获取开发日志
    public function develop(){

    }

    /*
     * 二手车后台管理
     * */

    //用户列表
    public function get_user_list($where_arr, $offset, $per_page = 10){
        $status = $this->db->order_by('id DESC')->get_where('used-car_user', $where_arr, $per_page, $offset)->result_array();
        return $status;

    }

    //搜索用户
    public function get_user_search($keywords){
        $keywords = addslashes($keywords);
        $status = $this->db->like('nickname', $keywords)->or_like('realname', $keywords);
        return $status;
    }

    //认证申请
    public function get_apply_list($where_arr, $offset, $per_page = 10){
        $status = $this->db->order_by('create_time DESC')->get_where('used-car_apply', $where_arr, $per_page, $offset)->result_array();
        return $status;
    }

    //获取弹跳认证信息
    public function get_apply_info($where_arr){
        return $this->db->get_where('used-car_apply', $where_arr)->result_array();
    }

    //获取所有数据条数
    public function get_total_rows($where_arr, $table){
        $status = $this->db->where($where_arr)->count_all_results($table);
        return $status;
    }


}