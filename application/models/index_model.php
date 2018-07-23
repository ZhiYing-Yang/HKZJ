<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Index_model extends CI_model {
	//获取
	public function get_user_article_list($where_arr, $order_str, $offset, $per_page = 10) {
		$get_info = 'article_id, article.user_id, type, title, content, article.create_time, praise, read, is_top, nickname, headimgurl, vip';
		$status = $this->db->select($get_info)->order_by($order_str)->limit($per_page, $offset)->join('user', 'article.user_id = user.user_id')->get_where('article', $where_arr)->result_array();
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
			$data['create_time'] = $this->formatTime($datas['create_time']);
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

	//格式化时间多少天前
	private function formatTime($time) {
		if (!is_numeric($time)) {
			$time = strtotime($time);
		}
		$rtime = date("m-d H:i", $time);
		$htime = date("H:i", $time);
		$time = time() - $time;
		if ($time < 60) {
			$str = '刚刚';
		} elseif ($time < 60 * 60) {
			$min = floor($time / 60);
			$str = $min . '分钟前';
		} elseif ($time < 60 * 60 * 24) {
			$h = floor($time / (60 * 60));
			$str = $h . '小时前 ';
		} elseif ($time < 60 * 60 * 24 * 3) {
			$d = floor($time / (60 * 60 * 24));
			if ($d == 1) {
				$str = '昨天 ' . $rtime;
			} else {
				$str = '前天 ' . $rtime;
			}
		} else {
			$str = $rtime;
		}
		return $str;
	}
}