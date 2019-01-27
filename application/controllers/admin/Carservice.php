<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Carservice extends CI_Controller{
  private $id ='';
  public function __construct() {
    parent::__construct();
        if(empty($this->session->userdata('user_id'))){
            $this->session->set_userdata('user_id', 9);
        }
        $this->load->model('Carservice_model','carservice');
        $this->load->library('my_control'); //登录控制
        $this->load->model('admin_model');
        $this->id = $this->session->userdata('user_id');
        //$this->load->model('usedcar_model');  //别名admin
      }
      //审核
      public function check($offset = 0){
        $where_arr = array("shenhe"=>0);
        $per_page = 10;
        $page_url = site_url('admin/carservice/check/');
        $total_rows = $this->carservice->get_total_rows($where_arr,'carservice');
        $offset_uri_segment = 5;
        $this->load->library('myclass');
        $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);
        $data['news'] = $this->carservice->get_news_list($where_arr, $offset, $per_page);
        $this->load->view('admin/carservice/check.html',$data);

      }


      //管理
      public function manage($type = 'id', $offset = 0){
        $where_arr = array("shenhe"=>1);
        $per_page = 10;
        $page_url = site_url('admin/carservice/manage/'.$type);
        $total_rows = $this->carservice->get_total_rows($where_arr,'carservice');
        $offset_uri_segment = 5;
        $this->load->library('myclass');
        $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);
        $data['news'] = $this->carservice->get_news_list($where_arr, $offset, $per_page,$type);
        $this->load->view('admin/carservice/manage.html',$data);
      }
      //搜索新闻
      public function news_search($keywords){
          $data['keywords'] = urldecode($keywords);
          $data['news'] = $this->carservice->get_news_search($keywords);
          $data['link'] = '';
          $this->load->view('admin/carservice/manage.html', $data);
      }

      //check搜索
      public function check_search($keywords){
          $where_arr = array("shenhe"=>0);
          $data['keywords'] = urldecode($keywords);
          $data['news'] = $this->carservice->get_news_search($keywords,$where_arr);
          $data['link'] = '';
          $this->load->view('admin/carservice/check.html', $data);
      }

      //信息操作
      public function news_action($action, $id){
          $news = $this->carservice->get_news_list(array('id'=>$id),0,1);
          if(empty($news)){
              alert_msg('该信息已被删除！');
          }
          $new = $new[0];
          $status = false;
          if($action == 'delete'){  //删除
              $msg = '删除';
              if($this->db->delete('carservice', array('id'=>$id))){
                  $status = true;
              }
          }else if($action == 'yes'){
            $msg = '审核';
            if($this->db->update('carservice',array('shenhe'=>1), array('id'=>$id))){
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
