<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('index_model');
		$this->session->set_userdata('id', 1);
	}

	//论坛首页帖子，type = '精品' => 精品帖子 '所有'=>除了卡友求助以外的全部帖子 '热门'=>卡友求助的热门帖子
	public function index($type = '所有', $offset = 0, $la = false) {
		$type = urldecode($type); //文章类型
		$data['type'] = $type;
		$where_arr = array('type !=' => '卡友求助');
		//精品帖子
		if ($type == '精品') {
			$order_str = 'praise DESC, read DESC';
			$article = $this->index_model->get_user_article_list($where_arr, $order_str, $offset, 10);
			$data['article'] = $this->index_model->format_data($article);
			$this->load->view('index/index_boutique.html', $data);
			return;
		}

		//热门求助
		if ($type == '热门') {
			$order_str = 'read DESC, article.create_time ASC';
			$article = $this->index_model->get_user_article_list(array('type' => '卡友求助', 'solve' => 0), $order_str, $offset, 10);
			$data['article'] = $this->index_model->format_data($article);
			$this->load->view('index/help-hot.html', $data);
			return;
		}

		//论坛首页帖子,获取不同类型的文章
		if (in_array($type, $this->config->item('article_type'))) {
			$where_arr = array('type' => $type);
		}
		$order_str = 'article.create_time DESC, praise DESC'; //排序规则
		//关联查询user表和article表 获取文章数据和想关发布者信息
		$status = $this->index_model->get_user_article_list($where_arr, $order_str, $offset);
		//格式化处理文章数据，去掉html标签，匹配出文章图片
		$data['article'] = $this->index_model->format_data($status);

		//如果是上拉加载更多，返回json数据，否则data多传一个total_rows给前端识别共多少条文章
		if ($la == 'up') {
			get_json(200, '加载成功', $data['article']);
			return;
		} else {
			$data['total_rows'] = $this->db->where($where_arr)->count_all_results('article');
		}

		if ($type == '卡友求助') {
			$this->load->view('index/help.html', $data);
		} else {
			$this->load->view('index/index.html', $data);
		}
	}

	//卡友求助的搜索页
	public function help_search($search, $offset = 0, $la = '') {
		$search = urldecode($search);
		$status = $this->index_model->get_help_search($search, $offset);
		$data['article'] = $this->index_model->format_data($status);
		//print_r($data['article']);
		if (!empty($la)) {
			get_json(200, '加载成功！', $data['article']);return;
		} else {
			$total_rows = $this->index_model->get_help_search_count($search);
			$data['total_rows'] = empty($total_rows) ? 0 : $total_rows[0]['total_rows'];
			$data['search'] = $search;
			$this->load->view('index/help-search.html', $data);
		}
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
		$data['comment'] = $this->index_model->get_user_comment_list(array('article_id' => $id, 'pid' => 0), 'praise DESC, create_time DESC', 0);
		$data['comment'] = $this->index_model->get_reply_comment($data['comment']);
		if ($data['article']['type'] == '卡友求助') {
			$this->load->view('index/help-article.html', $data);
		} else {
			$this->load->view('index/article.html', $data);
		}
	}
	//更多评论或者回答页
	public function get_more_comment($id, $offset) {
		$data['comment'] = $this->index_model->get_user_comment_list(array('article_id' => $id, 'pid' => 0), 'create_time DESC', $offset);
		$data['comment'] = $this->index_model->get_reply_comment($data['comment']);
		get_json(200, '加载成功！', $data['comment']);
	}

	//举报文章页
	public function accuse_article($action, $id) {
		if (!is_numeric($id)) {
			get_json(400, '您举报的文章也被删除！');return;
		}
		if ($action == 'do') {
			$data = array(
				'article_id' => $id, //文章ID
				'reason' => $this->input->post('reason'), //举报原因
				'content' => $this->input->post('content'), //举报描述
				'create_time' => time(), //举报时间
			);
			if ($this->db->insert('accuse_article', $data)) {
				get_json(200, '举报成功，我们会尽快处理！');
			} else {
				get_json(400, '举报信息提交失败，请稍后重试！');
			}
		} else {
			$data['id'] = $id;
			$this->load->view('index/jubao.html', $data);
		}
	}

	//添加文章页
	public function add_article($action = 'see') {
		//默认添加文章页面
		if ($action == 'do') {
			//执行文章发布的操作

			//判断是否为vip，是否超过今天发布上限——待开发

			//判断文章类型是否在规定的范围内
			if (!isset($this->config->item('article_type')[$this->input->post('type')])) {
				get_json(400, '请选择正确的文章类型哟！');return;
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
				get_json(200, '发布完成！');return;
			} else {
				get_json(400, '发布失败了, 请检查您的网络');return;
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
			get_json(200, '点赞成功');return;
		} else {
			get_json(400, '点赞失败');return;
		}
	}

	/*
		*对文章的评论
	*/
	public function comment($id, $pid) {
		if (!is_numeric($id) && !is_numeric($id)) {
			get_json(400, '该文章不存在');return;
		}

		//接收评论信息
		$data = array(
			'article_id' => $id,
			'user_id' => $this->session->userdata('id'),
			'content' => $this->input->post('content'),
			'create_time' => time(),
			'pid' => $pid,
		);
		//执行插入评论信息操作 并返回信息
		if ($this->db->insert('comment', $data)) {
			//获取评论者的信息
			$user = $this->index_model->get_user(array('user_id' => $this->session->userdata('id')))[0];
			get_json(200, '评论成功', $user);return;
		} else {
			get_json(400, '评论失败，请稍后重试！');return;
		}
	}
	public function ceshi() {
		$a = '';
		var_dump(empty($a));
	}
}
