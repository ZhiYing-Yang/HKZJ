<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class News extends CI_Controller {

  private $id ;
  public function __construct(Type $foo = null)
  {
    parent::__construct();
      $this->id = $this->session->userdata("news_user_id");
      $this->load->model('news_model','news');
      if(empty($this->id)){
          header('location:'.site_url('login/news_login'));
      }
  }
  //新闻首页
  public function index()
  {
    $data['tuijian'] = $this->news->get_news_list(array(),0,10,'zan desc');
    $data['redian'] = $this->news->get_news_list(array(),0,10,'collect desc');
    $data['zonghe'] = $this->news->get_all_news();
    $data['info'] = $this->news->get_user_info(array('id'=>$this->id))[0];
    //推荐
    for($i=0;$i<count($data['tuijian']);$i++){
      if($this->isChoiced($data['tuijian'][$i]['id'],'collect')){
        $data['tuijian'][$i]['collection'] = 1;
      }else{
        $data['tuijian'][$i]['collection'] = 0;
      }
      if($this->isChoiced($data['tuijian'][$i]['id'],'zan')){
        $data['tuijian'][$i]['dianzan'] = 1;
      }else{
        $data['tuijian'][$i]['dianzan'] = 0;
      }
    }
    //热点
    for($i=0;$i<count($data['redian']);$i++){
      if($this->isChoiced($data['redian'][$i]['id'],'collect')){
        $data['redian'][$i]['collection'] = 1;
      }else{
        $data['redian'][$i]['collection'] = 0;
      }
      if($this->isChoiced($data['redian'][$i]['id'],'zan')){
        $data['redian'][$i]['dianzan'] = 1;
      }else{
        $data['redian'][$i]['dianzan'] = 0;
      }
    }
    //综合
    for($i=0;$i<count($data['zonghe']);$i++){
      if($this->isChoiced($data['zonghe'][$i]['id'],'collect')){
        $data['zonghe'][$i]['collection'] = 1;
      }else{
        $data['zonghe'][$i]['collection'] = 0;
      }
      if($this->isChoiced($data['zonghe'][$i]['id'],'zan')){
        $data['zonghe'][$i]['dianzan'] = 1;
      }else{
        $data['zonghe'][$i]['dianzan'] = 0;
      }
    }
    $this->load->view("news/index.html",$data);
  }

  //文章操作
  public function action($pid,$action){
    $count = $this->news->get_news_list(array('id'=>$pid),0,1)[0][$action];
    $collect = $this->news->get_user_info(array('id'=>$this->id))[0][$action];
    if(empty($collect)){
        $str = $pid;
    }else{
        $str = $collect.'-$-'.$pid;
    }
    if($this->db->update('news_user', array($action=>$str), array('id'=>$this->id))){
      if($this->db->update('news', array($action=>$count+1), array('id'=>$pid))){
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
      $count = $this->news->get_news_list(array('id'=>$pid),0,1)[0][$action];
      $collect = $this->news->get_user_info(array('id'=>$this->id))[0][$action];
      $collect = str_replace("-".$pid."-$","",$collect);
      $collect = str_replace($pid."-$-","",$collect);
      $collect = str_replace("-$-".$pid,"",$collect);
      $collect = str_replace($pid,"",$collect);
      // $collect = str_replace("-".$pid,"",$collect);
      if($this->db->update('news_user', array($action=>$collect), array('id'=>$this->id))){
        if($this->db->update('news', array($action=>$count-1), array('id'=>$pid))){
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
      $see_user = $this->news->get_user_info(array('id'=>$this->id))[0]; //获取浏览者信息
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

  //收藏新闻页面
  public function collection($offset = 0)
  {
    $user = $this->news->get_user_info(array('id'=>$this->id))[0];
    if(empty($user['collect'])){
        $data['collect'] = array();
    }else{
        $where_in = explode('-$-', $user['collect']);
        $data['collect'] = $this->news->get_collect_list($where_in, $offset, 10);
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
    $this->load->view("news/my_collect.html",$data);
  }
//搜索
public function search($keyword,$offset=0){
  $data['search'] = $this->news->get_search_list($keyword,$offset,10);
  for($i=0;$i<count($data['search']);$i++){
    if($this->isChoiced($data['search'][$i]['id'],'collect')){
      $data['search'][$i]['collection'] = 1;
    }else{
      $data['search'][$i]['collection'] = 0;
    }
    if($this->isChoiced($data['search'][$i]['id'],'zan')){
      $data['search'][$i]['dianzan'] = 1;
    }else{
      $data['search'][$i]['dianzan'] = 0;
    }
  }
  $this->load->view("news/search.html",$data);
}
}
