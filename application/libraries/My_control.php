<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/3
 * Time: 18:47
 */
class My_control{
    private $CI;

    function __construct() {
        # code...
        $this->CI = &get_instance();

        $this->CI->session->set_userdata('admin_id', 9);//货卡之家货运平台管理员
        /*$id = $this->CI->session->userdata('id');
        $admin_name = $this->CI->session->userdata('admin_name');
        $identity = $this->CI->session->userdata('identity');
        if (empty($id) || empty($admin_name) || empty($identity)) {
            header('location:' . site_url('admin/login'));
        }*/
    }
}