<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * 司机招聘
 * User: Administrator
 * Date: 2018/8/13
 * Time: 10:48
 */
class Driverhire extends CI_Controller{

  private $id ="";

     public function __construct(){
      parent::__construct();
      if($this->id==""){

      }
    }
    //首页
    public function index(){
      $this->load->view("driverhire/index.html");
    }

    //发布招聘视图
    public function dist($value='')
    {
      $this->load->view("driverhire/dist.html");
    }
    public function distform()//发布招聘
    {
      $data = $_POST;
      unset($data["capt"]);
      $data["create_time"] = time();

      $data["user_id"] = $this->session->userdata("user_id");
      if($this->db->insert("driverhire_content" , $data)){
        get_json(200,"提交成功了");
      }else{
        get_json(405 , "上传失败");
      };

    }
    public function load($value='') //加载数据
    {
      $index = $this->input->post("index");
      $perpage = $this->input->post("perpage");
      $sql =  $this->db->select("*")->from("driverhire_content");
      if($this->input->post("option")){
        $keyword = $this->input->post("option")["keyword"];
        $sql->or_like(array("postname"=>$keyword , "address"=>$keyword));
      }

      $result = $sql->order_by("create_time desc")->limit($perpage,$index)->get()->result_array();
      get_json(200,"" ,json_encode($result));
    }
/**
    assad
  */
    //个人中心
    public function personal($value='')
    {
      $this->load->view("driverhire/personal.html");
      // code...
    }
    /**

    */
    public function detail($id)
    {
      $result = $this->db->get_where("driverhire_content",array("id"=>$id))->row_array();
      $data["post"] = $result;
      $this->load->view("driverhire/detail.html",$data);
    }
    
}
