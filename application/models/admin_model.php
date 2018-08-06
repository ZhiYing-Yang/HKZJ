<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Admin_model extends CI_Model {

    public function get_forum_search($keywords){
        $keywords = addslashes($keywords);//防止sql注入攻击
        $get_info = 'article_id, article.user_id, type, title, content, article.create_time, praise, `read`, is_top, nickname, headimgurl, vip, solve';
        $sql = 'SELECT '. $get_info .' FROM article, user WHERE article.user_id = user.user_id AND ( title LIKE "%' . $keywords . '%" ESCAPE "!" OR content LIKE "%' . $keywords . '%" ESCAPE "!") ORDER BY create_time DESC';
        $status = $this->db->query($sql)->result_array();
        return $status;
    }


    /***************  司机群部分  ****************/
    public function  get_flock_search($keywords, $where_arr){
        $keywords = addslashes($keywords);
        $this->db->like('title', $keywords)->or_like('province', $keywords)->or_like('city', $keywords)->or_like('county', $keywords);
        $status = $this->db->get_where('flock', $where_arr)->result_array();
        return $status;
    }
	
}
