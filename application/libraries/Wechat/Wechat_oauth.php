<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 微信网页授权
 * @author SanLingNet <202015066@qq.com>
 * @version 1.0，20171107
 */
require_once(APPPATH.'libraries/Wexin/lib/Wechat_common.php');

class Wechat_oauth extends CI_Wechat_common {

    const OAUTH_PREFIX = 'https://open.weixin.qq.com/connect/oauth2';
    const OAUTH_AUTHORIZE_URL = '/authorize?';
    const OAUTH_TOKEN_URL = '/sns/oauth2/access_token?';
    const OAUTH_REFRESH_URL = '/sns/oauth2/refresh_token?';
    const OAUTH_USERINFO_URL = '/sns/userinfo?';
    const OAUTH_AUTH_URL = '/sns/auth?';

    /**
     * Oauth 授权跳转接口
     * @param string $callback 授权回跳地址
     * @param string $state 为重定向后会带上state参数（填写a-zA-Z0-9的参数值，最多128字节）
     * @param string $scope 授权类类型(可选值snsapi_base|snsapi_userinfo)
     * @return string
     */
    public function getOauthRedirect($callback, $state = '', $scope = 'snsapi_base') {
        $redirect_uri = urlencode($callback);
        return self::OAUTH_PREFIX . self::OAUTH_AUTHORIZE_URL . "appid={$this->appid}&redirect_uri={$redirect_uri}&response_type=code&scope={$scope}&state={$state}#wechat_redirect";
    }

    /**
     * 通过code获取Access Token
     * @return array {access_token,expires_in,refresh_token,openid,scope}
     */
    public function getOauthAccessToken() {
        $code = isset($_GET['code']) ? $_GET['code'] : '';
        if (empty($code)) {
            return false;
        }
        $result = $this->http_get(self::API_BASE_URL_PREFIX . self::OAUTH_TOKEN_URL . "appid={$this->appid}&secret={$this->appsecret}&code={$code}&grant_type=authorization_code");
        if ($result) {
            $json = json_decode($result, true);
            if (empty($json)) {
                $this->errCode = isset($json['errcode']) ? $json['errcode'] : '505';
                $this->errMsg = isset($json['errmsg']) ? $json['errmsg'] : '无法解析接口返回内容！';
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    /**
     * 刷新access token并续期
     * @param string $refresh_token
     * @return boolean|mixed
     */
    public function getOauthRefreshToken($refresh_token) {
        $result = $this->http_get(self::API_BASE_URL_PREFIX . self::OAUTH_REFRESH_URL . "appid={$this->appid}&grant_type=refresh_token&refresh_token={$refresh_token}");
        if ($result) {
            $json = json_decode($result, true);
            if (empty($json) || !empty($json['errcode'])) {
                $this->errCode = isset($json['errcode']) ? $json['errcode'] : '505';
                $this->errMsg = isset($json['errmsg']) ? $json['errmsg'] : '无法解析接口返回内容！';
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    /**
     * 获取授权后的用户资料
     * @param string $access_token
     * @param string $openid
     * @return array {openid,nickname,sex,province,city,country,headimgurl,privilege,[unionid]}
     * 注意：unionid字段 只有在用户将公众号绑定到微信开放平台账号后，才会出现。建议调用前用isset()检测一下
     */
    public function getOauthUserinfo($access_token, $openid) {
        $result = $this->http_get(self::API_BASE_URL_PREFIX . self::OAUTH_USERINFO_URL . "access_token={$access_token}&openid={$openid}");
        if ($result) {
            $json = json_decode($result, true);
            if (empty($json) || !empty($json['errcode'])) {
                $this->errCode = isset($json['errcode']) ? $json['errcode'] : '505';
                $this->errMsg = isset($json['errmsg']) ? $json['errmsg'] : '无法解析接口返回内容！';
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    /**
     * 检验授权凭证是否有效
     * @param string $access_token
     * @param string $openid
     * @return boolean 是否有效
     */
    public function getOauthAuth($access_token, $openid) {
        $result = $this->http_get(self::API_BASE_URL_PREFIX . self::OAUTH_AUTH_URL . "access_token={$access_token}&openid={$openid}");
        if ($result) {
            $json = json_decode($result, true);
            if (empty($json) || !empty($json['errcode'])) {
                $this->errCode = isset($json['errcode']) ? $json['errcode'] : '505';
                $this->errMsg = isset($json['errmsg']) ? $json['errmsg'] : '无法解析接口返回内容！';
                return false;
            } else if ($json['errcode'] == 0) {
                return true;
            }
        }
        return false;
    }

}
