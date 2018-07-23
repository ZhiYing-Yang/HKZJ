<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('index_model');
		$this->session->set_userdata('id', 1);
	}

	//论坛首页
	public function index($type = '', $offset = 0) {
		$type = urldecode($type);
		//文章类型
		$where_arr = array();
		if (in_array($type, $this->config->item('article_type'))) {
			$where_arr = array('type' => $type);
		}
		$order_str = 'article.create_time DESC, praise DESC'; //排序规则
		//关联查询user表和article表 获取文章数据和想关发布者信息
		$status = $this->index_model->get_user_article_list($where_arr, $order_str, $offset);
		//格式化处理文章数据，去掉html标签，匹配出文章图片
		$data['article'] = $this->index_model->format_data($status);
		$this->load->view('index/index.html', $data);
	}

	//查看文章页
	public function see_article($id) {
		$article = $this->index_model->get_article(array('article_id' => $id));
		if (empty($article)) {
			$data['title'] = '文章不存在';
			$data['str'] = '<h1 style="color:#fff;">该文章已被删除</h1>';
			$this->load->view('404/404.html', $data);
			return;
		}
		//文章存在， 获取文章信息
		$data['article'] = $article[0];

		//阅读量加1
		$sql = 'UPDATE article SET `read` = `read` + 1 WHERE article_id =' . $article[0]['article_id'];
		$this->db->query($sql);

		//该篇文章评论量
		$data['article']['comment_total'] = $this->db->where(array('article_id' => $article[0]['article_id']))->count_all_results('comment');

		//作者信息
		$data['user'] = $this->index_model->get_user(array('user_id' => $article[0]['user_id']))[0];

		//评论列表
		$data['comment'] = $this->index_model->get_user_comment_list(array('article_id' => $id), 'praise DESC, create_time DESC', 0);
		$this->load->view('index/article.html', $data);
	}

	//添加文章页
	public function add_article($action = 'see') {
		//默认添加文章页面
		if ($action == 'do') {
			//执行文章发布的操作

			//判断是否为vip，是否超过今天发布上限——待开发

			//判断文章类型是否在规定的范围内
			if (!isset($this->config->item('article_type')[$this->input->post('type')])) {
				get_json(400, '请选择正确的文章类型哟！');
			}

			//文章类型
			$type = $this->config->item('article_type')[$this->input->post('type')];

			//发布文章字段
			$article_data = array(
				'title' => $this->input->post('title'),
				'content' => $this->input->post('content', false),
				'type' => $type,
				'create_time' => time(),
				'user_id' => 1,
			);

			//执行插入操作
			$status = $this->db->insert('article', $article_data);
			if ($status) {
				get_json(200, '发布完成！');
			} else {
				get_json(400, '发布失败了, 请检查您的网络');
			}
		} else {
			//文章发布页面
			$this->load->view('index/editor.html');
		}
	}

	//artEditor文章图片上传
	public function artEditor_img_upload() {
		$base64_image_content = $this->input->post('image', false);
		//echo $base64_image_content;
		//匹配出图片的格式
		if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
			$type = strtolower($result[2]);
			$allowed_type = array('gif', 'jpeg', 'png', 'bmp');
			if (!in_array($type, $allowed_type)) {
				echo json_encode(array('error' => $type . '格式的图片不允许上传哟！'));
			}
			$new_file = "uploads/articleImg/" . date('Ymd', time()) . "/";
			if (!file_exists($new_file)) {
				//检查是否有该文件夹，如果没有就创建，并给予最高权限
				mkdir($new_file, 0777, true);
			}
			$new_file = $new_file . md5(time() . mt_rand(1000, 9999)) . ".{$type}";
			if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
				//echo $new_file;
				echo json_encode(array('path' => '/' . $new_file));
				return;
			} else {
				echo json_encode(array('result' => 'error'));
			}
		}
	}

	/*
		* 文章和评论的点赞
		* type=article=>文章  type=comment=>评论
	*/
	public function praise($type, $id) {
		if (!is_numeric($id)) {
			//id不为数字
			return;
		}
		//拼接sql语句
		if ($type == 'article') {
			$sql = 'UPDATE article SET praise = praise+1 WHERE article_id = ' . $id;
		} else {
			$sql = 'UPDATE comment SET praise = praise+1 WHERE comment_id = ' . $id;
		}
		if ($this->db->query($sql)) {
			get_json(200, '点赞成功');
		} else {
			get_json(400, '点赞失败');
		}
	}

	/*
		*对文章的评论
	*/
	public function comment($id) {
		if (!is_numeric($id)) {
			get_json(400, '该文章不存在');
		}

		//接收评论信息
		$data = array(
			'article_id' => $id,
			'user_id' => $this->session->userdata('id'),
			'content' => $this->input->post('content'),
			'create_time' => time(),
		);
		//执行插入评论信息操作 并返回信息
		if ($this->db->insert('comment', $data)) {
			//获取评论者的信息
			$user = $this->index_model->get_user(array('user_id' => $this->session->userdata('id')))[0];
			get_json(200, '评论成功', $user);
		} else {
			get_json(400, '评论失败，请稍后重试！');
		}
	}
	public function ceshi() {
		$array = array(
			'data' => 200,
			'message' => 'dada',
		);
		get_json(200, 'dadsad');
		echo '2111';
	}
}
