<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/2
 * Time: 10:39
 */
class Monitor extends CI_Controller {
    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $data=array(
            'url' => site_url('monitor/index'),
            'timestamp' => time(),
            'noncestr' => 'Wm3WZYTPz0wzccnW',
            'appid' => $this->config->item('wechat_appid'),
        );
        $data = $this->get_signature($data);
        $this->load->view('monitor/index.html', $data);
    }

    //获取车辆的具体位置
    public function car_monitor(){
        $car_num = $this->input->post('license_number');

        $charge = 0; //免费查询
        $status = '{"result":{"adr":"安徽省安庆市怀宁县长琳塑业，向西方向，148 米","drc":"225","lat":"18451089","lon":"70094469","spd":"73.0","utc":"1536049439000","province":"安徽省","city":"安庆市","country":"怀宁县"},"status":1001}';
        $arr = json_decode($status, true)['result'];

        /*$this->load->library('zhiyun');
        $arr = $this->zhiyun->get_location($car_num);*/

        if(is_array($arr)){
            $data = array(
                'license_number'=>$car_num,
                'time'=>date('Y-m-d H:i:s', substr($arr['utc'], 0, -3)),
                'lon'=>$arr['lon'],
                'lat'=>$arr['lat'],
                'spd'=>$arr['spd'],
                'address'=>$arr['adr'],
                'charge'=>$charge,
            );
            get_json(200, '查询成功', $data);
        }elseif($arr == '无结果'){
            get_json(401, '无结果');
        }else{
            get_json(400, '当前查询人数较多，请稍后再试');
        }


    }
    public function ceshi(){
        /*$this->load->library('zhiyun');
        var_dump($this->zhiyun->get_location('赣C1N980'));*/
    }

    public function get_token(){
        $this->load->library('zhiyun');
        $result = $this->zhiyun->login(); //类型{"result":"b658f70f-f694-47ee-8da7-e083762875e9","status":1001}
        $result = json_decode($result, true);
        if($result['status'] == 1001){
            $this->db->update('zhiyun_config', array('token'=>$result['result']), array('id'=>1));
            return $result['result'];
        }
        return false;
    }

    //获取微信js-sdk签名
    private function get_signature($data){
        // 创建SDK实例
        $script = &  load_wechat('Script');

        // 获取JsApi使用签名，通常这里只需要传 $ur l参数
        $options = $script->getJsSign($data['url'], $data['timestamp'], $data['noncestr'], $data['appid']);

        // 处理执行结果
        if($options===FALSE){
            // 接口失败的处理
            echo $script->errMsg;
        }else{
            return $options;
        }
    }
}