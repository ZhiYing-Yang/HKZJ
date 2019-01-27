<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * 车辆维修
 * ApiKey : 5d493777514565298e14dde91c4be448
 * User: Administrator
 * Date: 2018/8/13
 * Time: 10:48
 */
class Carservice extends CI_Controller{
  private $id ="";

  public  function __construct(){
     parent::__construct();
     $this->id = $this->session->userdata('carservice_user_id');
     if(empty($this->id)){
         header('location:'.site_url('login/carservice_login'));
     }
     $this->load->model("Carservice_model",'carservice');
   }
   //首页
   public function index(){
     // if(!empty($this->input->get('position'))){
     //   $position = $this->input->get('position');
     //   $data['position'] = $position;
     // }
     $data['info'] = $this->carservice->get_all_service('create_time DESC',array('shenhe'=>'1'));
     $this->load->view("carservice/index.html",$data);
   }

   //details
   public function details($id){
      $data['info'] = $this->carservice->get_all_service('create_time DESC',array('id'=>$id))[0];
      $data["sid"] = $id;
      $this->load->view("carservice/details.html",$data);
   }

   //map
   public function map($id){
      $data['info'] = $this->carservice->get_all_service('create_time DESC',array('id'=>$id))[0];
      $a = explode(',',$data['info']['position2']);
      $data['lng'] = $a[0];
      $data['lat'] = $a[1];
      $this->load->view("carservice/map.html",$data);
   }

   //发布招聘视图
   public function dist($value='')
   {
     $this->load->view("carservice/dist.html");
   }

   public function distform()//发布招聘
   {
     $data = $_POST;
     $data["create_time"] = time();
     $data["user_id"] = $this->session->userdata("user_id");
     if($this->db->insert("carservice" , $data)){
       get_json(200,"提交成功了");
     }else{
       get_json(405 , "上传失败");
     };

   }










   //获得当前经纬
   public function getPosition($action){
     if($action=='get'){
       $data['msg']='get';
       $this->load->view("carservice/map.html",$data);
     }
   }
 }
