<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Admin_model extends CI_Model {

    /******************** 论坛部分Begin *********************/
    //论坛文章搜索
    public function get_forum_search($keywords){
        $keywords = addslashes($keywords);//防止sql注入攻击
        $get_info = 'article_id, article.user_id, type, title, content, article.create_time, praise, `read`, is_top, nickname, headimgurl, vip, solve';
        $sql = 'SELECT '. $get_info .' FROM article, user WHERE article.user_id = user.user_id AND ( title LIKE "%' . $keywords . '%" ESCAPE "!" OR content LIKE "%' . $keywords . '%" ESCAPE "!") ORDER BY create_time DESC';
        $status = $this->db->query($sql)->result_array();
        return $status;
    }

    //用户举报帖子
    public function get_accuse_article_list($where_arr, $offset, $per_page=10){
        $get_info = 'id, accuse_article.article_id, reason, accuse_article.content, accuse_article.create_time, disposed, title, accuse_article.user_id as accuser_id, article.user_id as accused_id';
        $status = $this->db->select($get_info)->order_by('create_time DESC')->join('article', 'accuse_article.article_id = article.article_id')->get_where('accuse_article', $where_arr, $per_page, $offset)->result_array();
        return $status;
    }

    //论坛用户列表
    public function get_user_list($where_arr, $offset, $per_page=10){
        $get_info = 'user_id, phone, nickname, headimgurl, signature, province, city, vip, sex, status, login_time';
        $status = $this->db->select($get_info)->order_by('login_time DESC, user_id DESC')->get_where('user', $where_arr, $per_page, $offset)->result_array();
        return $status;
    }

    //论坛用户搜索
    public function get_forum_user_search($keywords){
        $keywords = addslashes($keywords);//防止sql注入攻击
        $get_info = 'user_id, phone, nickname, headimgurl, signature, province, city, vip, sex, status';
        $status = $this->db->select($get_info)->get('user')->like('nickname', $keywords)->or_like('phone', $keywords)->or_like('province', $keywords)->or_like('city', $keywords)->or_like('signature', $keywords)->result_array();
        return $status;
    }

    //获取用户的发帖数
    public function get_user_article_count($data){
        if(empty($data)){
            return $data;
        }

        foreach ($data as $d){
            $d['article_total'] = $this->db->where(array('user_id'=>$d['user_id']))->count_all_results('article');
            $status[] = $d;
        }
        return $status;

    }

    /******************** 论坛部分End *********************/


    /***************  司机群部分  ****************/
    public function  get_flock_search($keywords, $where_arr){
        $keywords = addslashes($keywords);
        $this->db->like('title', $keywords)->or_like('province', $keywords)->or_like('city', $keywords)->or_like('county', $keywords);
        $status = $this->db->get_where('flock', $where_arr)->result_array();
        return $status;
    }


    /*******************  二手车交易平台  *********************/
    //获取车辆信息
    public function get_car_info($where_arr){
        $status = $this->db->get_where('used-car_sale', $where_arr)->result_array();
        return $status;
    }
	
}
