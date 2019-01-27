<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
*     平台活动
*/
/**
 *
 */
class activities extends CI_Controller
{

  function __construct()
  {
    parent::__construct();
  }
  public function index($value='')
  {
    $this->load->view("activity/index.html");
  }
  public function load($value='')
  {
    $index = $this->input->post("index");
    $perpage = $this->input->post("perpage");
    $sql =  $this->db->select("*")->from("activity");
    if($this->input->post("option")){
      $keyword = $this->input->post("option")["keyword"];
      $sql->or_like(array("postname"=>$keyword , "address"=>$keyword));
    }
    $result = $sql->order_by("create_time desc")->limit($perpage,$index)->get()->result_array();
    get_json(200,"" ,json_encode($result));
  }
}
