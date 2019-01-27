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
	public function fenye($page_url , $total_rows, $offset_uri_segment, $per_page = 10) {
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

	//发送邮件
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

	//生成验证码
    public function authcode($key) {
        /*if (!isset($_SESSION)) {
            session_start();
        }*/
        $img = imagecreatetruecolor(100, 40);
        $bgcolor = imagecolorallocate($img, rand(200, 255), rand(200, 255), rand(200, 255));
        imagefill($img, 0, 0, $bgcolor);
        $captch_code = "";
        $fontfile = $this->CI->config->item('MYPATH') . 'Soopafresh.ttf';
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
        $this->CI->session->set_userdata(array($key => $captch_code));
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
