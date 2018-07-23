<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Index_model extends CI_model {
	//关联user表和article表 获取文章列表+用户头像、昵称等信息
	public function get_user_article_list($where_arr, $order_str, $offset, $per_page = 10) {
		$get_info = 'article_id, article.user_id, type, title, content, article.create_time, praise, read, is_top, nickname, headimgurl, vip';
		$status = $this->db->select($get_info)->order_by($order_str)->limit($per_page, $offset)->join('user', 'article.user_id = user.user_id')->get_where('article', $where_arr)->result_array();
		return $status;
	}

	//获取文章信息
	public function get_article($where_arr) {
		$status = $this->db->get_where('article', $where_arr)->result_array();
		return $status;
	}
	//获取用户信息
	public function get_user($where_arr) {
		$get_info = 'user_id, nickname, headimgurl, signature, sex, province, city, vip';
		$status = $this->db->select($get_info)->get_where('user', $where_arr)->result_array();
		return $status;
	}
	//获取评论信息
	public function get_user_comment_list($where_arr, $order_str, $offset, $per_page = 20) {
		$get_info = 'comment_id, comment.user_id, content, comment.create_time, praise, pid, nickname, headimgurl, vip';
		$status = $this->db->select($get_info)->order_by($order_str)->limit($per_page, $offset)->join('user', 'user.user_id = comment.user_id')->get_where('comment', $where_arr)->result_array();
		return $status;
	}
	/*
		*处理文章列表数据
		*获取
	*/
	public function format_data($data) {
		//检查有没有信息 如果没有返回空数组 不予遍历
		if (empty($data)) {
			return array();
		}
		$pattern_img = "/<(img|IMG)(.*?)(\/>|><\/img>|>)/";
		$pattern_src = '/(src|SRC)=(\'|\")(.*?)(\'|\")/';
		$status = array();
		foreach ($data as $datas) {
			$datas['url'] = array();
			//先匹配img标签
			if (preg_match_all($pattern_img, $datas['content'], $matchs)) {
				//再匹配img标签中图片src地址

				//匹配第一张图片地址
				if (preg_match_all($pattern_src, $matchs[0][0], $src)) {
					$datas['url'][0] = $src[3][0];
				}
				//如果有第二张图 继续匹配第二张图片地址
				if (isset($matchs[0][1]) && !empty($matchs[0][1]) && preg_match_all($pattern_src, $matchs[0][1], $src)) {
					$datas['url'][1] = $src[3][0];
				}
				//如果有三张图  继续匹配第三张图片地址
				if (isset($matchs[0][2]) && !empty($matchs[0][2]) && preg_match_all($pattern_src, $matchs[0][2], $src)) {
					$datas['url'][2] = $src[3][0];
				}
			}
			//格式化时间， 几分钟前形式
			$data['create_time'] = formatTime($datas['create_time']);
			//获取每一篇文章的评论量
			$comment_total = $this->db->where(array('article_id' => $datas['article_id']))->count_all_results('comment');
			$datas['comment_total'] = $comment_total;
			//去掉html标签
			$datas['content'] = preg_replace('/<\/?\s*\w+.*?>/', '', $datas['content']);
			$datas['content'] = strlen($datas['content']) > 60 ? mb_substr($datas['content'], 0, 60) . '...' : $datas['content'];
			$status[] = $datas;
		}
		return $status;
	}
}