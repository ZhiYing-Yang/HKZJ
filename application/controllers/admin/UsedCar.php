<?php
/**
 * Created by Administartor.
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

        $this->load->model('usedcar_model');  //别名admin
    }

    //用户列表
    public function user_list($type = '全部', $offset = 0){
        $type = urldecode($type);
        $where_arr = array();
        if($type != '全部'){
            $where_arr = array('identify'=>$type);
        }
        $per_page = 10;
        $page_url = site_url('admin/usedCar/user_list/'.$type);
        $total_rows = $this->db->where($where_arr)->count_all_results('used-car_user');
        $offset_uri_segment = 5;
        $this->load->library('myclass');
        $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);

        $data['user'] = $this->usedcar_model->get_user_list($where_arr, $offset, $per_page);
        $this->load->view('admin/usedcar/user_list.html', $data);
    }

    //搜索用户
    public function user_search($keywords){
        $data['keywords'] = urldecode($keywords);
        $data['user'] = $this->usedcar_model->get_user_search($keywords);
        $data['link'] = '';
        $this->load->view('admin/usedcar/user_list.html', $data);
    }

    //认证申请列表
    public function apply_list($status = 0, $offset = 0){
        if($status != 0 && $status !=1){
            alert_msg('参数错误');
        }

        $where_arr = array('status'=>0);

        $page_url = site_url('admin/usedCar/apply_list/'.$status);
        $total_rows = $this->usedcar_model->get_total_rows($where_arr, 'used-car_apply');
        $offset_uri_segment = 5;
        $per_page = 10;
        $this->load->library('myclass');
        $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);
        $data['apply'] = $this->usedcar_model->get_apply_list($where_arr, $offset, $per_page);
        $this->load->view('admin/usedcar/apply_list.html', $data);
    }

    //认证页面
    public function approve($id){
        if(!is_numeric($id)){
            alert_msg('参数错误');
        }
        $data['apply'] = $this->usedcar_model->get_apply_info(array('id'=>$id))[0]; //申请信息
        if(empty($data['apply'])){
            alert_msg('该申请已被删除');
        }
        $data['user'] = $this->usedcar_model->get_user_info(array('id'=>$data['apply']['user_id']))[0]; //发起申请的用户的信息
        $this->load->view('admin/usedcar/apply_info.html', $data);
    }

    public function do_approve(){
        $apply_id = $this->input->post('apply_id'); //申请信息 id
        $code = $this->input->post('code'); //认证结果 1=>通过认证 -1=> 未通过
        $status = $this->db->update('used-car_apply', array('status'=>$code), array('id'=>$apply_id));
        if($code == 1){
            $user_id = $this->input->post('user_id');
            $identify = $this->input->post('identify');
            $this->db->update('used-car_user', array('identify'=>$identify), array('id'=>$user_id));
        }

        if($status){
            get_json(200, '操作成功！');
        }else{
            get_json(400, '操作失败，请稍后重试！');
        }

    }
    public function developLog(){
        $this->load->view("admin/usedcar/developLog.html");
    }

}