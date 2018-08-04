<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Login extends CI_Controller {

	/*
		登录页面
	*/
	public function index() {
		$this->load->view('admin/login.html');
	}
	/*
		登陆验证页
	*/
	public function login_in() {
		$code = $this->input->post('authcode');
		if (!isset($_SESSION)) {
			session_start();
		}
		$authcode = $this->session->userdata('authcode');
		//echo $code;
		if (strtolower($code) != strtolower($authcode)) {alert_msg('验证码错误');return;};
		//echo $code;die;
		$this->load->model('admin_model');
		$username = $this->input->post('username');
		$password = $this->input->post('password');
		$status = $this->admin_model->get_admin_info($username, md5($password));
		if (empty($status)) {
			alert_msg('用户名或密码错误');
		} else {
			$this->session->set_userdata(array('admin_name' => $username, 'id' => $status[0]['id'], 'identity' => $status[0]['identity']));
			header('location:' . site_url('admin/admin/'));
		}
		exit;
	}
	/*
		修改密码
	*/

	/*
		退出
	*/
	public function logout() {
		$this->session->sess_destroy();
		alert_msg('成功退出');
	}

	/*
		验证码
	*/
	public function authcode() {
		/*if (!isset($_SESSION)) {
			session_start();
		}*/
		$img = imagecreatetruecolor(100, 40);
		$bgcolor = imagecolorallocate($img, rand(200, 255), rand(200, 255), rand(200, 255));
		imagefill($img, 0, 0, $bgcolor);
		$captch_code = "";
		$fontfile = $this->config->item('MYPATH') . 'Soopafresh.ttf';
		for ($i = 0; $i < 4; $i++) {
			$fontsize = 20;
			$fontcolor = imagecolorallocate($img, rand(0, 100), rand(0, 100), rand(0, 100));
			$date = "abcdefghjkmnpqrstuvwxyz23456789";
			$fontcontent = substr($date, rand(0, strlen($date)), 1);
			$captch_code .= $fontcontent;

			$x = ($i * 100 / 4) + rand(5, 10);
			$y = rand(25, 30);

			imagettftext($img, $fontsize, 0, $x, $y, $fontcolor, $fontfile, $fontcontent);
		}
		$this->session->set_userdata(array('authcode' => $captch_code));
		//点干扰
		for ($i = 0; $i < 200; $i++) {
			$pointcolor = imagecolorallocate($img, rand(50, 200), rand(50, 200), rand(50, 200));
			imagesetpixel($img, rand(1, 99), rand(1, 29), $pointcolor);

		}

		//线干扰
		for ($i = 0; $i < 3; $i++) {
			$linecolor = imagecolorallocate($img, rand(80, 220), rand(80, 220), rand(80, 220));
			imageline($img, rand(1, 99), rand(1, 29), rand(1, 99), rand(1, 29), $linecolor);
		}

		header('content-type:image/png');
		imagepng($img);
	}
}
