<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Partssell extends CI_Controller{
  private $id ='';
  public function __construct() {
    parent::__construct();
        if(empty($this->session->userdata('user_id'))){
            $this->session->set_userdata('user_id', 9);
        }
        $this->id = $this->session->userdata('user_id');
        $this->load->library('my_control'); //登录控制
        $this->load->model('admin_model');
        $this->load->model('Partssell_model');
      }

  // 发布货物
  public function publish(){
    if(empty($this->input->post())){
      $this->load->view('admin/partssell/publish.html');
    }else{
      $authcode = $this->input->post("authcode");
      if($authcode  != $this->session->userdata('partssell_authcode')){
          get_json(400, '验证码输入错误');
          return;
      }
      $data = array(
      'name' => $this->input->post('name'),
      'money' => $this->input->post('money'),
      'content' => $this->input->post('content'),
      'detail' => $this->input->post('detail'),
      'class' => $this->input->post('class'),
      'img0' =>$this->input->post('img0'),
      'img1' =>$this->input->post('img1'),
      'img2' =>$this->input->post('img2'),
      'uptime' =>time()
    );
      if($this->db->insert('parts-sell_source', $data)){
          get_json(200, '发布成功！');
      }else{
          get_json(400, '发布失败，请重试');
      }
    }
  }
  //验证码
  public function authcode(){
      $this->load->library('myclass');
      $this->myclass->authcode('partssell_authcode');
  }

  //上传图片
  public function uploadImage(){
    $base64_image_content = $this->input->post('image', false);
    //echo $base64_image_content;
    //匹配出图片的格式
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
        $type = strtolower($result[2]);
        $allowed_type = array('jpg', 'jpeg', 'png', 'bmp');
        if (!in_array($type, $allowed_type)) {
            get_json(400, '只允许上传jpg, jpeg, png, bmp格式的图片哟！');
        }
        $new_file = "uploads/PartssellImg/".$this->id. "/" . date('Ymd', time()) . "/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0777, true);
        }
        $new_file = $new_file . md5(time() . mt_rand(1000, 9999)) . ".{$type}";
        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
            //echo $new_file;
            get_json(200, '图片上传成功', array('path' => '/' . $new_file));
            return;
        } else {
            get_json(400, '图片上传失败，请检查您的网络！');return;
        }
    }else{
        get_json(400, '图片上传失败');return;
    }
  }


  //货物管理
  public function manage($type = 'id',$offset = 0){
    $type = urldecode($type);
    $where_arr = array();
    $per_page = 10;
    $page_url = site_url('admin/partssell/manage/'.$type);
    $total_rows = $this->Partssell_model->get_total_rows(array(), 'parts-sell_source');
    $offset_uri_segment = 5;
    $this->load->library('myclass');
    $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);
    $data['list'] = $this->Partssell_model->get_list($where_arr, $offset, $per_page,$type);
    $this->load->view('admin/partssell/manage.html', $data);
  }

  //筛选
  public function type_list($type='',$data_type='',$offset = 0){
    $type = urldecode($type);
    $data_type = urldecode($data_type);
    $where_arr = array($type=>$data_type);
    $per_page = 10;
    $page_url = site_url('admin/partssell/type_list/'.$type.'/'.$data_type);
    $total_rows = $this->Partssell_model->get_total_rows($where_arr, 'parts-sell_source');
    $offset_uri_segment = 5;
    $this->load->library('myclass');
    $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);
    $data['list'] = $this->Partssell_model->get_list($where_arr, $offset, $per_page);
    $this->load->view('admin/partssell/manage.html', $data);

  }

  //货物搜索
  public function parts_search($keywords, $offset = 0){
      $data['keywords'] = urldecode($keywords);;
      $data['list'] = $this->Partssell_model->get_search_list($data['keywords'], $offset, 10);

      $data['link'] = '';
      $this->load->view('admin/partssell/manage.html', $data);
  }

  //货物信息操作
  public function parts_action($action, $id){
      $car = $this->admin_model->get_source_info(array('id'=>$id));
      if(empty($car)){ //判断是否存在
          alert_msg('该信息已被删除！');
      }
      $car = $car[0];
      $status = false;
      if($action == 'delete'){  //删除
          $msg = '删除';
          if($this->db->delete('parts-sell_source', array('id'=>$id))){
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
