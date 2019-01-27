<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
*     司机招聘管理
*/
/**
 *
 */
 /**
  *
  */
 class Driverhire  extends CI_Controller
 {

   function __construct($argument="")
   {
      parent::__construct();
      if(empty($this->session->userdata('user_id'))){
          $this->session->set_userdata('user_id', 9);
      }
      $this->load->model("driverhire_model","driver");
   }

   //司机招聘管理
   public function index($offset='')
   {
     $where_arr = array();
     $per_page = 10;
     $page_url = site_url('admin/driverhire/index');
     $total_rows = $this->db->count_all_results('driverhire_content');
     $offset_uri_segment = 4;
     $this->load->library('myclass');
     $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);
     $data['post'] = $this->driver->get_driver_list(array("check ="=>1),$offset, $per_page,"");

      $this->load->view("admin/driverhire/index.html",$data);
   }
   //司机招聘审核
   public function check($offset=0)
   {
     $where_arr = array();
     $per_page = 10;
     $page_url = site_url('admin/driverhire/check/');
     $total_rows = $this->db->count_all_results('driverhire_content');
     $offset_uri_segment = 4;
     $this->load->library('myclass');
     $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);
     $data['post'] = $this->driver->get_driver_list(array("check !="=>1),$offset, $per_page,"");
      $this->load->view("admin/driverhire/check.html" ,$data);
   }
   //过审核
   public function pass($id='')
   {
      if(is_numeric($id) ) {
        $this->db->update("driverhire_content",array("check" => 1) , array("id"=>$id)  )  ;
        alert_msg("" , "onlyback" ,"");
      }else {
        alert_msg("失败");
      }
   }
   //删除
   public function delete($value='')
   {
      $this->db->delete("driverhire_content",array("id"=>$value));
      alert_msg("删除成功");
   }
   //详细信息

 }
