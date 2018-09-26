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

        $id = $this->CI->session->userdata('admin_id');
        $admin_name = $this->CI->session->userdata('admin_name');
        $identity = $this->CI->session->userdata('identity');
        if (empty($id) || empty($admin_name) || empty($identity)) {
            header('location:' . site_url('admin/login'));
        }
    }
}