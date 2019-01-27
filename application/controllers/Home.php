<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends MY_Controller {
  private $id = "";
	public function __construct() {
		parent::__construct();
      $this->id = $this->session->userdata("user_id");
		  $this->load->model('index_model');
      if(empty($this->id)){
        $this->session->set_userdata(  array("go_url"=>'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']));
        header("location:".site_url("login/weChat_login"));
      }
		/*if(empty($this->session->userdata('user_id'))){ //测试用户
		    $this->session->set_userdata('user_id', 4);
        }*/
	}

	//论坛首页帖子，type = '精品' => 精品帖子 '所有'=>除了卡友求助以外的全部帖子 '热门'=>卡友求助的热门帖子
	public function index($type = 'all', $offset = 0, $la = '') {
		$type = urldecode($type); //文章类型
		$data['type'] = $type;
		$where_arr = '论坛';//除了 卡友求助, 公告, 广告以外的所有文章
    $data["banner"]=$this->db->get("advertise_index")->result_array() ;
		//精品帖子
		if ($type == '精品') {
			$order_str = 'praise DESC, read DESC';
			$article = $this->index_model->get_user_article_list($where_arr, $order_str, $offset, 10);
			$data['article'] = $this->index_model->format_data($article);
			$data['user'] = $this->index_model->get_user(array('user_id'=>$this->session->userdata('user_id')))[0];

            $data['active'] = '论坛'; //底部高亮标签名
			$this->load->view('index/index_boutique.html', $data);
			return;
		}

		//热门求助
		if ($type == '热门') {
			$order_str = 'read DESC, article.create_time ASC';
			$article = $this->index_model->get_user_article_list(array('type' => '卡友求助'), $order_str, $offset, 10);
			$data['article'] = $this->index_model->format_data($article);
            $data['active'] = '求助'; //底部高亮标签名
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
            $data['notice'] = $this->index_model->get_article_list(array('type'=>'公告'), 0, 3);
            if($where_arr = '论坛'){ // 论坛首页展示所有文章
                $this->db->where_in('type', array('卡友生活', '卡友经验', '自由贸易', '灌水区'));
            }else{ //根据条件决定
                $this->db->where($where_arr);
            }
			$data['total_rows'] = $this->db->count_all_results('article');
		}

		if ($type == '卡友求助') {
            $data['active'] = '求助'; //底部高亮标签名
			$this->load->view('index/help.html', $data);
		} else {
		    $data['user'] = $this->index_model->get_user(array('user_id'=>$this->session->userdata('user_id')))[0];
            $data['active'] = '论坛'; //底部高亮标签名
		    $this->load->view('index/index.html', $data);
		}
	}

	//卡友求助的搜索页
	public function help_search($search="", $offset = 0, $la = '1') {
		$search = urldecode($search);
		$status = $this->index_model->get_help_search($search, $offset);
		$data['article'] = $this->index_model->format_data($status);
		//print_r($data['article']);
		// if (!empty($la)) {
		// 	get_json(200, '加载成功！', $data['article']);return;
		// } else {
			$total_rows = $this->index_model->get_help_search_count($search);
			$data['total_rows'] = empty($total_rows) ? 0 : $total_rows[0]['total_rows'];
			$data['search'] = $search;
            $data['active'] = '求助'; //底部高亮标签名
			$this->load->view('index/help-search.html', $data);
		// }
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
		if(!empty($this->session->userdata('admin_id'))){ //管理员查看文章
		    $this->load->view('admin/forum/forum_article.html', $data);return;
        }
		if ($data['article']['type'] == '卡友求助') {
			$this->load->view('index/help-article.html', $data);
		} else {
			$this->load->view('index/article.html', $data);
		}
	}
	//求助解决
    public function help_solve($id){
	    if(!is_numeric($id)){
	        get_json(400,'该文章已被删除');return;
        }

        if($this->db->update('article', array('solve'=>1), array('article_id'=>$id))){
	        get_json(200, '问题已解决！');
        }else{
            get_json(200,'操作失败，请稍后重试！');
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
                'user_id' => $this->session->userdata('user_id'),
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
				'user_id' => $this->session->userdata('user_id'),
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
            $data['active'] = '添加'; //底部高亮标签名
			$this->load->view('index/editor.html', $data);
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
			'user_id' => $this->session->userdata('user_id'),
			'content' => $this->input->post('content'),
			'create_time' => time(),
			'pid' => $pid,
            'to_user'=>$this->input->post('to_user'),
		);
		//执行插入评论信息操作 并返回信息
		if ($this->db->insert('comment', $data)) {
			//获取评论者的信息
            $comment_id= $this->db->insert_id();
			$user = $this->index_model->get_user(array('user_id' => $this->session->userdata('user_id')))[0];
			$user['comment_id'] = $comment_id;
			get_json(200, '评论成功', $user);return;
		} else {
			get_json(400, '评论失败，请稍后重试！');return;
		}
	}

	//发现页
    public function discover(){
	    $data['active'] = '发现';
	    $this->load->view('index/discover.html', $data);
    }

    //个人中心 $id=>被访问者的id
    public function person(  $as =1, $type = 'article'){
        $id = $this->id;
        if(!is_numeric($id)){
          $data["str"] = "未找到";
            $this->load->view('404/404.html',$data);return;
        }
        $user_id = $this->session->userdata('user_id'); //当前访问者的id

        $data['active'] = '个人'; //图标高亮
        $data['person'] = $user_id == $id?'self':'other';
        $data['this_id'] = $id;
        if($type == 'help'){
            $where_arr = array('type'=> '卡友求助', 'article.user_id'=>$id);
            $view = 'index/person_help.html';
        }else{
            $where_arr = array('article.user_id'=>$id, 'type !='=> '卡友求助');
            $view = 'index/person.html';
        }
        $article = $this->index_model->get_article_list($where_arr, 0);
        $data['article'] = $this->index_model->format_data($article);
        $user = $this->index_model->get_user(array('user_id'=>$id));
        $data['user'] = $user[0];
        $this->load->view($view, $data);
    }

    //个人资料页
    public function personal_data(){
        $id = $this->session->userdata('user_id');

        $user = $this->index_model->get_user(array('user_id'=>$id));
        if(empty($user)){
            $this->load->view('404/404.html');return;
        }
        $data['user'] = $user[0];
        $this->load->view('index/personal_data.html', $data);
    }

    //修改个人资料 昵称、个性签名、手机号
    public function edit_personal_data(){
        //昵称
        if(!empty($this->input->post('nickname'))){
            $data['nickname'] = $this->input->post('nickname');
        }

        //个性签名
        if(!empty($this->input->post('signature'))){
            $data['signature'] = $this->input->post('signature');
        }

        //手机号
        if(!empty($this->input->post('phone'))){
            //post过来的验证码和session中的相等 且 手机号和session中的相等方可通过验证
            if($this->session->tempdata('phone_code') == $this->input->post('phone_code') || $this->session->tempdata('phone') !=$this->input->post('phone')){
                $data['phone'] = $this->input->post('phone');
            }else {
                get_json(400, '手机验证码错误，请重新输入！');
                return;
            }
        }

        if(!empty($data)){
            if($this->db->update('user',$data, array('user_id'=>$this->session->userdata('user_id')))){
                get_json(200, '保存成功');
            }else{
                get_json(200, '保存失败，请稍后重试！');
            }
        }else{
            get_json(400, '请输入要修改的内容');
        }
    }

    //获取手机短信验证码
    public function get_phone_code(){
        //验证码防止操作过于频繁
        if(!empty($this->session->tempdata('got'))){
            get_json(400, '您的操作过于频繁！');return;
        }
        $phone = $this->input->post('phone');
        //验证手机号的正确性 正则表达式验证 略

        //验证手机号是否被绑定
        $status = $this->index_model->get_user(array('phone'=>$phone));
        if(!empty($status)){
            get_json(400,'该手机号已经被绑定');return;
        }

        //生成并发送验证码
        $phone_code = mt_rand(000000, 999999);
        $this->load->library('mysms'); //加载自定义发送短信类库
        $result = $this->mysms->send_phone_code($phone, $phone_code);
        if($result['result'] == 0){
            $this->session->set_tempdata('phone', $phone);//将被发送验证码的手机号存入session中以备后续验证
            $this->session->set_tempdata('phone_code', $phone_code);//将验证码存入session中，已备验证
            $this->session->set_tempdata('got', '已发送', 60);
            get_json(200,'发送成功');
        }else{
            get_json(400,$result['errmsg']);
        }
    }

    //个人消息
    public function forum_message($action = '', $offset=0){
        $where_arr=array('comment.to_user' => $this->session->userdata('user_id'));
        $status = $this->index_model->get_message_list($where_arr, $offset, 10);
        if($action == 'more'){
            get_json(200,'获取成功', $status);return;
        }else{
            $data['message']=$status;
            $this->load->view('index/message.html', $data);
        }
    }
    //签到
    public function sign($value='')
    {
        $id = $this->id ;
        $info = $this->db->get_where("user", array("user_id " =>$id));
        $lastday = date( "Y-m-d" , $info["lastsign"]) ;
        $nowday = date("Y-m-d") ;
        if($lastday!=$nowday){
          $this->db->set("user" , array("sign"=>$info["sign"]+1 , "lastsign"=>time() ,array("user_id"=>$id))) ;
          get_json(200,"签到成功","");
        }


    }

    /***************************** 论坛部分结束 ***************************/

    /*************************  司机群部分Begin ***************************/

    //司机群列表
    public function flock_list($offset = 0, $la = null){
        $where_arr = array();
        $status = $this->index_model->get_flock_list($where_arr, $offset, 10);
        $data['flock'] = $this->index_model->format_data($status, false);

        if($la == ''){
            $this->load->view('index/flock/flock_index.html', $data);
        }else{
            get_json(200, '获取成功', $data['flock']);
        }
    }

    //司机群搜索列表
    public function flock_search($offset = 0, $la= null){
        $keywords = $this->input->post('keywords');
        $status = $this->index_model->get_search_flock_list($keywords, $offset, 10);
        $data['flock'] = $this->index_model->format_data($status, false);
        if($la == ''){
            $data['keywords'] = $keywords;//标识为搜索结果页 前台搜索框里显示搜索的关键词
            $this->load->view('index/flock/flock_index.html', $data);
        }else{
            get_json(200, '获取成功', $data['flock']);
        }
    }

    //查看司机群内容
    public function flock_see($id){
        $where_arr = array('id'=>$id);
        $status = $this->index_model->get_flock($where_arr);
        if(empty($status)){
            $data['str'] = '<h4 style="color: #FFFFFF;">您访问的信息不存在</h4>';
            $this->load->view('404/404.html', $data);
        }else{
            $data = $status[0];
            $this->load->view('index/flock/flock_see.html', $data);
        }
    }

    public function delete_sess(){
        unset($_SESSION);
        session_destroy();
    }
}
