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
		if (empty($user_id)) {
			header('location:' . site_url('login/weChat_login'));
		}
	}
}