<?php
/**
 * Created by Caoyuxin.
 * Date: 2018/9/2
 * Time: 17:16
 * @desc[ 二手车栏目控制器 ]
 */
defined('BASEPATH') OR exit('No direct script access allowed');
class UsedCar extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->library('my_control'); //登录控制

        $this->load->model('admin_model','admin');  //别名admin
    }


    public function developLog(){
        $this->load->view("admin/usedcar/developLog.html");
    }
}