<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/30
 * Time: 19:52
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Weixin extends CI_Controller {
    /**
     * 微信消息对象
     * @var WechatReceive
     */
    protected $wechat;

    protected $menu;

    /**
     * 微信openid
     * @var type
     */
    protected $openid;

    /**
     * 接口入口
     */

    public function index() {
        /* 创建接口操作对象 */
        $this->wechat = &load_wechat('Receive');
        /* 验证接口 */
        if ($this->wechat->valid() === FALSE) {
            log_message('ERROR', "微信被动接口验证失败，{$this->wechat->errMsg}[{$this->wechat->errCode}]");
            exit($this->wechat->errMsg);
        }
        /* 获取openid */
        $this->openid = $this->wechat->getRev()->getRevFrom();
        /* 记录接口日志 */
        //$this->_logs();
        /* 分别执行对应类型的操作 */
        switch ($this->wechat->getRev()->getRevType()) {
            case WechatReceive::MSGTYPE_TEXT:

                $keys = $this->wechat->getRevContent();
                return $this->_keys("wechat_keys#keys#{$keys}");
            case WechatReceive::MSGTYPE_EVENT:
                $event = $this->wechat->getRevEvent();
                return $this->_event($event);
            case WechatReceive::MSGTYPE_IMAGE:

                return $this->_image();
            case WechatReceive::MSGTYPE_LOCATION:

                return $this->_location();
            default:

                return $this->_default();
        }
    }

    /**
     * 关键字处理
     * @param type $keys      关键字（常规或规格关键字）
     * @return type
     */
    protected function _keys($keys) {
        $url="http://www.tuling123.com/openapi/api?key=78ceb20dc9414a4aa9c785b78af69ef3";
        $url =$url.'&info='.$keys."&userid=1234";
        $content=file_get_contents($url);
        $msg=json_decode($content)->text;
        $this->wechat->text($msg)->reply();
    }

    /**
     * 智能默认回复
     * @param type $msg
     */
    protected function _default_reply($msg = '') {
        $keys = $this->wechat->getRevContent();
        if (empty($msg) && preg_match('/^\d{17}\d|X$/i', $keys)) {
            $url = "http://apis.juhe.cn/idcard/index?cardno={$keys}&dtype=json&key=81352b16963e290f98c016cdcd2508b5";
            $result = json_decode(Http::get($url), TRUE);
            if (!empty($result['result']['area'])) {
                $msg = "证号：{$keys}\n";
                $msg .= "性别：{$result['result']['sex']}\n";
                $msg .= "生日：{$result['result']['birthday']}\n";
                $msg .= "区域：{$result['result']['area']}";
            }
        }
        # 识别手机号
        if (empty($msg) && preg_match('/^1\d{10}$/i', $keys)) {
            $url = "http://apis.juhe.cn/mobile/get?phone={$keys}&dtype=json&key=836454688c9a444fc4813b943fb8e4cd";
            $result = json_decode(Http::get($url), TRUE);
            if (!empty($result['result']['province'])) {
                $msg = "手机号：{$keys}\n";
                $msg .= "地　区：{$result['result']['province']}{$result['result']['city']}\n";
                $msg .= "运营商：{$result['result']['company']}\n";
                $msg .= "卡类型：{$result['result']['card']}";
            }
        }
        # 机器人
        if (empty($msg)) {
            $url = "http://op.juhe.cn/robot/index?info={$keys}&dtype=json&loc=&lon=&lat=&userid={$this->openid}&key=90e77695b06915a6f0096036bfeb3f54";
            $result = json_decode(Http::get($url), TRUE);
            if (!empty($result['result']['text'])) {
                $msg = $result['result']['text'];
            }
        }
        exit(empty($msg) ? 'success' : $this->wechat->text($msg)->reply());
    }

    /**
     * 回复图文
     * @param type $news_id
     */
    protected function _news($news_id = 0) {
        $this->load->library('NewsData');
        $newsinfo = $this->newsdata->readyById($news_id);
        if (!empty($newsinfo)) {
            $newsdata = array();
            foreach ($newsinfo['articles'] as &$vo) {
                $newsdata[] = array('Title' => $vo['title'], 'Description' => $vo['digest'], 'PicUrl' => $vo['local_url'], 'Url' => site_url("news-{$vo['id']}"));
            }
            $this->wechat->news($newsdata)->reply();
        } else {
            exit('success');
        }
    }

    /**
     * 事件处理
     * @return type
     */
    protected function _event($event) {

        switch (strtolower($event['event'])) {
            case 'subscribe':/* 关注事件 */
                return $this->wechat->text('欢迎关注货卡人之家公众号！更好的为卡友、物流企业、配件商家、维修企业提供最优质的服务。平台提供新旧车信息，货源信息，货物仓储服务，保险代理咨询，车辆消费信贷金融咨询，车辆动态监控，车辆救援服务，配件销售，维修企业名录等。一切为了更好的服务用户！')->reply();
            case 'unsubscribe':/* 取消关注 */
                return $this->wechat->text('欢迎关注货卡人之家公众号！更好的为卡友、物流企业、配件商家、维修企业提供最优质的服务。平台提供新旧车信息，货源信息，货物仓储服务，保险代理咨询，车辆消费信贷金融咨询，车辆动态监控，车辆救援服务，配件销售，维修企业名录等。一切为了更好的服务用户！')->reply();
                exit('success');
            case 'click': /* 点击链接 */
                return $this->_keys($event['key']);
            case 'scancode_push':
            case 'scancode_waitmsg':/* 扫码推事件 */
                return $this->wechat->text('欢迎关注货卡人之家公众号！更好的为卡友、物流企业、配件商家、维修企业提供最优质的服务。平台提供新旧车信息，货源信息，货物仓储服务，保险代理咨询，车辆消费信贷金融咨询，车辆动态监控，车辆救援服务，配件销售，维修企业名录等。一切为了更好的服务用户！')->reply();
            case 'scan':
                return $this->wechat->text('欢迎关注货卡人之家公众号！更好的为卡友、物流企业、配件商家、维修企业提供最优质的服务。平台提供新旧车信息，货源信息，货物仓储服务，保险代理咨询，车辆消费信贷金融咨询，车辆动态监控，车辆救援服务，配件销售，维修企业名录等。一切为了更好的服务用户！')->reply();
        }
    }

    /**
     * 推荐好友扫码关注
     * @param type $key
     */
    protected function _spread($key) {
        $fans = $this->db->where('id', $key)->get('wechat_fans')->first_row('array');
        if (empty($fans) || $fans['openid'] === $this->openid) {
            return;
        }
        # 标识推荐关系
        $this->db->reset_query();
        $this->db->where('openid', $this->openid)->where('spread_openid is ', ' NULL', FALSE)->or_where('spread_openid', '');
        $this->db->update('wechat_fans', array('spread_at' => date('Y-m-d H:i:s'), 'spread_openid' => $fans['openid']));
        # 推荐成功的奖励
        // @todo
    }

    /**
     * 位置类事情回复
     */
    protected function _location() {
        $vo = $this->wechat->getRevData();
        $url = "http://apis.map.qq.com/ws/geocoder/v1/?location={$vo['Location_X']},{$vo['Location_Y']}&key=ZBHBZ-CHQ2G-RDXQF-I5TUX-SAK53-A5BZT";
        $data = json_decode(file_get_contents($url), true);
        if (!empty($data) && intval($data['status']) === 0) {
            $msg = $data['result']['formatted_addresses']['recommend'];
        } else {
            $msg = "{$vo['Location_X']},{$vo['Location_Y']}";
        }
        $this->wechat->text($msg)->reply();
    }

    /**
     * 默认事件处理
     */
    protected function _default() {
        return $this->wechat->transfer_customer_service()->reply();
        exit('success');
    }

    /**
     * 图片事件处理
     */
    protected function _image() {
        return $this->_keys('wechat_keys#keys#default', TRUE);
        exit('success');
    }

    /**
     * 记录接口日志
     */
    protected function _logs() {
        $data = $this->wechat->getRev()->getRevData();
        if (empty($data)) {
            return;
        }
        if (isset($data['Event']) && in_array($data['Event'], array('scancode_push', 'scancode_waitmsg', 'scan'))) {
            $scanInfo = $this->wechat->getRev()->getRevScanInfo();
            $data = array_merge($data, $scanInfo);
        }
        if (isset($data['Event']) && in_array($data['Event'], array('location_select'))) {
            $locationInfo = $this->wechat->getRev()->getRevSendGeoInfo();
            $data = array_merge($data, $locationInfo);
        }
        $this->wechat->formdata->save('wechat_message', array_change_key_case($data, CASE_LOWER));
    }

    /**
     * 同步粉丝状态
     * @param type $subscribe
     */
    protected function _sync_fans($subscribe = TRUE) {
        if ($subscribe) {
            $wechat = & load_wechat('User');
            $fansInfo = $wechat->getUserInfo($this->openid);
            $fansInfo['subscribe'] = intval($subscribe);
            $this->load->library('FansData');
            $this->fansdata->set($fansInfo);
        } else {
            $this->formdata->save('wechat_fans', array('openid' => $this->openid, 'subscribe' => '0'), 'openid');
        }
    }

    //简单创建菜单 public=>允许访问创建菜单；private=>不允许访问创建菜单;
    public function create_menu(){
        $data = array(
            //button类型
            'button'=>array(
                //第一个一级菜单
                array(
                    'name'=>'卡友圈',
                    //二级菜单sub_button类型
                    'sub_button'=>array(
                        array(
                            'type'=>'view',
                            'name'=>'卡友论坛',
                            'url'=>site_url('home/index'),
                        ),
                        array(
                            'type'=>'view',
                            'name'=>'卡友求助',
                            'url'=>site_url('home/index/卡友求助'),
                        ),
                        array(
                            'type'=>'view',
                            'name'=>'货源信息',
                            'url'=>base_url(),
                        ),
                        array(
                            'type'=>'view',
                            'name'=>'车辆监控',
                            'url'=>site_url('monitor/index'),
                        ),
                    ),
                ),

                //第二个一级菜单
                array(
                    'type'=>'view',
                    'name'=>'发现',
                    'url'=>site_url('home/discover'),
                ),

                //第三个二级菜单
                array(
                    'type'=>'view',
                    'name'=>'二手车',
                    'url'=>site_url('usedcar/index'),
                ),
            ),
        );
        $this->menu = & load_wechat('menu');

        $result = $this->menu->createMenu($data);

        if($result == FALSE){
            echo $this->menu->errMsg;
        }else{
            echo '菜单创建成功';
        }
    }
}