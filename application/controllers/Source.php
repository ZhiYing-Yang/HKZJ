<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * 二手车交易平台
 * User: Administrator
 * Date: 2018/8/13
 * Time: 10:48
 */
class Source extends CI_Controller {
    private $id =  '';// 本模块用户id
    public function __construct()
    {
        parent::__construct();
        $this->id = $this->session->userdata('source_user_id');
        if(empty($this->id)){
            header('location:'.site_url('login/source_login'));
        }
        $this->load->model("source_model","source");
        $this->load->helper('date');
    }

    /**
     * 货源信息主界面
     */
    public function index($offset = 0,$data_type = ''){
      $data['list'] = $this->source->get_list(array() , $offset);

      if($data_type == 'json'){
          get_json(200, '获取成功', $data['list']);
      }else{
          $data['active'] = '首页';
          $this->load->view('source/index.html', $data);
      }
      //$this->load->view("source/index.html");

    }

    /**
    * 搜索
    */
    public function search($offset = 0 ,$data_type = ''){
      $keywords = urldecode($this->input->get('keywords'));
      $data['list'] = $this->source->get_search_list($keywords, $offset, 10);

      if($data_type == 'json'){
          get_json(200, '获取成功', $data['list']);
      }else{
          $this->load->view('source/index.html', $data);
      }
      //$this->load->view("source/index.html");

    }

    /**
     * 发布车辆详细信息
     */
    public function carDetails($id){
      $info = $this->source->get_list(array('id'=>$id), 0, 1);
      if(empty($info)){
          $data['str'] = '该信息已被删除！';
          $this->load->view('source/not_found.html', $data);
          return;
      }
      $see_user = $this->source->get_user_info(array('id'=>$this->id))[0]; //获取浏览者信息
      $data['id'] = $id;
      $data['collect'] = in_array($id, explode('-$-', $see_user['collect']))?1:0;//是否被收藏
      $data['info'] = $info[0];//车辆信息
      $data['departure'] = $data['info']['departure']; //出发地
      $data['destination'] = $data['info']['destination']; //目的地
      $data['path'] = $data['info']['path'];//承包路线
      $data['length'] = $data['info']['length'];//车厂
      $data['model'] = $data['info']['model'];//用车类型
      $data['weight'] = $data['info']['weight'];//载重
      $data['uptime'] = pTime($data['info']['uptime']);//发布时间
      $data['other'] = $data['info']['other'];//备注
      $data['username'] = $this->source->get_user_info(array(
        'id' => $data['info']['user_id']))[0]['nickname'];
      $data['user'] = $this->source->get_user_info(array('id'=>$data['info']['user_id']))[0];  //获取卖车用户信息
      $this->load->view("source/car_details.html",$data);
    }

    /**
     * 发布货物详细信息
     */
    public function goodsDetails($id){
      $info = $this->source->get_list(array('id'=>$id), 0, 1);
      if(empty($info)){
          $data['str'] = '该信息已被删除！';
          $this->load->view('source/not_found.html', $data);
          return;
      }
      $see_user = $this->source->get_user_info(array('id'=>$this->id))[0]; //获取浏览者信息
      $data['id'] = $id;
      $data['collect'] = in_array($id, explode('-$-', $see_user['collect']))?1:0;//是否被收藏
      $data['info'] = $info[0];//车辆信息
      $data['departure'] = $data['info']['departure']; //出发地
      $data['destination'] = $data['info']['destination']; //目的地
      $data['weight'] = $data['info']['weight'];//载重
      $data['uptime'] = pTime($data['info']['uptime']);//发布时间
      $data['other'] = $data['info']['other'];//备注
      $data['cartype'] = $data['info']['cartype'];//车型
      $data['goodstype'] = $data['info']['goodstype'];//货物类型
      $data['money'] = $data['info']['money'];//运费金额
      $data['time'] = $data['info']['time'];//装车时间
      $data['username'] = $this->source->get_user_info(array(
        'id' => $data['info']['user_id']))[0]['nickname'];
      $data['user'] = $this->source->get_user_info(array('id'=>$data['info']['user_id']))[0];  //获取卖车用户信息
      $this->load->view("source/goods_details.html",$data);
    }

