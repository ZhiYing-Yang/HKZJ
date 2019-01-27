<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *商家入驻控制器
 *
 */
class Merchantcheck extends CI_Controller {

  private $id ='';
  private $current_user ;
  public function __construct()
  {
      parent::__construct();
      //$this->session->set_userdata('used_car_user_id', 1); //测试用户
      $this->id = $this->session->userdata('user_id');
      if(empty($this->id)){
          $this->session->set_userdata(array('go_url' => site_url("Login/weChat_login")   ));
      }
    //  echo $this->id ; die;
      $this->current_user  = $this->db->get_where("user", array('user_id' => $this->id ))->result_array()[0] ; //拿到当前用户 doe;
      $this->load->model('merchant_model' , "merchant");
    //  var_dump($this->current_user) ; die;
  }
//首页处理
public function test($value='')
{
  $data = $this->db->select_sum("id")->from("merchant")->where(array("user_id"=>$this->id) )->get()->result_array() ;
  var_dump(count($data)) ; die;
}
  public function index(){
 //var_dump(  );  die;
    if ($this->current_user["is_merchant"]==1) {
      //这个人是商家

      $data["person"] =$this->current_user;
      $this->detail($this->merchant->get_merchant_id_by_userid($this->current_user["user_id"])["id"]);

    }else if( $this->current_user["is_merchant"]==0 && count($this->db->select_sum("id")->from("merchant")->where(array("user_id"=>$this->id) )->get()->result_array() )>=1  ){
      $this->load->view("merchantcheck/hasbeen.html");
    }else{
      // code...
      $this->load->view("merchantcheck/apply-for.html");
    }
  }
//个体商户
  public function personal(){
    $this->load->view("merchantcheck/apply-for-personal.html");
  }
//公司商户
  public function company()
  {
    $this->load->view("merchantcheck/apply-for-company.html");

  }
  public function detail($id='')
  {

    $data["merchant"] = $this->merchant->get_info_byID($id);
    //var_dump($data["merchant"]); die;
  //  echo $data["merchant"]["headphoto"]; die;

    $this->load->view("merchantcheck/detail.html",$data);
  }

//个人性质表单递交
//执行认证操作 提交表单处理后存放数据库
public function apply($type){
    $type = urldecode($type);
    $authcode = $this->input->post('authcode');
    if($authcode != $this->session->userdata('usedcar_authcode')){
        get_json(400, '验证码错误!');
        return;
    }
    $data = array(
        'user_id'   =>  $this->id,
        'realname'  =>  $this->input->post('realname'),     //真实姓名
        'address'   =>  $this->input->post('address'),      //所在地址
        'idcardno'   =>  $this->input->post('ID_card'),      //身份证号
        'phone'     =>  $this->input->post('phone'),        //卖车手机号
        'we_chat'   =>  $this->input->post('we_chat'),      //微信号
        'img0'      =>  $this->input->post('img0'),         //身份证正面
        'img1'      =>  $this->input->post('img1'),         //身份证背面
        'img2'      =>  $this->input->post('img2'),         //手持身份证
        'module'    =>  $this->input->post('module'),       //所属模块
        'type'      =>  $type, //认证类型
        'create_time'      =>  time(),
        "headphoto"=>$this->input->post("headphoto"),
        "merchant_name"=>$this->input->post("merchant_name")
    );


    //商家额外信息
  if($type == '商家'){
        $data['img3'] = $this->input->post('img3'); //营业执照
        $data['merchant_type'] = $this->input->post('merchant_type'); //商家类型
        $data['company_name'] = $this->input->post('company_name'); //公司名称
        $data['indate'] = $this->input->post('indate'); //有效期
        $data['registration_number'] = $this->input->post('registration_number'); //注册号
        $data['company_address'] = $this->input->post('company_address'); //公司地址
    }
    //if($this->db->update('used-car_user' , $data, array('id'=>$this->id))){
    if($this->db->insert('merchant' , $data)){
        get_json(200, '提交成功!');
    }else{
        get_json(200, '提交失败!');
    }
  }
  //商家排名
  public function merchant_rank($type=""){
    $this->load->view("merchant/index.html");
  }
  public function merchant_list(){

    $index = $this->input->post("index");
    $perpage = $this->input->post("perpage");
    $option = $this->input->post("option");
    $sql = $this->db->select("address , id , merchant_name ,headphoto ,type,realname")->from("merchant")->where("check =",1);
    if($option["queue"]=="time"){
      $result = $sql->limit($perpage, $index)->get()->result_array();
    }else {
      $result = $sql->limit($perpage, $index)->order_by("id asc")->get()->result_array();
//      $result=7;
    }


    get_json(200,"成功",json_encode($result));

  }
  public function get_merchant($limit )
  {
    get_json(200 , "成功获取", json_encode($this->db->get_where("user" ,  array('is_merchant' => 1 ) )->limit(3,0)->result_array())  );
  }

}
