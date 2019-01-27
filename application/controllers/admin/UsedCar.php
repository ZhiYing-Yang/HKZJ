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
        if(empty($this->session->userdata('user_id'))){
            $this->session->set_userdata('user_id', 9);
        }
        $this->load->library('my_control'); //登录控制
        $this->load->model('admin_model');
        $this->load->model('usedcar_model');  //别名admin
	}

  //广告
  public function advertisement($offset='')
  {
    $data["adver"] = $this->db->get("advertise_usedcar")->result_array();

    $this->load->view("admin/usedcar/advertise.html" , $data);
  }
  //广告管理
  public function advertisement_manage($action , $id)
  {
    if($action=="add"){
      $this->load->view("admin/usedcar/adver_add.html") ;
      return ;
    }
    if($action=="edit"){
      $res = $this->db->get_where("advertise_usedcar" , array("id"=>$id))->row_array();
      $data["res"] = $res ;
      $this->load->view("admin/usedcar/adver_edit.html" , $data ) ;
      return ;
    }
    if($action=="doedit"){
      $url = $this->input->post("url");
      $content = $this->input->post("content");
      $img = $this->input->post("img");
      if($this->db->update("advertise_usedcar" , array("url"=>$url ,"content"=>$content , "img"=>$img ) , array("id"=>$id))){
        get_json(200 , " " , "");
      }
      return ;
    }
    if($action=="doadd"){
      $url = $this->input->post("url");
      $content = $this->input->post("content");
      $img = $this->input->post("img");
      $this->db->insert("advertise_usedcar" , array(
        "url"=>$url,
        "img"=>$img ,
        "content"=>$content ));
        get_json(200,"" , "");
      return ;
    }
    if($action=="delete"){
      if($this->db->delete("advertise_usedcar" , array("id"=>$id ))){
        get_json(200," " ,"");
      }
    }
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

    //用户具体信息
    public function user_info($id){
        $user = $this->usedcar_model->get_user_info(array('id'=>$id));
        if(empty($user)){
           alert_msg('用户不存在！');
        }

        $data['user'] = $user[0];
        $data['total_rows'] = $this->usedcar_model->get_total_rows(array('user_id'=>$id), 'used-car_sale');
        $apply = $this->usedcar_model->get_apply_list(array('user_id'=>$id), 0, 1);
        if(!empty($apply)){
            $data['apply'] = $apply[0];
        }

        $this->load->view('admin/usedcar/user_info.html', $data);
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


    //车辆列表
    public function car_list($key = '其他', $value = '其他', $offset = 0){
        $key = urldecode($key);
        $value = urldecode($value);

        $data['key'] = $key;
        $data['value'] = $value;


        $order_str = 'create_time DESC';
        $array = array();
        if($key == '排序'){
            switch ($value){
                case '最新上架':
                    $order_str = 'create_time DESC';
                    break;
                case '价格最低':
                    $order_str = 'whole_price ASC';
                    break;
                case '价格最高':
                    $order_str = 'whole_price DESC';
                    break;
                case '降价急售':
                    $order_str = 'whole_price ASC';
                    break;
            }
        }elseif ($key == '车型'){
            $array = array('car_type'=>$value);
        }elseif ($key == '价格'){
            $price = explode('到', $value);
            $array = array('whole_price <=' => (float)$price[1], 'whole_price >=' => (float)$price[0]);
        }elseif ($key == '排放'){
            $array = array('parameter0' => $value);
        }
        else{
            $array = array();
            $order_str = 'create_time DESC';
        }
        $this->load->model('usedcar_model');

        $per_page = 10;
        $data['car'] = $this->usedcar_model->get_sale_list($array, $offset, $per_page, $order_str);


        $page_url = site_url('admin/usedCar/car_list/'.$key.'/'.$value);
        $total_rows = $this->db->where($array)->count_all_results('used-car_sale');
        $offset_uri_segment = 6;
        $this->load->library('myclass');
        $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);

        $this->load->view('admin/usedcar/car_list.html', $data);
    }

    //车辆搜索
    public function car_search($keywords, $offset = 0){
        $data['keywords'] = urldecode($keywords);
        $this->load->model('usedcar_model');
        $data['car'] = $this->usedcar_model->get_search_list($data['keywords'], $offset, 10);

        $data['link'] = '';
        $this->load->view('admin/usedcar/car_list.html', $data);
    }

    //车辆操作
    public function car_action($action, $id){
        $car = $this->admin_model->get_car_info(array('id'=>$id));
        if(empty($car)){ //判断车辆是否存在
            alert_msg('该车辆信息已被删除！');
        }
        $car = $car[0];
        $status = false;
        if($action == 'delete'){  //删除
            $msg = '删除';
            if($this->db->delete('used-car_sale', array('id'=>$id))){
                $status = true;
            }
        }elseif($action == 'check'){ //审核
            $msg = '审核';
            $check_code = $car['status'] == 1?0:1;
            if($this->db->update('used-car_sale', array('status'=>$check_code), array('id'=>$id))){
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
