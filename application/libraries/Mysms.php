<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/4
 * Time: 10:10
 */
use Qcloud\Sms\SmsSingleSender;
class Mysms {
    protected $CI;
    protected $appid;
    protected $appkey;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->appid = $this->CI->config->item('SMS')['appid'];
        $this->appkey = $this->CI->config->item('SMS')['appkey'];
    }

    public function send_phone_code($phone, $phone_code){
        $templId = '168154';
        try {
            $sender = new SmsSingleSender($this->appid, $this->appkey);
            $params = [$phone_code];
            // 尊敬的卡友：{1}为您的验证码，请于5分钟内完成绑定。如非本人操作，请忽略本短信！
            $result = $sender->sendWithParam("86", $phone, $templId,
                $params, "", "", "");
            return json_decode($result, true);
        } catch(\Exception $e) {
            log_message('INFO','短信验证码发送失败');
        }
    }
}