    /**
     *货源信息界面
     */
    public function source($data_type=''){
      if(empty($this->input->post())){
        $data['list'] = $this->source->get_list(array(
          'isgood'=>1
        ), 0, 10);

        if($data_type == 'json'){
            get_json(200, '获取成功', $data['list']);
        }else{
            $data['active'] = '首页';
            $this->load->view('source/source.html', $data);
        }
      }else{
        $choice['isgood'] = 1;
        if(!empty($this->input->post('departure'))){//出发地
          $choice['departure'] = $this->input->post('departure');
        }
        if(!empty($this->input->post('destination'))){//目的地
          $choice['destination'] = $this->input->post('destination');
        }
        if(!empty($this->input->post('radio4'))){//货物类型
          $choice['goodstype'] = $this->input->post('radio4');
        }
        if(!empty($this->input->post('radio3'))){//车型
          $choice['model'] = $this->input->post('radio3');
        }
        if(!empty($this->input->post('radio1'))){//用车类型
          $choice['cartype'] = $this->input->post('radio1');
        }
        $data['list'] = $this->source->get_list($choice, 0, 10);
        $this->load->view('source/source.html', $data);
      }

    }

    /**
     *车源信息界面
     */
    public function car($data_type=''){
      if(empty($this->input->post())){
        $data['list'] = $this->source->get_list(array(
          'isgood'=>0
        ), 0, 10);

        if($data_type == 'json'){
            get_json(200, '获取成功', $data['list']);
        }else{
            $data['active'] = '首页';
            $this->load->view('source/car.html', $data);
        }
      }else{
        $choice = array();
        if(!empty($this->input->post('departure'))){//出发地
          $choice['departure'] = $this->input->post('departure');
        }
        if(!empty($this->input->post('destination'))){//目的地
          $choice['destination'] = $this->input->post('destination');
        }
        if(!empty($this->input->post('radio2'))){//车长
          $choice['length'] = $this->input->post('radio2');
        }
        if(!empty($this->input->post('radio3'))){//车型
          $choice['model'] = $this->input->post('radio3');
        }
        $choice['isgood'] = 0;
        $data['list'] = $this->source->get_list($choice, 0, 10);
        $this->load->view('source/car.html', $data);
      }


    }

    //收藏信息界面
    public function collect($id){
        $collect = $this->source->get_user_info(array('id'=>$this->id))[0]['collect'];
        if(empty($collect)){
            $str = $id;
        }else{
            $str = $collect.'-$-'.$id;
        }
        if($this->db->update('source_user', array('collect'=>$str), array('id'=>$this->id))){
            get_json(200,'收藏成功！');
        }else{
            get_json(400,'收藏失败！');
        }
    }

    /**
     *发布货源界面
     */
    public function PSource(){
        $user = $this->source->get_user_info(array('id'=>$this->id));
        //如果用户信息不完善 不能发布卖车信息 跳转完善个人信息页
        if(!isset($user[0]) || empty($user[0]['address'])){
            $data['info'] = 'empty';
            $this->load->view('source/PuGoodSource.html', $data);
            return;
        }
        if(empty($this->input->post())){
            $this->load->view('source/PuGoodSource.html');
        }else{
                $authcode = $this->input->post('authcode');
                if($authcode  != $this->session->userdata('source_authcode')){
                    get_json(400, '验证码输入错误');
                    return;
                }
                $data = array(
                    'user_id' => $this->id, //用户id
                    'departure'=>$this->input->post('departure'), //出发地
                    'destination'=>$this->input->post('destination'), //目的地
                    'isgood' => 1,//是否为货物 1为货物，0为车
                    'goodstype'=>$this->input->post('radio4'),   //货物类型
                    'cartype'=>$this->input->post('radio1'), //用车类型
                    'money'=>$this->input->post('money'),   //运费金额
                    'time'=>$this->input->post('time'), //装车时间
                    'length'=>$this->input->post('radio2'), //车长
                    'model'=>$this->input->post('radio3'),   //车型
                    'weight'=>$this->input->post('weight'), //货物重量体积
                    'dun' =>$this->input->post('radio5'),//1表示计数单位为吨，0为方
                    'other'=>$this->input->post('other'), //其他补充
                    'contact'=>$this->input->post('contact'), //行驶证登记日期
                    'tel'=>$this->input->post('tel'), //联系电话
                    'uptime'=>time()   //信息发布时间
                );
                if($this->db->insert('resource', $data)){
                    get_json(200, '发布成功！');
                }else{
                    get_json(400, '发布失败，请重试');
                }

            }
        //$this->load->view("source/PuGoodSource.html");

    }

