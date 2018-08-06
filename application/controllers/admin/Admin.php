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

        if ($type != 'all') { //根据单个类型获取帖子
            $where_arr = array('type' => $type);
            $where_in = array($type);
        }else{  //获取论坛帖子
            $where_arr = '论坛+求助';
            $where_in = $this->config->item('article_type');
        }
        $status          = $this->index_model->get_user_article_list($where_arr, $order_str, $offset);
        $article         = $this->index_model->format_data($status);
        $data['article'] = $article;

        //分页相关参数
        $page_url           = site_url('admin/admin/forum_list/') . $type;
        $total_rows         = $this->db->where_in('type', $where_in)->count_all_results('article');
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

    /********************************** 论坛部分 END************************************/

    /********************************** 司机群部分 Begin************************************/
	/*
	 * 司机群
	 * */
	//列表
	public function flock_list($offset = 0){

        $where_arr = array();
	    if(empty($this->input->post('keywords'))){ //不是搜索出来的结果
            $this->load->model('index_model');
            $data['flock'] = $this->index_model->get_flock_list($where_arr, $offset, 10);

            //分页相关参数
            $page_url           = site_url('admin/admin/flock_list');
            $total_rows         = $this->db->where($where_arr)->count_all_results('flock');
            $offset_uri_segment = 4;
            $per_page           = 10;
            $this->load->library('myclass');
            $data['link'] = $this->myclass->fenye($page_url, $total_rows, $offset_uri_segment, $per_page);
        }else{ //关键字不为空 为搜搜出来的结果

	        $keywords = $this->input->post('keywords');
	        $status = $this->admin_model->get_flock_search($keywords, $where_arr);

	        $data['flock'] = $status;
	        $data['keywords'] = $keywords;
	        $data['link'] = '';
        }
        $this->load->view('admin/flock/flock_list.html', $data);

    }
    //添加司机群
    public function flock_add(){
	    if(empty($this->input->post('title'))){ //如果没有post过来数据，为view页。
            $this->load->view('admin/flock/flock_add.html');
        }else{ //否则执行添加群信息操作
            $data = array(
                'province'=>$this->input->post('province'),
                'city'=>$this->input->post('city'),
                'county'=>$this->input->post('county'),
                'title'=>$this->input->post('title'),
                'content'=>$this->input->post('content', false),
                'create_time'=>time(),
            );
            if($this->db->insert('flock', $data)){
                alert_msg('添加成功');
            }else{
                alert_msg('添加失败，请重试！');
            }
        }
    }

    //编辑修改司机群
    public function flock_edit($id){
        if(empty($this->input->post('title'))){ //司机群编辑页
            $this->load->model('index_model');
            $status = $this->index_model->get_flock(array('id'=>$id));
            if(empty($status)){
                alert_msg('该群信息不存在！');
            }else{
                $data = $status[0];
                $this->load->view('admin/flock/flock_add.html', $data);
            }
        }else{ //执行对司机群的修改操作
            $data = array(
                'province'=>$this->input->post('province'),
                'city'=>$this->input->post('city'),
                'county'=>$this->input->post('county'),
                'title'=>$this->input->post('title'),
                'content'=>$this->input->post('content', false),
            );
            if($this->db->update('flock', $data, array('id'=>$id))){
                alert_msg('修改成功');
            }else{
                alert_msg('修改失败');
            }
        }
    }

    public function flock_action($action, $id){
        if($action == 'delete'){
            if($this->db->delete('flock', array('id'=>$id))){
                alert_msg('删除成功！');
            }else{
                alert_msg('删除失败，请稍后重试！');
            }
        }
    }
}
