<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/31
 * Time: 19:09
 */
class Login extends CI_Controller{


    /**
     * 微信登录，网页授权第一步
     * 创建微信网页授权URL
     */
    public function weChat_login($scope_code = 0){

        //SDK对象实例
        $oauth = & load_wechat('Oauth');

        //设置相关参数
        $callback = site_url('login/get_access_token'); //微信回跳地址（接口已经默认url_encode处理，授权成功会有$_GET['code']值，可用于下个步骤）
        $state = 'jxhkzj'; //重定向后会带上state参数（开发者可以填写a-zA-Z0-9的参数值，最多128字节）
        if($scope_code == 1){
            $scope = 'snsapi_userinfo'; //应用授权作用域（snsapi_base | snsapi_userinfo）
        }else{
            $scope = 'snsapi_base'; //只能获取用户的openid
        }


        //执行接口操作
        $result = $oauth->getOauthRedirect($callback, $state, $scope);

        //处理返回结果
        if($result === false){
            echo '授权失败';
        }else{
            header('location:'.$result);
            echo '第一步创建微信网页授权完成';
        }
    }

    /**
     * 微信登录，网页授权第二步和第三步
     * 通过code换取网页授权access_token 成功后 -->获取授权后的用户资料
     */
    public function get_access_token(){
        log_message('INFO', '走到get_access_token这一步了');
        $oauth = & load_wechat('Oauth');

        //执行接口操作
        $result = $oauth->getOauthAccessToken();

        //处理结果
        if($result === false){
            return false;
        }else { //成功获取access_token后->获取登陆者信息
            $openid       = $result['openid'];
            $access_token = $result['access_token'];
            //查看数据库里是否有用户信息
            $this->load->model('index_model');
            $user = $this->index_model->get_user(array('openid' => $result['openid']));

            if (empty($user)) {//数据库里没有用户信息
                $result = $oauth->getOauthUserinfo($access_token, $openid);
                if ($result == false) { //简单网页授权(snsapi_base)拉去用户信息失败 ，则调用snsapi_userinfo重新进行网页授权
                    $this->weChat_login(1);
                    return;
                } else {
                    log_message('INFO', '数据库里没有信息准备序列化信息然后插入');
                    $user_data = array(
                        'openid' => $result['openid'],
                        'nickname' => $result['nickname'],
                        'sex' => $result['sex'] == 1 ? '男' : '女',
                        'province' => $result['province'],
                        'city' => $result['city'],
                        'headimgurl' => $result['headimgurl'],
                        'login_time' => time(),//更新登录时间
                    );

                    //获取用户信息，自行序列化后插入数据库
                    if ($this->db->insert('user', $user_data)) {
                        $id = $this->db->insert_id();
                        $this->session->set_userdata(array('user_id' => $id));//将用户id存储到session
                        header('Location:' . $this->session->userdata('go_url'));
                    } else {
                        echo '插入用户信息失败，请重试';
                    }
                }
            } else {//数据库里有用户信息
                if($user[0]['status'] == 0){ //账号被封
                    $data['str'] = '<h4 style="color: #f7f9fa;">您的账户存在违规行为，已被管理员封禁</h4>';
                    $data['title'] = '账号已被封禁';
                    $this->load->view('404/404.html', $data);return;
                }
                $id = $user[0]['user_id'];
                $this->db->update('user', array('login_time'=>time()), array('user_id'=>$id));//更新登录时间
                $this->session->set_userdata(array('user_id' => $id));
                header('Location:' . $this->session->userdata('go_url'));
            }
        }
    }

    /**
     * 二手车交易平台登录
     */
    public function used_car_login(){
        if(empty($this->session->userdata('user_id'))){ //如果用户没有登录论坛，先登录论坛
            $this->session->set_userdata('go_url', site_url('login/used_car_login'));
            $this->weChat_login();return;
        }else{  //取用户部分信息填入二手车
            $forum_id = $this->session->userdata('user_id');

            $this->load->model('usedcar_model');
            $user = $this->usedcar_model->get_user_info(array('forum_id'=>$forum_id));

            if(empty($user)){   //二手车用户表里没有该用户相关信息
                $this->load->model('index_model');
                $data = $this->index_model->get_user(array('user_id'=>$forum_id));
                if(empty($data)){   //论坛用户表里没有用户数据，重新执行微信网页授权登录
                    $this->weChat_login();return;
                }else{ //将信息插入二手车用户表
                    $user_data = array(
                        'forum_id'=>$data[0]['user_id'],
                        'headimgurl'=>$data[0]['headimgurl'],
                        'nickname'=>$data[0]['nickname'],
                    );
                    if($this->db->insert('used-car_user', $user_data)){
                        $used_car_user_id = $this->db->insert_id();
                        $this->session->set_userdata('used_car_user_id', $used_car_user_id);

                        header('location:'.site_url('usedcar/index'));
                    }else{
                        header('location:'.base_url());
                    }
                }
            }else{
                $this->session->set_userdata('used_car_user_id',$user[0]['id']);
                header('location:'.site_url('usedcar/index'));
            }
        }
    }


    //测试一下
    public function ceshi() {
       var_dump(file_exists('uploads/usedcarImg/1/20180815/a983dea9dffa5dbb7e9f310b7e345767.jpeg'));
    }

}