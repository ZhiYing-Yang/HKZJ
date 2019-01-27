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
      //  $this->session->set_userdata('monitor_user_id', 3); //测试用户
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
        // $status = '{"result":{"adr":"安徽省安庆市怀宁县长琳塑业，向西方向，148 米","drc":"225","lat":"18451089","lon":"70094469","spd":"73.0","utc":"1536049439000","province":"安徽省","city":"安庆市","country":"怀宁县"},"status":1001}';
        // $arr    = json_decode($status, true)['result'];
        // $data = array(
        //        'license_number' => $car_num,
        //        'time' => date('Y-m-d H:i:s', substr($arr['utc'], 0, -3)),
        //        'lon' => $arr['lon'],
        //        'lat' => $arr['lat'],
        //        'spd' => $arr['spd'],
        //        'address' => $arr['adr'],
        //        'charge' => $charge,
        //    );
        //    get_json(200, '查询成功', $data); die;
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
        //echo $this->input->post('str') ; die ;
        //这里已经拿到了str
        $str = $this->input->post('str') ;
        $belong = $this->input->post('belong');
        //测试数据
      // $str = "&lon=114.30731&lat=34.79726&dist=1000";
        if(empty($str)&&empty($belong)){ //找车页面
            $this->load->view('monitor/seek_car.html', $data);
        }else{ //调用接口获取附近车辆信息
            $belong = $this->input->post("belong");;
            $str = $this->input->post('str');
            $data['str'] = $str;
            $pageNum = $this->input->post('pageNum');
            $pageNum = empty($pageNum)?1:$pageNum;
            $data['page'] = $pageNum;
            $str .='&pageNum='.$pageNum;
            //echo $str;
            $this->load->library('zhiyun');
            $data['car'] = $this->zhiyun->get_car_infoV3($str);
            $newcar=[];
            if(!empty($belong)){//所属地填上了
              $car_license_flag  = $this->get_car_license_flag($belong);
              $cars = $data['car'];
              foreach ($cars as $key => $value) {
                if(preg_match("/^".$car_license_flag.".*/" , $value["vno"])==1 ){
                  $newcar[]=$value;
                }
              }
              $data['car'] = $newcar;
            }
            if(!is_array($data['car'])){
                $data['car'] = array();
            }
            $data["car"] =json_encode($data["car"]);
          //  var_dump($data['car']);//测试接口
            //print_r($data['car']);
            $this->load->view('monitor/car_info.html', $data);
        }
    }
    public function get_car_license_flag($value='')//拿到所属地的代表车牌
    {
      $plate_city = array (
   '石家庄市'    =>   '冀A' ,
   '郑州市'    =>   '豫A' ,
   '昆明市'    =>   '云A' ,
   '唐山市'    =>   '冀B' ,
   '开封市'    =>   '豫B' ,
  '东川区'    =>   '云' ,
   '秦皇岛市'    =>   '冀C' ,
   '洛阳市'    =>   '豫C' ,
   '昭通市'    =>   '云C' ,
   '邯郸市'    =>   '冀D' ,
   '平顶山市'    =>   '豫D' ,
   '曲靖市'    =>   '云D' ,
   '邢台市'    =>   '冀E' ,
   '安阳市'    =>   '豫E' ,
   '楚雄彝族自治州'    =>   '云E' ,
   '保定市'    =>   '冀F' ,
   '鹤壁市'    =>   '豫F' ,
   '玉溪市'    =>   '云F' ,
   '张家口市'    =>   '冀G' ,
   '新乡市'    =>   '豫G' ,
   '红河哈尼族彝族自治州'    =>   '云G' ,
   '承德市'    =>   '冀H' ,
   '焦作市'    =>   '豫H' ,
   '文山壮族苗族自治州'    =>   '云H' ,
   '沧州市'    =>   '冀J' ,
   '濮阳市'    =>   '豫J' ,
   '思茅区'    =>   '云J' ,
   '廊坊市'    =>   '冀R' ,
   '许昌市'    =>   '豫K' ,
   '西双版纳傣族自治州'    =>   '云K' ,
   '沧州市'    =>   '冀S' ,
   '漯河市'    =>   '豫L' ,
   '大理白族自治州'    =>   '云L' ,
   '衡水市'    =>   '冀T' ,
   '三门峡市'    =>   '豫M' ,
   '保山市'    =>   '云M' ,
   '商丘市'    =>   '豫N' ,
   '德宏傣族景颇族自治州'    =>   '云N' ,
   '周口市'    =>   '豫P' ,
   '丽江市'    =>   '云P' ,
   '驻马店市'    =>   '豫Q' ,
   '怒江傈僳族自治州'    =>   '云Q' ,
   '南阳市'    =>   '豫R' ,
   '迪庆藏族自治州'    =>   '云R' ,
   '信阳市'    =>   '豫S' ,
   '临沧市'    =>   '云S' ,
   '济源市'    =>   '豫U' ,
   '沈阳市'    =>   '辽A' ,
   '哈尔滨市'    =>   '黑A' ,
   '长沙市'    =>   '湘A' ,
   '大连市'    =>   '辽B' ,
   '齐齐哈尔市'    =>   '黑B' ,
   '株洲市'    =>   '湘B' ,
   '鞍山市'    =>   '辽C' ,
   '牡丹江市'    =>   '黑C' ,
   '湘潭市'    =>   '湘C' ,
   '抚顺市'    =>   '辽D' ,
   '佳木斯市'    =>   '黑D' ,
   '衡阳市'    =>   '湘D' ,
   '本溪市'    =>   '辽E' ,
   '大庆市'    =>   '黑E' ,
   '邵阳市'    =>   '湘E' ,
   '丹东市'    =>   '辽F' ,
   '伊春市'    =>   '黑F' ,
   '岳阳市'    =>   '湘F' ,
   '锦州市'    =>   '辽G' ,
   '鸡西市'    =>   '黑G' ,
   '张家界市'    =>   '湘G' ,
   '营口市'    =>   '辽H' ,
   '鹤岗市'    =>   '黑H' ,
   '益阳市'    =>   '湘H' ,
   '阜新市'    =>   '辽J' ,
   '双鸭山市'    =>   '黑J' ,
   '常德市'    =>   '湘J' ,
   '辽阳市'    =>   '辽K' ,
   '七台河市'    =>   '黑K' ,
   '娄底市'    =>   '湘K' ,
   '盘锦市'    =>   '辽L' ,
   '哈尔滨市'    =>   '黑L' ,
   '郴州市'    =>   '湘L' ,
   '铁岭市'    =>   '辽M' ,
   '绥化市'    =>   '黑M' ,
   '永州市'    =>   '湘M' ,
   '朝阳市'    =>   '辽N' ,
   '黑河市'    =>   '黑N' ,
   '怀化市'    =>   '湘N' ,
   '葫芦岛市'    =>   '辽P' ,
   '大兴安岭地区'    =>   '黑P' ,
   '湘西土家族苗族自治州'    =>   '湘U' ,
   '农垦系统'    =>   '黑R' ,
   '合肥市'    =>   '皖A' ,
   '济南市'    =>   '鲁A' ,
   '乌鲁木齐市'    =>   '新A' ,
   '芜湖市'    =>   '皖B' ,
   '青岛市'    =>   '鲁B' ,
   '昌吉回族自治州'    =>   '新B' ,
   '蚌埠市'    =>   '皖C' ,
   '淄博市'    =>   '鲁C' ,
   '石河子市'    =>   '新C' ,
   '淮南市'    =>   '皖D' ,
   '枣庄市'    =>   '鲁D' ,
   '奎屯市'    =>   '新D' ,
   '马鞍山市'    =>   '皖E' ,
   '东营市'    =>   '鲁E' ,
   '博尔塔拉蒙古自治州'    =>   '新E' ,
   '淮北市'    =>   '皖F' ,
   '烟台市'    =>   '鲁F' ,
   '伊犁哈萨克自治州'    =>   '新F' ,
   '铜陵市'    =>   '皖G' ,
   '潍坊市'    =>   '鲁G' ,
   '塔城地区'    =>   '新G' ,
   '安庆市'    =>   '皖H' ,
   '济宁市'    =>   '鲁H' ,
   '阿勒泰地区'    =>   '新H' ,
   '黄山市'    =>   '皖J' ,
   '泰安市'    =>   '鲁J' ,
   '克拉玛依市'    =>   '新J' ,
   '阜阳市'    =>   '皖K' ,
   '威海市'    =>   '鲁K' ,
   '吐鲁番地区'    =>   '新K' ,
   '宿州市'    =>   '皖L' ,
   '日照市'    =>   '鲁L' ,
   '哈密地区'    =>   '新L' ,
   '滁州市'    =>   '皖M' ,
   '滨州市'    =>   '鲁M' ,
   '巴音郭愣蒙古自治州'    =>   '新M' ,
   '六安市'    =>   '皖N' ,
   '德州市'    =>   '鲁N' ,
   '阿克苏地区'    =>   '新N' ,
   '宣城市'    =>   '皖P' ,
   '聊城市'    =>   '鲁P' ,
   '克孜勒苏柯尔克孜自治州'    =>   '新P' ,
   '巢湖市'    =>   '皖Q' ,
   '临沂市'    =>   '鲁Q' ,
   '喀什地区'    =>   '新Q' ,
   '池州市'    =>   '皖R' ,
   '菏泽市'    =>   '鲁R' ,
   '和田地区'    =>   '新R' ,
   '亳州市'    =>   '皖S' ,
   '莱芜市'    =>   '鲁S' ,
   '青岛市'    =>   '鲁U' ,
   '潍坊市'    =>   '鲁V' ,
   '烟台市'    =>   '鲁Y' ,
   '南京市'    =>   '苏A' ,
   '杭州市'    =>   '浙A' ,
   '南昌市'    =>   '赣A' ,
   '无锡市'    =>   '苏B' ,
   '宁波市'    =>   '浙B' ,
   '赣州市'    =>   '赣B' ,
   '徐州市'    =>   '苏C' ,
   '温州市'    =>   '浙C' ,
   '宜春市'    =>   '赣C' ,
   '常州市'    =>   '苏D' ,
   '绍兴市'    =>   '浙D' ,
   '吉安市'    =>   '赣D' ,
   '苏州市'    =>   '苏E' ,
   '湖州市'    =>   '浙E' ,
   '上饶市'    =>   '赣E' ,
   '南通市'    =>   '苏F' ,
   '嘉兴市'    =>   '浙F' ,
   '抚州市'    =>   '赣F' ,
   '连云港市'    =>   '苏G' ,
   '金华市'    =>   '浙G' ,
   '九江市'    =>   '赣G' ,
   '淮安市'    =>   '苏H' ,
   '衢州市'    =>   '浙H' ,
   '景德镇市'    =>   '赣H' ,
   '盐城市'    =>   '苏J' ,
   '台州市'    =>   '浙J' ,
   '萍乡市'    =>   '赣J' ,
   '扬州市'    =>   '苏K' ,
   '丽水市'    =>   '浙K' ,
   '新余市'    =>   '赣K' ,
   '镇江市'    =>   '苏L' ,
   '舟山市'    =>   '浙L' ,
   '鹰潭市'    =>   '赣L' ,
   '泰州市'    =>   '苏M' ,
   '南昌市'    =>   '赣M' ,
   '宿迁市'    =>   '苏N' ,
   '武汉市'    =>   '鄂A' ,
   '南宁市'    =>   '桂A' ,
   '兰州市'    =>   '甘A' ,
   '黄石市'    =>   '鄂B' ,
   '柳州市'    =>   '桂B' ,
   '嘉峪关市'    =>   '甘B' ,
   '十堰市'    =>   '鄂C' ,
   '桂林市'    =>   '桂C' ,
   '金昌市'    =>   '甘C' ,
   '荆州市'    =>   '鄂D' ,
   '梧州市'    =>   '桂D' ,
   '白银市'    =>   '甘D' ,
   '宜昌市'    =>   '鄂E' ,
   '北海市'    =>   '桂E' ,
   '天水市'    =>   '甘E' ,
   '襄樊市'    =>   '鄂F' ,
   '崇左市'    =>   '桂F' ,
   '酒泉市'    =>   '甘F' ,
   '鄂州市'    =>   '鄂G' ,
   '来宾市'    =>   '桂G' ,
   '张掖市'    =>   '甘G' ,
   '荆门市'    =>   '鄂H' ,
   '桂林市'    =>   '桂H' ,
   '武威市'    =>   '甘H' ,
   '黄冈市'    =>   '鄂J' ,
   '贺州市'    =>   '桂J' ,
   '定西市'    =>   '甘J' ,
   '孝感市'    =>   '鄂K' ,
   '玉林市'    =>   '桂K' ,
   '陇南市'    =>   '甘K' ,
   '咸宁市'    =>   '鄂L' ,
   '百色市'    =>   '桂L' ,
   '平凉市'    =>   '甘L' ,
   '仙桃市'    =>   '鄂M' ,
   '河池市'    =>   '桂M' ,
   '庆阳市'    =>   '甘M' ,
   '潜江市'    =>   '鄂N' ,
   '钦州市'    =>   '桂N' ,
   '临夏回族自治州'    =>   '甘N' ,
   '神农架林区'    =>   '鄂P' ,
   '防城港市'    =>   '桂P' ,
   '甘南藏族自治州'    =>   '甘P' ,
   '恩施土家族苗族自治州'    =>   '鄂Q' ,
   '贵港市'    =>   '桂R' ,
   '天门市'    =>   '鄂R' ,
   '随州市'    =>   '鄂S' ,
   '太原市'    =>   '晋A' ,
   '呼和浩特市'    =>   '蒙A' ,
   '西安市'    =>   '陕A' ,
   '大同市'    =>   '晋B' ,
   '包头市'    =>   '蒙B' ,
   '铜川市'    =>   '陕B' ,
   '阳泉市'    =>   '晋C' ,
   '乌海市'    =>   '蒙C' ,
   '宝鸡市'    =>   '陕C' ,
   '长治市'    =>   '晋D' ,
   '赤峰市'    =>   '蒙D' ,
   '咸阳市'    =>   '陕D' ,
   '晋城市'    =>   '晋E' ,
   '呼伦贝尔市'    =>   '蒙E' ,
   '渭南市'    =>   '陕E' ,
   '朔州市'    =>   '晋F' ,
   '兴安盟'    =>   '蒙F' ,
   '汉中市'    =>   '陕F' ,
   '忻州市'    =>   '晋H' ,
   '通辽市'    =>   '蒙G' ,
   '安康市'    =>   '陕G' ,
   '吕梁市'    =>   '晋J' ,
   '锡林郭勒盟'    =>   '蒙H' ,
   '商洛市'    =>   '陕H' ,
   '晋中市'    =>   '晋K' ,
   '乌兰察布市'    =>   '蒙J' ,
   '延安市'    =>   '陕J' ,
   '临汾市'    =>   '晋L' ,
   '鄂尔多斯市'    =>   '蒙K' ,
   '榆林市'    =>   '陕K' ,
   '运城市'    =>   '晋M' ,
   '巴彦淖尔市'    =>   '蒙L' ,
   '杨凌区'    =>   '陕V' ,
   '阿拉善盟'    =>   '蒙M' ,
   '长春市'    =>   '吉A' ,
   '福州市'    =>   '闽A' ,
   '贵阳市'    =>   '贵A' ,
   '吉林市'    =>   '吉B' ,
   '莆田市'    =>   '闽B' ,
   '六盘水市'    =>   '贵B' ,
   '四平市'    =>   '吉C' ,
   '泉州市'    =>   '闽C' ,
   '遵义市'    =>   '贵C' ,
   '辽源市'    =>   '吉D' ,
   '厦门市'    =>   '闽D' ,
   '铜仁地区'    =>   '贵D' ,
   '通化市'    =>   '吉E' ,
   '漳州市'    =>   '闽E' ,
   '黔西南布依族苗族自治州'    =>   '贵E' ,
   '白山市'    =>   '吉F' ,
   '龙岩市'    =>   '闽F' ,
   '毕节地区'    =>   '贵F' ,
   '白城市'    =>   '吉G' ,
   '三明市'    =>   '闽G' ,
   '安顺市'    =>   '贵G' ,
   '延边朝鲜族自治州'    =>   '吉H' ,
   '南平市'    =>   '闽H' ,
   '黔东南苗族侗族自治州'    =>   '贵H' ,
   '松原市'    =>   '吉J' ,
   '宁德市'    =>   '闽J' ,
   '黔南布依族苗族自治州'    =>   '贵J' ,
   '长白朝鲜族自治县'    =>   '吉K' ,
   '省直系统'    =>   '闽K' ,
   '广州市'    =>   '粤A' ,
   '成都市'    =>   '川A' ,
   '西宁市'    =>   '青A' ,
   '深圳市'    =>   '粤B' ,
   '绵阳市'    =>   '川B' ,
   '海东地区'    =>   '青B' ,
   '珠海市'    =>   '粤C' ,
   '自贡市'    =>   '川C' ,
   '海北藏族自治州'    =>   '青C' ,
   '汕头市'    =>   '粤D' ,
   '攀枝花市'    =>   '川D' ,
   '黄南藏族自治州'    =>   '青D' ,
   '佛山市'    =>   '粤E' ,
   '泸州市'    =>   '川E' ,
   '海南藏族自治州'    =>   '青E' ,
   '韶关市'    =>   '粤F' ,
   '德阳市'    =>   '川F' ,
   '果洛藏族自治州'    =>   '青F' ,
   '湛江市'    =>   '粤G' ,
   '广元市'    =>   '川H' ,
   '玉树藏族自治州'    =>   '青G' ,
   '肇庆市'    =>   '粤H' ,
   '遂宁市'    =>   '川J' ,
   '海西蒙古族藏族自治州'    =>   '青H' ,
   '江门市'    =>   '粤J' ,
   '内江市'    =>   '川K' ,
   '茂名市'    =>   '粤K' ,
   '乐山市'    =>   '川L' ,
   '惠州市'    =>   '粤L' ,
   '资阳市'    =>   '川M' ,
   '梅州市'    =>   '粤M' ,
   '宜宾市'    =>   '川Q' ,
   '汕尾市'    =>   '粤N' ,
   '南充市'    =>   '川R' ,
   '河源市'    =>   '粤P' ,
   '达州市'    =>   '川S' ,
   '阳江市'    =>   '粤Q' ,
   '雅安市'    =>   '川T' ,
   '清远市'    =>   '粤R' ,
   '阿坝藏族羌族自治州'    =>   '川U' ,
   '东莞市'    =>   '粤S' ,
   '甘孜藏族自治州'    =>   '川V' ,
   '中山市'    =>   '粤T' ,
   '凉山彝族自治州'    =>   '川W' ,
   '潮州市'    =>   '粤U' ,
   '广安市'    =>   '川X' ,
   '揭阳市'    =>   '粤V' ,
   '巴中市'    =>   '川Y' ,
   '云浮市'    =>   '粤W' ,
   '眉山市'    =>   '川Z' ,
   '佛山市'    =>   '粤X' ,
   '佛山市'    =>   '粤Y' ,
   '拉萨市'    =>   '藏A' ,
   '海口市'    =>   '琼A' ,
   '银川市'    =>   '宁A' ,
   '昌都地区'    =>   '藏B' ,
   '三亚市'    =>   '琼B' ,
   '石嘴山市'    =>   '宁B' ,
   '山南地区'    =>   '藏C' ,
   '琼海市'    =>   '琼C' ,
   '银南市'    =>   '宁C' ,
   '日喀则地区'    =>   '藏D' ,
   '五指山市'    =>   '琼D' ,
   '固原市'    =>   '宁D' ,
   '那曲地区'    =>   '藏E' ,
   '洋浦开发区'    =>   '琼E' ,
   '中卫市'    =>   '宁E' ,
   '阿里地区'    =>   '藏F' ,
   '林芝地区'    =>   '藏G' ,
   '驻四川省天全县车辆管理所'    =>   '藏H' ,
   '驻青海省格尔木市车辆管理所'    =>   '藏J' ,
   '重庆市'    =>   '渝A' ,
   '北京市'    =>   '京A' ,
   '天津市'    =>   '津A' ,
   '重庆市'    =>   '渝B' ,
   '北京市'    =>   '京B' ,
   '天津市'    =>   '津B' ,
   '永川区'    =>   '渝C' ,
   '北京市'    =>   '京C' ,
   '天津市'    =>   '津C' ,
   '万州区'    =>   '渝F' ,
   '北京市'    =>   '京D' ,
   '天津市'    =>   '津D' ,
   '涪陵区'    =>   '渝G' ,
   '北京市'    =>   '京E' ,
   '天津市'    =>   '津E' ,
   '黔江区'    =>   '渝H' ,
   '北京市'    =>   '京J' ,
   '北京市'    =>   '京K' ,
   '北京市'    =>   '京L' ,
   '北京市'    =>   '京M' ,
   '北京市'    =>   '京Y' ,
   '上海市'    =>   '沪A' ,
   '上海市'    =>   '沪B' ,
   '上海市'    =>   '沪C' ,
   '上海市'    =>   '沪D' ,
   '崇明县'    =>   '沪R' ,
   );
    return $plate_city[$value];
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
      //车辆轨迹
    public function track()
    {
        $etm = $this->input->post("etm");
        $btm = $this->input->post("btm");
        $e = strtotime($etm) ;
        $b = strtotime($btm) ;
        $license = $this->input->post("license");
        $interval = ($e-$b)/60;//分钟

        //扣钱-----------
        $user = $this->monitor_model->get_user_info(array('id' => $this->id))[0];
        if($user['money'] < 1){
            get_json(410, '货卡币余额不足');//410  充钱吧 小伙子
            return;
        }
        $this->db->update("monitor_user",array( "money" => $user["money"]-1  ),array("id"=>$user["id"]) );
        //-------------------
        if($interval<=0){
          get_json(401,"时间间隔不正确");
        }else if($interval>60*24){
          get_json(401 , "时间间隔不能超过24小时");
        }else{
          //格式化
          $etm.=":00";
          $btm.=":00";
          //加载智运库
          $this->load->library("zhiyun");
          $result = $this->zhiyun->get_trace($license,$btm ,$etm);

          if($result["status"]==1001){//成功查到啦
              get_json(200,"ok",$result);die;
          }else{
            get_json(402 ,"找不到结果,可能不存在该车辆" );
          }

        }





    }
}
