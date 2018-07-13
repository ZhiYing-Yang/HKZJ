<?php
defined('BASEPATH') or die('No direct script access allowed');

/**
 *
 */
class Myclass {
	protected $CI;

	function __construct() {
		# code...
		$this->CI = &get_instance();
	}

	//分页
	public function fenye($page_url, $total_rows, $offset_uri_segment, $per_page = 10) {
		//载入分页类
		$this->CI->load->library('pagination');
		//配置分页信息
		$config['base_url'] = $page_url;
		$config['total_rows'] = $total_rows;
		$config['per_page'] = $per_page;
		$config['uri_segment'] = $offset_uri_segment;
		$config['first_link'] = '首页';
		$config['last_link'] = '尾页';
		$config['next_link'] = '下一页';
		$config['prev_link'] = '上一页';
		//载入配置信息
		$this->CI->pagination->initialize($config);
		$link = $this->CI->pagination->create_links();
		return $link;
	}

	public function send_email($to, $subject = '', $message = '') {
		$this->CI->load->library('email');
		$this->CI->email->clear();
		$config = array(
			'protocol' => 'smtp',
			'smtp_host' => $this->CI->config->item('smtp_host'),
			'smtp_user' => $this->CI->config->item('smtp_user'),
			'smtp_pass' => $this->CI->config->item('smtp_pass'),
			'validate' => TRUE,
			'newwline' => '\r\n',
			'crlf' => '\r\n',
			'priority' => 1,
			'smtp_port' => 25,
			'charset' => 'utf-8',
		);
		$this->CI->email->initialize($config);
		$this->CI->email->from($this->CI->config->item('smtp_user'), '');
		$this->CI->email->to($to);
		$this->CI->email->subject($subject);
		$this->CI->email->message($message);

		/*if (!$this->email->send()) {
			return $this->email->print_debugger();
		}*/
		return $this->CI->email->send();
	}
}