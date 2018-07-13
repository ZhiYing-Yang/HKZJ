<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: Ascexz
 * Date: 2018/3/27
 * Time: 18:07
 */
Class MY_Controller extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$user_id = $this->session->userdata('user_id');
		$realname = $this->session->userdata('realname');
		$identity = $this->session->userdata('identity');
		if (empty($user_id) || empty($realname) || empty($identity)) {
			header('location:' . site_url('home/login'));
		}
	}
}