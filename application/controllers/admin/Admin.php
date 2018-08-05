<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {
	public function __construct() {
		parent::__construct();
		/*$id = $this->session->userdata('id');
		$admin_name = $this->session->userdata('admin_name');
		$identity = $this->session->userdata('identity');
		if (empty($id) || empty($admin_name) || empty($identity)) {
			header('location:' . site_url('admin/login'));
		}*/
		$this->load->model('admin_model');
	}
	/*
		后台首页
	*/
	public function index() {
		$this->load->view('admin/index.html');
	}

	/********************************** 论坛部分 ************************************/
	//论坛文章列表
    public function forum_list($type = 'all', $offset = 0)
    {
        $this->load->model('index_model');
        $where_arr = array();//检索条件
        $order_str = 'article.create_time DESC';//排序方式
        $type      = urldecode($type);
        $data['type'] = $type;
        $data['keywords'] = '';//无意义
        $data['notice']=$type == '公告'?true:false;
        if ($type != 'all') {
            $where_arr = array('type' => $type);
        }
        $status          = $this->index_model->get_user_article_list($where_arr, $order_str, $offset);
        $article         = $this->index_model->format_data($status);
        $data['article'] = $article;

        //分页相关参数
        $page_url           = site_url('admin/admin/forum_list/') . $type;
        $total_rows         = $this->db->where($where_arr)->count_all_results('article');
        $offset_uri_segment = 5;
        $per_page           = 10;
        $this->load->library('myclass');
        $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);

        //载入视图
        $this->load->view('admin/forum/forum_list.html', $data);
    }

    //论坛文章操作
    public function forum_action($action, $id){
        if($action == 'delete'){
            if($this->db->delete('article', array('article_id'=>$id))){
                alert_msg('删除成功');
            }else{
                alert_msg('删除失败');
            }
        }
    }

    public function forum_search($keywords){
        $keywords = urldecode($keywords);
        $article = $this->admin_model->get_forum_search($keywords);

        //格式化数据
        $this->load->model('index_model');
        $data['article'] = $this->index_model->format_data($article);

        //其他数据
        $data['type'] = $data['link'] = '';
        $data['keywords'] = $keywords;

        $this->load->view('admin/forum/forum_list.html', $data);
    }

    public function forum_add_notice(){
        if(empty($this->input->post('title'))){
            $this->load->view('admin/forum/forum_add_notice.html');
        }else{
            $data = array(
                'title' => $this->input->post('title'),
                'content' => $this->input->post('content', false),
                'type' => '公告',
                'create_time' => time(),
                'user_id' => $this->config->item('admin_user_id'),
            );

            if($this->db->insert('article', $data)){
                alert_msg('公告发布成功');
            }else{
                alert_msg('公告发布失败');
            }
        }
    }

	/*
	 * 司机群
	 * */
	//列表
	public function flock_list(){
	    $this->load->view('admin/flock_list.html');
    }
    //添加司机群
    public function flock_add(){
	    if(empty($this->input->post('title'))){ //如果没有post过来数据，为view页。
            $this->load->view('admin/flock_add.html');
        }else{ //否则执行添加群信息操作
            $data = array(
                'province'=>$this->input->post('province'),
                'city'=>$this->input->post('city'),
                'county'=>$this->input->post('county'),
                'title'=>$this->input->post('title'),
                'content'=>$this->input->post('content', false),
            );
            if($this->db->insert('flock', $data)){
                alert_msg('添加成功');
            }else{
                alert_msg('添加失败，请重试！');
            }
        }
    }
}
