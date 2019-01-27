<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class News extends CI_Controller
{
  private $id ='';
  public function __construct() {
		parent::__construct();
        if(empty($this->session->userdata('user_id'))){
            $this->session->set_userdata('user_id', 9);
        }
        $this->load->model('news_model','news');
        $this->load->library('my_control'); //登录控制
        $this->load->model('admin_model');
        $this->id = $this->session->userdata('user_id');
        //$this->load->model('usedcar_model');  //别名admin
	}
  //发布新闻
  public function publish(){
    if(empty($this->input->post())){
      $this->load->view('admin/news/publish_news.html');
    }else{
      $data = array(
        'title' => $title = $this->input->post('title'),
        'url' => $url = $this->input->post('url'),
        'img' => $img = $this->input->post('img'),
        'create_time' =>time()
      );

        if($this->db->insert('news', $data)){
            get_json(200, '发布成功！');
        }else{
            get_json(400, '发布失败，请重试');
        }
    }
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
        $new_file = "uploads/news/".$this->id. "/" . date('Ymd', time()) . "/";
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
    //新闻管理
    public function manage($type = 'id', $offset = 0){
      $where_arr = array();
      $per_page = 10;
      $page_url = site_url('admin/news/manage/'.$type);
      $total_rows = $this->db->count_all_results('news');
      $offset_uri_segment = 5;
      $this->load->library('myclass');
      $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);
      $data['news'] = $this->news->get_news_list($where_arr, $offset, $per_page,$type);
      $this->load->view('admin/news/news.html',$data);
    }
    //搜索新闻
    public function news_search($keywords){
        $data['keywords'] = urldecode($keywords);
        $data['news'] = $this->news->get_news_search($keywords);
        $data['link'] = '';
        $this->load->view('admin/news/news.html', $data);
    }

    //信息操作
    public function news_action($action, $id){
        $news = $this->news->get_news_list(array('id'=>$id),0,1);
        if(empty($news)){
            alert_msg('该信息已被删除！');
        }
        $new = $new[0];
        $status = false;
        if($action == 'delete'){  //删除
            $msg = '删除';
            if($this->db->delete('news', array('id'=>$id))){
                $status = true;
            }
        }
        if($status){
            alert_msg($msg.'成功');
        }else{
            alert_msg($msg.'失败，请稍后重试！');
        }
    }
//编辑新闻
      public function edit($id){
            $data = $this->news->get_news_list(array('id'=>$id),0,1)[0];
            $this->load->view('admin/news/edit.html',$data);
      }


}
