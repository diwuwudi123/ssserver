<?php


namespace App\Services;

use App\Models\PasswordReset;
use App\Utils\Tools;

/***
 * Class Password
 * @package App\Services
 */
class Password
{
    const REST_URL = 'http://api.sendcloud.net/apiv2/mail/send';

    /**
     * @param $email string
     * @return bool
     */
    public static function sendResetEmail($email, $user)
    {
        $pwdRst = new PasswordReset();
        $pwdRst->email = $email;
        $pwdRst->init_time = time();
        $pwdRst->expire_time = time() + 3600 * 24; // @todo
        $pwdRst->token = Tools::genRandomChar(64);
        if (!$pwdRst->save()) {
            return false;
        }
        $subject  = Config::get('appName') . "重置密码,你是猪吗,竟然把密码都忘了";
        $rest_url = Config::get('baseUrl') . "/password/token/" . $pwdRst->token;
        $user_name= $user->user_name;
        $html     = "<p>&nbsp;</p>

                <p>哈哈哈 ".$user_name."&nbsp;你是猪吗 竟然把网站的密码忘了</p>

                <p>还好我大发慈悲的告诉你</p>

                <p>点击它--------->>>>>>> {$rest_url} <<<<<<<---------点击它 </p>

                <p>哈哈哈哈哈哈哈哈哈哈哈哈哈哈哈哈哈哈哈哈哈哈</p>

                <p>你是猪吗 这次改完密码千万要记住了</p>";

        if (self::send_mail($email, $subject, $html)) {
            return true;
        }
        return false;
    }

    public static function resetBy($token, $password)
    {

    }
    public static function send_mail($to_user, $subject, $html)
    {
        $post_data = [
            'apiUser'               => Config::get('apiUser'),
            'apiKey'                => Config::get('apiKey'),
            'from'                  => Config::get('mail_from'),
            'to'                    => $to_user,
            'subject'               => $subject,
            'html'                  => $html,
        ];
        $rest_url = Config::get('rest_url');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $rest_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $result = curl_exec($ch);
        curl_close($ch);
        //打印获得的数据
        $result = json_decode($result);
        if ($result && $result->result === true && $result->statusCode == 200) 
        {
            return true;
        }
        return false;
    }
}
