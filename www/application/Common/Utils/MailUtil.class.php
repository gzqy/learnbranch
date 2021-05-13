<?php
namespace Common\Utils;
/**
 * 邮件发送util
 */
class MailUtil {
    private $header;
    private $body;
    private $clients;
    private $instance;
    private $conf;
    public function __construct(){
        require_once './simplewind/Lib/Util/class.phpmailer.php';
        require_once './simplewind/Lib/Util/class.smtp.php';
        $this -> instance = new \PHPMailer();
        $this -> conf = C('smtp_mail');
    }

    public function setHeader($header){
        if(!$header){
            return false;
        }
        $this -> header = $header;
    }

    public function setBody($body){
        if(!$body){
            return false;
        }
        $this -> body = $body;
    }

    public function setClients(array $clients){
        if(!$clients){
            return false;
        }
        foreach($clients as $k=>$v){
            $is = $this->chkMail($v['email']);
            if($is){
                $this->instance->addAddress($v['email'],'VAA');
            }
        }
        return true;
    }
    
    /**
     * 邮件发送
     */
	public function send() {
        $mail = $this->instance;

        $mail->Helo= "test";
        $mail->SMTPDebug = 1;
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = $this->conf['server'];
        $mail->SMTPSecure = 'ssl';
        $mail->Port = $this->conf['port'];
        $mail->CharSet = 'UTF-8';
        
        $mail->Username = $this->conf['user'];
        $mail->Password = $this->conf['pass'];
        $mail->FromName = $this->conf['from']['name'];
        $mail->From = $this->conf['from']['mail'];
        $mail->isHTML(true);
        $mail->Subject = $this->header;
        $mail->Body = $this->body;
        $status = $mail->send();
        if ($status) {
            return true;
        } else {
            echo '发送邮件失败，错误信息未：' . $mail->ErrorInfo;exit;
            return false;
        }
        exit;
    }

    public function send_back() {
        $mail = $this->instance;
        $mail->SMTPDebug = 1;
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = $this->conf['server'];
        if ( $this->conf['port'] == 465 ) {
             $mail->SMTPSecure = 'ssl';
        }
        $mail->Port = $this->conf['port'];
        $mail->Hostname = 'qq.com';
        $mail->CharSet = 'UTF-8';
        
        $mail->Username = $this->conf['user'];
        $mail->Password = $this->conf['pass'];
        $mail->FromName = $this->conf['from']['name'];
        $mail->From = $this->conf['from']['mail'];
        $mail->isHTML(true);
        $mail->Subject = $this->header;
        $mail->Body = $this->body;
        $status = $mail->send();
        if ($status) {
            return true;
        } else {
            // echo '发送邮件失败，错误信息未：' . $mail->ErrorInfo;exit;
            return false;
        }
        exit;
    }

     /**
     * 邮箱格式检测
     */
    function chkMail($mail) {
        if ( empty($mail) ) {
            return false;
        }
        $pattern = "/\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/";
        if (preg_match($pattern,$mail)) {
            return true;
        }
        return false;
    }
}