    /**
     *发布车源界面
     */
    public function PCar(){
      $user = $this->source->get_user_info(array('id'=>$this->id));
      //如果用户信息不完善 不能发布卖车信息 跳转完善个人信息页
      if(!isset($user[0]) || empty($user[0]['address'])){
          $data['info'] = 'empty';
          $this->load->view('source/PuCarSource.html', $data);
          return;
      }
      if(empty($this->input->post())){
          $this->load->view('source/PuCarSource.html');
      }else{
        $authcode = $this->input->post('authcode');
        if($authcode  != $this->session->userdata('source_authcode')){
            get_json(400, '验证码输入错误');
            return;
        }
              $data = array(
                  'user_id' => $this->id, //用户id
                  'departure'=>$this->input->post('departure'), //出发地
                  'destination'=>$this->input->post('destination'), //目的地
                  'isgood' => 0,//是否为货物 1为货物，0为车
                  'length'=>$this->input->post('radio2'), //车长
                  'model'=>$this->input->post('radio3'),   //车型
                  'weight'=>$this->input->post('weight'), //货物重量体积
                  'dun' =>$this->input->post('radio5'),//1表示计数单位为吨，0为方
                  'path' => $this->input->post('radio4'),//承包路线
                  'other'=>$this->input->post('other'), //其他补充
                  'contact'=>$this->input->post('contact'), //行驶证登记日期
                  'tel'=>$this->input->post('tel'), //联系电话
                  'uptime'=>time()   //信息发布时间
              );
              if($this->db->insert('resource', $data)){
                  get_json(200, '发布成功！');
              }else{
                  get_json(400, '发布失败，请重试');
              }

          }
        //$this->load->view("source/PuCarSource.html");

    }

    /**
     * 我的
     */
    public function mine(){
        $data['active'] = '我的';
        $data['user'] = $this->source->get_user_info(array('id'=>$this->id))[0];
        $this->load->view("source/mine.html",$data);
    }

    /**
     * 我的  编辑个人信息
     */
    public function editData(){
      if(empty($this->input->post('address'))){
          $data['user'] = $this->source->get_user_info(array('id'=>$this->id))[0];
          $this->load->view('source/edit_data.html', $data);
      }else{
          $data = array(
              'address'=>$this->input->post('address'),
              'realname'=>$this->input->post('realname'),
              'phone'=>$this->input->post('phone'),
              'wechat'=>$this->input->post('wechat'),
          );
          if($this->db->update('source_user', $data, array('id'=>$this->id))){
              get_json(200, '信息修改成功');
          }else{
              get_json(200, '信息修改失败，请稍后重试');
          }
        }
      }

    /**
     * 我的发布
     */
    public function myPublish($data_type=''){
        $data['list'] = $this->source->get_list(array('user_id'=>$this->id), 0, 10);

        if($data_type == 'json'){
            get_json(200, '获取成功', $data['list']);
        }else{
            $this->load->view('source/myPublish.html', $data);
        }
        //$this->load->view("source/myPublish.html");
    }

    /**
     * 我的收藏
     */
    public function myCollect($offset = 0, $type = ''){
      $user = $this->source->get_user_info(array('id'=>$this->id))[0];
      if(empty($user['collect'])){
          $data['collect'] = array();
      }else{
          $where_in = explode('-$-', $user['collect']);
          $data['collect'] = $this->source->get_collect_list($where_in, $offset, 10);
      }

      if($type == 'json'){
          get_json(200, '获取成功', $data['collect']);
      }else{
          $this->load->view('source/my_collect.html', $data);
      }
      //$this->load->view("source/my_collect.html");
    }

    public function authcode(){
        $this->load->library('myclass');
        $this->myclass->authcode('source_authcode');
    }

    public function test(){

        echo time();
    }
}
