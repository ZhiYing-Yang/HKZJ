<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('index_model');
	}

	public function index() {
		echo '安排上了';
	}
}
