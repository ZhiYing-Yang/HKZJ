<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/16
 * Time: 12:27
 */
defined('BASEPATH') OR exit('No direct script access allowed');
class Monitor extends CI_Controller{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('my_control'); //登录控制

        $this->load->model('monitor_model');
    }

    //用户列表
    public function user_list($order = 'default', $offset = 0){
        if($order = 'money'){
            $order_str = 'money DESC';
        }else{
            $order_str = 'id DESC';
        }
        $this->load->library('myclass');
        $page_url = site_url('admin/monitor/user_list'.$order);
        $offset_uri_segment = 5;
        $total_rows = $this->db->count_all_results('monitor_user');
        $per_page = 10;
        $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);
        $data['user'] = $this->monitor_model->get_user_list(array(), $order_str, $offset, $per_page);
        $this->load->view('admin/monitor/user_list.html', $data);
    }

    //搜索用户
    public function user_search($keywords){
        $data['keywords'] = urldecode($keywords);
        $data['user'] = $this->monitor_model->get_user_search($data['keywords']);
        $data['link'] = '';
        $this->load->view('admin/monitor/user_list.html', $data);
    }

}