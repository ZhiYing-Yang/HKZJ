<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Advisory extends CI_Controller {

  private $id ;
  public function __construct(Type $foo = null)
  {
    parent::__construct();
      $this->id = $this->session->userdata("advisory_user_id");
      $this->load->model('advisory_model','advisory');
      if(empty($this->id)){
          header('location:'.site_url('login/advisory_login'));
      }
  }
  //首页
  public function index()
  {
    $data['info'] = $this->advisory->get_news_list(array(),0,10);
    //推荐
    for($i=0;$i<count($data['info']);$i++){
      if($this->isChoiced($data['info'][$i]['id'],'collect')){
        $data['info'][$i]['collection'] = 1;
      }else{
        $data['info'][$i]['collection'] = 0;
      }
      if($this->isChoiced($data['info'][$i]['id'],'zan')){
        $data['info'][$i]['dianzan'] = 1;
      }else{
        $data['info'][$i]['dianzan'] = 0;
      }
    }
    $this->load->view("advisory/index.html",$data);
  }

  //文章操作
  public function action($pid,$action){
    $count = $this->advisory->get_news_list(array('id'=>$pid),0,1)[0][$action];
    $collect = $this->advisory->get_user_info(array('id'=>$this->id))[0][$action];
    if(empty($collect)){
        $str = $pid;
    }else{
        $str = $collect.'-$-'.$pid;
    }
    if($this->db->update('advisory_user', array($action=>$str), array('id'=>$this->id))){
      if($this->db->update('advisory', array($action=>$count+1), array('id'=>$pid))){
        get_json(200,'操作成功！');
      }else{
        get_json(400,'操作失败！');
      }
    }else{
        get_json(400,'操作失败！');
    }
  }

  //取消操作
  public function noAction($pid,$action){
    $count = $this->advisory->get_news_list(array('id'=>$pid),0,1)[0][$action];
    $collect = $this->advisory->get_user_info(array('id'=>$this->id))[0][$action];
    $collect = str_replace("-".$pid."-$","",$collect);
    $collect = str_replace($pid."-$-","",$collect);
    $collect = str_replace("-$-".$pid,"",$collect);
    $collect = str_replace($pid,"",$collect);
    // $collect = str_replace("-".$pid,"",$collect);
    if($this->db->update('advisory_user', array($action=>$collect), array('id'=>$this->id))){
      if($this->db->update('advisory', array($action=>$count-1), array('id'=>$pid))){
        get_json(200,'操作成功！');
      }else{
        get_json(400,'操作失败！');
      }
    }else{
        get_json(400,'操作失败！');
    }

  }

  //判断文章状态（点赞 收藏
  public function isChoiced($pid,$type){
    $see_user = $this->advisory->get_user_info(array('id'=>$this->id))[0]; //获取浏览者信息
    $collect = $see_user[$type];
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

  //收藏页面
  public function collection($offset = 0)
  {
    $user = $this->advisory->get_user_info(array('id'=>$this->id))[0];
    if(empty($user['collect'])){
        $data['collect'] = array();
    }else{
        $where_in = explode('-$-', $user['collect']);
        $data['collect'] = $this->advisory->get_collect_list($where_in, $offset, 10);
    }
    for($i=0;$i<count($data['collect']);$i++){
      if($this->isChoiced($data['collect'][$i]['id'],'collect')){
        $data['collect'][$i]['collection'] = 1;
      }else{
        $data['collect'][$i]['collection'] = 0;
      }
      if($this->isChoiced($data['collect'][$i]['id'],'zan')){
        $data['collect'][$i]['dianzan'] = 1;
      }else{
        $data['collect'][$i]['dianzan'] = 0;
      }
    }
    $this->load->view("advisory/my_collect.html",$data);
  }

  //搜索
  public function search($keyword,$offset=0){
    $data['info'] = $this->advisory->get_search_list($keyword,$offset,10);
    for($i=0;$i<count($data['info']);$i++){
      if($this->isChoiced($data['info'][$i]['id'],'collect')){
        $data['info'][$i]['collection'] = 1;
      }else{
        $data['info'][$i]['collection'] = 0;
      }
      if($this->isChoiced($data['info'][$i]['id'],'zan')){
        $data['info'][$i]['dianzan'] = 1;
      }else{
        $data['info'][$i]['dianzan'] = 0;
      }
    }
    $this->load->view("advisory/index.html",$data);
  }
}
