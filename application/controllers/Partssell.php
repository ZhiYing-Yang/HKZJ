<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 配件销售
 * User: Noel
 * Date: 2018/8/13
 * Time: 10:48
 */
class Partssell extends CI_Controller{
  private $id = '';

  public function __construct()
  {
      parent::__construct();
      $this->id = $this->session->userdata('parts_sell_user_id');
      if(empty($this->id)){
          header('location:'.site_url('login/parts_sell_login'));
      }

      $this->load->model('partssell_model','parts_model');
  }
  //首页
  public function index(){
    $data['list'] = $this->parts_model->get_list(array(), 0, 2);
    for($i=0;$i<count($data['list']);$i++){
      if($this->isColleted($data['list'][$i]['id'])){
        $data['list'][$i]['collection'] = 1;
      }else{
        $data['list'][$i]['collection'] = 0;
      }
      if($this->isShoped($data['list'][$i]['id'])){
        $data['list'][$i]['shopcar'] = 1;
      }else{
        $data['list'][$i]['shopcar'] = 0;
      }
    }

    $this->load->view('partssell/index.html',$data);
  }

  //商品详情
  public function parts_detail($pid){
    $data['info'] = $this->parts_model->get_list(array('id'=>$pid),0,1);
      if($this->isColleted($data['info'][0]['id'])){
        $data['info'][0]['collection'] = 1;
      }else{
        $data['info'][0]['collection'] = 0;
      }
      if($this->isShoped($data['info'][0]['id'])){
        $data['info'][0]['shopcar'] = 1;
      }else{
        $data['info'][0]['shopcar'] = 0;
      }
    $this->load->view('partssell/parts_details.html',$data);
  }

  //柴滤
  public function chailv($offset = 0){
    $data['list'] = $this->parts_model->get_list(array('class'=>'柴滤'), $offset, $offset+10);
    $this->load->view('partssell/chailv.html',$data);
  }

  //机滤
  public function jilv($offset = 0){
    $data['list'] = $this->parts_model->get_list(array('class'=>'机滤'), $offset, $offset+10);
    $this->load->view('partssell/jilv.html',$data);
  }

  //空滤
  public function konglv($offset = 0){
    $data['list'] = $this->parts_model->get_list(array('class'=>'空滤'), $offset, $offset+10);
    $this->load->view('partssell/konglv.html',$data);
  }

  //机油
  public function jiyou($offset = 0){
    $data['list'] = $this->parts_model->get_list(array('class'=>'机油'), $offset, $offset+10);
    $this->load->view('partssell/jiyou.html',$data);
  }

  //配件
  public function peijian($offset = 0){
    $data['list'] = $this->parts_model->get_list(array('class'=>'配件'), $offset, $offset+10);
    $this->load->view('partssell/peijian.html',$data);
  }

  //内饰
  public function neishi($offset = 0){
    $data['list'] = $this->parts_model->get_list(array('class'=>'内饰'), $offset, $offset+10);
    $this->load->view('partssell/neishi.html',$data);
  }

  //外饰
  public function waishi($offset = 0){
    $data['list'] = $this->parts_model->get_list(array('class'=>'外饰'), $offset, $offset+10);
    $this->load->view('partssell/waishi.html',$data);
  }

  //安全
  public function anquan($offset = 0){
    $data['list'] = $this->parts_model->get_list(array('class'=>'安全'), $offset, $offset+10);
    $this->load->view('partssell/anquan.html',$data);
  }

  //加装
  public function jiazhuang($offset = 0){
    $data['list'] = $this->parts_model->get_list(array('class'=>'加装'), $offset, $offset+10);
    $this->load->view('partssell/jiazhuang.html',$data);
  }

  //全部
  public function quanbu($offset = 0){
    $data['list'] = $this->parts_model->get_list(array(), $offset, $offset+10);
    $this->load->view('partssell/quanbu.html',$data);
  }

  //加入收藏
  public function collect($pid){
    $collect = $this->parts_model->get_user_info(array('id'=>$this->id))[0]['collection'];
    if(empty($collect)){
        $str = $pid;
    }else{
        $str = $collect.'-$-'.$pid;
    }
    if($this->db->update('parts-sell_user', array('collection'=>$str), array('id'=>$this->id))){
        get_json(200,'收藏成功！');
    }else{
        get_json(400,'收藏失败！');
    }
  }

