<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/2
 * Time: 10:39
 */
class Monitor extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        //$this->session->set_userdata('monitor_user_id', 1); //测试用户
        $this->id = $this->session->userdata('monitor_user_id');
        if (empty($this->id)) {
            if(empty($this->session->userdata('first_url'))){ //获取用户访问的url地址，以便登陆后跳转
                $this->session->set_userdata('first_url', current_url());
            }
            header('location:' . site_url('login/car_monitor_login'));
        }
        $this->load->model('monitor_model');
    }

    //首页，车辆监控页面
    public function index()
    {
        $data = array(
            'url' => site_url('monitor/index'),
            'timestamp' => time(),
            'noncestr' => 'Wm3WZYTPz0wzccnW',
            'appid' => $this->config->item('wechat_appid'),
        );
        $data = $this->get_signature($data); //获取签名

        //免费次数重置
        $free_time = 30; //免费次数
        $user      = $this->monitor_model->get_user_info(array('id' => $this->id))[0];
        if ($user['month'] != date('m')) { //本月没有重置
            $this->db->update('monitor_user', array('free_time' => $free_time, 'month' => date('m')), array('id' => $this->id));
            $user['free_time'] = $free_time;
        }
        $data['free_time'] = $user['free_time'];
        $data['money'] = $user['money'];
        $this->load->view('monitor/index.html', $data);
    }

    //获取微信js-sdk签名
    private function get_signature($data)
    {
        // 创建SDK实例
        $script = &load_wechat('Script');

        // 获取JsApi使用签名，通常这里只需要传 $ur l参数
        $options = $script->getJsSign($data['url'], $data['timestamp'], $data['noncestr'], $data['appid']);

        // 处理执行结果
        if ($options === FALSE) {
            // 接口失败的处理
            echo $script->errMsg;
        } else {
            return $options;
        }
    }


    //获取车辆位置，车辆监控
    public function car_monitor()
    {
        $car_num = $this->input->post('license_number');
        $charge  = 0; //免费查询

        $user = $this->monitor_model->get_user_info(array('id' => $this->id))[0];
        if ($user['free_time'] <= 0) { //免费次数已经用完 付费查询
            $charge = 'pay';
            if($user['money'] < 1){
                get_json(410, '货卡币余额不足');
                return;
            }
        }

        //模拟查询
        /*$status = '{"result":{"adr":"安徽省安庆市怀宁县长琳塑业，向西方向，148 米","drc":"225","lat":"18451089","lon":"70094469","spd":"73.0","utc":"1536049439000","province":"安徽省","city":"安庆市","country":"怀宁县"},"status":1001}';
        $arr    = json_decode($status, true)['result'];*/

        //正式调用接口查询
        $this->load->library('zhiyun');
        $arr = $this->zhiyun->get_location($car_num);

        if (is_array($arr)) { //查询成功

            $data = array(
                'license_number' => $car_num,
                'time' => date('Y-m-d H:i:s', substr($arr['utc'], 0, -3)),
                'lon' => $arr['lon'],
                'lat' => $arr['lat'],
                'spd' => $arr['spd'],
                'address' => $arr['adr'],
                'charge' => $charge,
            );

            if($charge != 0){
                //消耗一个货卡币
                $this->db->update('monitor_user', array('money'=>($user['money']-1)), array('id'=>$this->id));
            }else{
                //免费次数-1
                $this->db->update('monitor_user', array('free_time' => ($user['free_time']-1)), array('id' => $this->id));
            }
            get_json(200, '查询成功', $data);
        } elseif ($arr == '无结果') {
            get_json(401, '无结果');
        } else {
            get_json(400, '当前查询人数较多，请稍后再试');
        }
    }

    //货卡币充值
    public function recharge($action = 'see')
    {
        if ($action == 'see') {
            $data = array(
                'url' => site_url('monitor/recharge'),
                'timestamp' => time(),
                'noncestr' => 'Wm3WZYTPz0wzccnW',
                'appid' => $this->config->item('wechat_appid'),
            );
            $data = $this->get_signature($data); //获取签名
            $this->load->view('monitor/recharge.html', $data);
        } else { //获取options
            $fee = $this->input->post('fee');
            if (!is_numeric($fee)) {
                get_json(400, '请选择充值数量！');
                return;
            }
            $user         = $this->monitor_model->get_user_info(array('id' => $this->id))[0];
            $openid       = $user['openid'];
            $body         = '充值货卡币';
            $out_trade_no = $out_trade_no = time() . mt_rand(1000, 9999);
            $notify_url   = site_url('monitor/notify/'.$this->id);
            $total_fee    = $fee * 100; //单位 分
            $options      = $this->get_options($openid, $body, $out_trade_no, $total_fee, $notify_url);
            if (!$options) {
                get_json(400, '当前充值人数较多，请稍后再试！');
            } else {
                get_json(200, '获取成功', array('options' => $options));
            }
        }

    }

    //行驶证查询
    public function driving_license(){
        //if(empty($this->input->post('car_number'))){ //车辆行驶证信息查询页面

        //}else{
            //$car_number = $this->input->post('car_number'); //车牌号
            //$color_code = $this->input->post('color_code'); //车牌颜色  1=>蓝色  2=>黄色
            $car_number = '赣C1N980';
            $color_code = 2;
            $this->load->library('zhiyun');

            var_dump($this->zhiyun->get_driving_license($car_number, $color_code));
        //}
    }

    //找车,调用找车接口获取车辆信息
    public function seek_car(){
        //获取微信js-Api签名
        $data = array(
            'url' => site_url('monitor/seek_car'),
            'timestamp' => time(),
            'noncestr' => 'Wm3WZYTPz0wzccnW',
            'appid' => $this->config->item('wechat_appid'),
        );
        $data = $this->get_signature($data); //获取签名

        if(empty($this->input->post('str'))){ //找车页面

            $this->load->view('monitor/seek_car.html', $data);

        }else{ //调用接口获取附近车辆信息
            $str = $this->input->post('str');
            $data['str'] = $str;
            $pageNum = $this->input->post('pageNum');
            $pageNum = empty($pageNum)?1:$pageNum;
            $data['page'] = $pageNum;
            $str .='&pageNum='.$pageNum;
            //echo $str;
            $this->load->library('zhiyun');
            $data['car'] = $this->zhiyun->get_car_infoV3($str);
            //print_r($data['car']);
            $this->load->view('monitor/car_info.html', $data);
        }
    }

    //车主信息
    public function driver_info(){
        $vid = $this->input->post('vid');

        $this->load->model('monitor_model');
        $user = $this->monitor_model->get_user_info(array('id'=>$this->id))[0];
        if($user['money'] < 2){ //货卡币不够，提醒用户充值
            get_json(410, '货卡币余额不足，请及时充值');
            return;
        }

        $this->load->library('zhiyun');
        $data = $this->zhiyun->get_driver_info($vid);
        /*$data = array(
            'vehicleno'=>'京 A12345',
            'platecolorid'=>1,
            'vehicleOwnerName'=>'王郝鹏',
            'vehicleOwnerPhone'=>'15515613215'
        );*/
        if($data){
            $this->db->update('monitor_user', array('money'=>($user['money']-2)), array('id'=>$this->id));
            get_json(200, '查询成功', $data);
        }else{
            get_json(400, '未查询到相关信息，本次将不消耗货卡币！');
        }
    }

    public function car_info(){
        $vid = '1194039326092323738';
        $this->load->library('zhiyun');
        $data = $this->zhiyun->get_driver_info($vid);
        var_dump($data);
    }

    public function my_seek(){
        $str = '&lon=114.30731&lat=34.79726&type=1&belCity=豫';
        $this->load->library('zhiyun');
        var_dump($this->zhiyun->get_car_info($str));
    }

    //获取志云平台token
    /*public function get_token(){
        $this->load->library('zhiyun');
        echo $this->zhiyun->get_token();
    }*/



    /*
     * $openid, $body, $out_trade_no, $total_fee, $notify_url, $trade_type = "JSAPI"
     * 统一下单接口 生成预支付id 并创建js签名
     * return $options
     */

    private function get_options($openid, $body, $out_trade_no, $total_fee, $notify_url, $trade_type = "JSAPI")
    {
        // 实例支付接口
        $pay = &load_wechat('Pay');


        // 获取预支付ID
        $result = $pay->getPrepayId($openid, $body, $out_trade_no, $total_fee, $notify_url, $trade_type);

        // 处理创建结果
        if ($result === FALSE) {
            // 接口失败的处理
            return false;
        } else {
            // 接口成功的处理
            $this->session->set_tempdata('prepayid', $result);
            $prepayid = $result;
        }


        return $options = $pay->createMchPay($prepayid);
    }



    /*
     * notify，微信支付完成回调接口
     * 该方法由微信调用，将不能使用充钱用户的session，所以不能用$this->id
     * */
    public function notify($id)
    {
        // 实例支付接口
        $pay = & load_wechat('Pay');

        // 获取支付通知
        $notifyInfo = $pay->getNotify();

        // 支付通知数据获取失败
        if($notifyInfo===FALSE){
            // 接口失败的处理
            echo $pay->errMsg;
        }else{
            //支付通知数据获取成功
            if ($notifyInfo['result_code'] == 'SUCCESS' && $notifyInfo['return_code'] == 'SUCCESS') {
                // 支付状态完全成功，可以更新订单的支付状态了
                $order = $this->monitor_model->get_order_info(array('transaction_id'=>$notifyInfo['transaction_id'])); //获取订单信息
                if(empty($order)){ //没有该订单，则存入数据库
                    $this->create_order($notifyInfo);
                    $user = $this->monitor_model->get_user_info(array('id'=>$id))[0];
                    //更新用户货卡币数量
                    $recharged_money = $user['money'] + ($notifyInfo['total_fee']/100);
                    $pay_total = $user['pay_total'] + ($notifyInfo['total_fee']/100);
                    $this->db->update('monitor_user', array('money'=>$recharged_money, 'pay_total'=>$pay_total), array('id'=>$id));
                }
                // @todo
                // 返回XML状态，至于XML数据可以自己生成，成功状态是必需要返回的。
                ob_clean();
                exit('<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>');
            }
        }
    }

    //生成订单，存入数据库
    private function create_order($data){
        $data_order = array(
            'total_fee' => $data['total_fee'],
            'transaction_id' => $data['transaction_id'],
            'openid'=>$data['openid'],
            'time_end' =>$data['time_end']
        );
        return $this->db->insert('monitor_pay', $data_order);
    }
}