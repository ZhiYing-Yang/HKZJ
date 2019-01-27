<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Source extends CI_Controller{
  public function __construct() {
    parent::__construct();
        if(empty($this->session->userdata('user_id'))){
            $this->session->set_userdata('user_id', 9);
        }
        $this->load->library('my_control'); //登录控制
        $this->load->model('admin_model');
        $this->load->model('source_model');
  }

  //用户列表
  public function user_list($type = 'id', $offset = 0){
      $type = urldecode($type);
      $where_arr = array();
      // if($type != '全部'){
      //     $where_arr = array('identify'=>$type);
      // }
      $per_page = 10;
      $page_url = site_url('admin/source/user_list/'.$type);
      $total_rows = $this->db->count_all_results('source_user');
      $offset_uri_segment = 5;
      $this->load->library('myclass');
      $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);

      $data['user'] = $this->source_model->get_user_list($where_arr, $offset, $per_page,$type);
      $this->load->view('admin/source/user_list.html', $data);
  }

  //搜索用户
  public function user_search($keywords){
      $data['keywords'] = urldecode($keywords);
      $data['user'] = $this->source_model->get_user_search($keywords);
      $data['link'] = '';
      $this->load->view('admin/source/user_list.html', $data);
  }

  //用户具体信息
  public function user_info($id){
      $user = $this->source_model->get_user_info(array('id'=>$id));
      if(empty($user)){
         alert_msg('用户不存在！');
      }

      $data['user'] = $user[0];
      $data['total_rows'] = $this->source_model->get_total_rows(array('user_id'=>$id), 'resource');
      // $apply = $this->source_model->get_apply_list(array('user_id'=>$id), 0, 1);
      // if(!empty($apply)){
      //     $data['apply'] = $apply[0];
      // }

      $this->load->view('admin/source/user_info.html', $data);
  }

  //车辆列表
  public function car_list($type = 'id',$offset = 0){
    $type = urldecode($type);
    $where_arr = array('isgood'=>0);
    $per_page = 10;
    $page_url = site_url('admin/source/car_list/'.$type);
    $total_rows = $this->source_model->get_total_rows(array('isgood'=>0), 'resource');
    $offset_uri_segment = 5;
    $this->load->library('myclass');
    $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);
    $data['list'] = $this->source_model->get_list($where_arr, $offset, $per_page,$type);
    $this->load->view('admin/source/car_list.html', $data);
  }
  //筛选
  public function car_type_list($type='',$data_type='',$offset = 0){
    $type = urldecode($type);
    $data_type = urldecode($data_type);
    $where_arr = array('isgood' => 0,$type=>$data_type);
    $per_page = 10;
    $page_url = site_url('admin/source/car_type_list/'.$type.'/'.$data_type);
    $total_rows = $this->source_model->get_total_rows($where_arr, 'resource');
    $offset_uri_segment = 5;
    $this->load->library('myclass');
    $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);
    $data['list'] = $this->source_model->get_list($where_arr, $offset, $per_page);
    $this->load->view('admin/source/car_list.html', $data);

  }

  //车辆搜索
  public function car_search($keywords, $offset = 0){
      $data['keywords'] = urldecode($keywords);;
      $data['list'] = $this->source_model->get_search_list($data['keywords'], $offset, 10);

      $data['link'] = '';
      $this->load->view('admin/source/car_list.html', $data);
  }

  //车辆信息操作
  public function car_action($action, $id){
      $car = $this->admin_model->get_source_info(array('id'=>$id));
      if(empty($car)){ //判断车辆是否存在
          alert_msg('该信息已被删除！');
      }
      $car = $car[0];
      $status = false;
      if($action == 'delete'){  //删除
          $msg = '删除';
          if($this->db->delete('resource', array('id'=>$id))){
              $status = true;
          }
      }

      if($status){
          alert_msg($msg.'成功');
      }else{
          alert_msg($msg.'失败，请稍后重试！');
      }
  }


  //货物列表
  public function goods_list($type = 'id',$offset = 0){
    $type = urldecode($type);
    $where_arr = array('isgood'=>1);
    $per_page = 10;
    $page_url = site_url('admin/source/goods_list/'.$type);
    $total_rows = $this->source_model->get_total_rows(array('isgood'=>1), 'resource');
    $offset_uri_segment = 5;
    $this->load->library('myclass');
    $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);
    $data['list'] = $this->source_model->get_list($where_arr, $offset, $per_page,$type);
    $this->load->view('admin/source/goods_list.html', $data);
  }
  //货物筛选
  public function goods_type_list($type='',$data_type='',$offset = 0){
    $type = urldecode($type);
    $data_type = urldecode($data_type);
    $where_arr = array('isgood' => 1,$type=>$data_type);
    $per_page = 10;
    $page_url = site_url('admin/source/goods_type_list/'.$type.'/'.$data_type);
    $total_rows = $this->source_model->get_total_rows($where_arr, 'resource');
    $offset_uri_segment = 5;
    $this->load->library('myclass');
    $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);
    $data['list'] = $this->source_model->get_list($where_arr, $offset, $per_page);
    $this->load->view('admin/source/goods_list.html', $data);

  }

  //货物搜索
  public function goods_search($keywords, $offset = 0){
      $data['keywords'] = urldecode($keywords);;
      $data['list'] = $this->source_model->get_search_list($data['keywords'], $offset, 10);

      $data['link'] = '';
      $this->load->view('admin/source/goods_list.html', $data);
  }

  //货物信息操作
  public function goods_action($action, $id){
      $car = $this->admin_model->get_source_info(array('id'=>$id));
      if(empty($car)){ //判断车辆是否存在
          alert_msg('该信息已被删除！');
      }
      $car = $car[0];
      $status = false;
      if($action == 'delete'){  //删除
          $msg = '删除';
          if($this->db->delete('resource', array('id'=>$id))){
              $status = true;
          }
      }

      if($status){
          alert_msg($msg.'成功');
      }else{
          alert_msg($msg.'失败，请稍后重试！');
      }
  }


}