  //取消收藏
  public function noCollect($pid){
    $collect = $this->parts_model->get_user_info(array('id'=>$this->id))[0]['collection'];
    $collect = str_replace("-".$pid."-$","",$collect);
    $collect = str_replace($pid."-$-","",$collect);
    $collect = str_replace("-$-".$pid,"",$collect);
    $collect = str_replace($pid,"",$collect);
    // $collect = str_replace("-".$pid,"",$collect);
    if($this->db->update('parts-sell_user', array('collection'=>$collect), array('id'=>$this->id))){
        get_json(200,'取消收藏成功！');
    }else{
        get_json(400,'取消收藏失败！');
    }

  }
  //判断是否收藏
  public function isColleted($pid){
    $see_user = $this->parts_model->get_user_info(array('id'=>$this->id))[0]; //获取浏览者信息
    $collect = $see_user['collection'];
    if(empty($collect)){
        return 0;
    }
    $collection = explode('-$-', $collect);
    if(in_array($pid,$collection)){
      return 1;
    }else{
      return 0;
    }

  }

  //加入购物车
  public function shopcar($pid){
    $shop = $this->parts_model->get_user_info(array('id'=>$this->id))[0]['shopcar'];
    if(empty($shop)){
        $str = $pid;
    }else{
        $str = $shop.'-$-'.$pid;
    }
    if($this->db->update('parts-sell_user', array('shopcar'=>$str), array('id'=>$this->id))){
        get_json(200,'加入购物车成功！');
    }else{
        get_json(400,'加入购物车失败！');
    }
  }

  //取消购物车
  public function noshop($pid){
    $shop = $this->parts_model->get_user_info(array('id'=>$this->id))[0]['shopcar'];
    $shop = str_replace("-".$pid."-$","",$shop);
    $shop = str_replace($pid."-$-","",$shop);
    $shop = str_replace("-$-".$pid,"",$shop);
    $shop = str_replace($pid,"",$shop);
    // $shop = str_replace($pid."-","",$shop);
    if($this->db->update('parts-sell_user', array('shopcar'=>$shop), array('id'=>$this->id))){
        get_json(200,'取消购物成功！');
    }else{
        get_json(400,'取消购物失败！');
    }

  }
  //判断是否在购物车
  public function isShoped($pid){
    $see_user = $this->parts_model->get_user_info(array('id'=>$this->id))[0]; //获取浏览者信息
    $shop = $see_user['shopcar'];
    if(empty($shop)){
        return 0;
    }
    $shoption = explode('-$-', $shop);
    if(in_array($pid,$shoption)){
      return 1;
    }else{
      return 0;
    }

  }

  //购物车
  public function myShopCar(){
    $data['user'] = $this->parts_model->get_user_info(array('id'=>$this->id))[0];
    $id = explode('-$-',$data['user']['shopcar']);
    $data['info'] = array();
    if($id[0]==""){
      $this->load->view('partssell/shopcar.html',$data);
    }else{
        for($i=0;$i<count($id);$i++){
          array_push($data['info'],$this->parts_model->get_where('id',$id[$i])[0]);
        }
        $this->load->view('partssell/shopcar.html',$data);
    }
  }




  //个人界面
  public function mine(){
    $data = $this->parts_model->get_user_info(array('id'=>$this->id))[0];
    #var_dump($user);
    $this->load->view('partssell/mine.html',$data);
  }

  //编辑收货地址
  public function edit_address(){
    $id = $this->id;

    if(empty($this->input->post())){
      $data = $this->parts_model->get_user_info(array('id'=>$id))[0];
      $this->load->view('partssell/edit_address.html',$data);
    }else{
      $data = array(
        'realname' => $this->input->post('name'),
        'phone'=>$this->input->post('phone'),
        'address'=>$this->input->post('address'),
        'addressinfo'=>$this->input->post('addressinfo')
      );
      if($this->db->update('parts-sell_user',$data,array('id'=>$id))){
        get_json(200,'编辑成功!');
      }else{
        get_json(400,'编辑失败，请重试');
      }
    }

  }

  //收藏
  public function my_collect(){
    $data['user'] = $this->parts_model->get_user_info(array('id'=>$this->id))[0];
    $id = explode('-$-',$data['user']['collection']);
    $data['info'] = array();
    if($id[0]==""){
      $this->load->view('partssell/my_collect.html',$data);
    }else{
        for($i=0;$i<count($id);$i++){
          array_push($data['info'],$this->parts_model->get_where('id',$id[$i])[0]);
        }
        $this->load->view('partssell/my_collect.html',$data);
    }
  }

  //足迹
  public function my_footprint(){
    $data['list'] = $this->parts_model->get_list(array(), 0, 2);
    for($i=0;$i<count($data['list']);$i++){
      if($this->isColleted($data['list'][$i]['id'])){
        $data['list'][$i]['collection'] = 1;
      }else{
        $data['list'][$i]['collection'] = 0;
      }
      if($this->isShoped($data['list'][$i]['id'])){
        $data['list'][$i]['shopcar'] = 1;
      }else{
        $data['list'][$i]['shopcar'] = 0;
      }
    }
    $this->load->view('partssell/my_footprint.html',$data);
  }
}
