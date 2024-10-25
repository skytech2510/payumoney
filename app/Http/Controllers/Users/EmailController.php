<?php
/**
 * Email Controller
 */
namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Support\Facades\{Config,
    Mail,
    DB
};
use PHPMailer\PHPMailer\PHPMailer;
use App\Models\EmailConfig;

class EmailController extends Controller
{

    public function sendEmail($to, $subject, $message)
    {
        $mail = new \App\libraries\MailService();
        $data = [];
        $data = array(
            'to'      => array($to),
            'subject' => $subject,
            'content' => $message,
        );
    
        $emailConfig = EmailConfig::where(['email_protocol' => 'smtp', 'status' => '1'])->first();

        if (isset($emailConfig->email_protocol) && $emailConfig->email_protocol == 'smtp')
        {
            $this->setupEmailConfig();
            $mail->CharSet  = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->send($data, 'emails.sendmail');
        }
        else
        {
            $emailInfo = '';
            $this->sendPhpEmail($to, $subject, $message, $emailInfo);
        }
    }

    public function sendEmailWithAttachment($to, $subject, $messageBody, $path, $attachedFile)
    {
        $mail     = new \App\libraries\MailService();
        $dataMail = [];

        $dataMail = array(
            'to'      => array($to),
            'subject' => $subject,
            'content' => $messageBody,
            'attach'  => url('public/' . $path . '/' . $attachedFile),
        );

        $emailConfig = EmailConfig::where(['email_protocol' => 'smtp', 'status' => '1'])->first();
        if (isset($emailConfig->email_protocol) && $emailConfig->email_protocol == 'smtp')
        {
            $this->setupEmailConfig();
            $mail->CharSet  = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->send($dataMail, 'emails.sendmail');
        }
        else
        {
            $emailInfo = '';
            $this->sendPhpEmail($to, $subject, $messageBody, $emailInfo, $path, $attachedFile);
        }
    }

    public function sendPhpEmail($to, $subject, $message, $emailInfo, $path = null, $attachedFile = null)
    {
        require 'vendor/autoload.php';
        $mail = new PHPMailer(true);

        $admin = Admin::where(['status' => 'Active'])->first(['first_name', 'last_name', 'email']);
        if (!empty($admin))
        {
            $mail->From     = $admin->email;
            $mail->FromName = getColumnValue($admin);
            $mail->AddAddress($to, isset($admin) ? $mail->FromName : 'N/A');
            $mail->Subject = $subject;
            $mail->Body    = $message;

            //extra - starts
            $mail->WordWrap = 50;
            $mail->IsHTML(true);
            $mail->CharSet  = 'UTF-8';
            $mail->Encoding = 'base64';
            //extra - ends

            if (!empty($attachedFile))
            {
                $mail->AddAttachment(public_path('/' . $path . '/' . $attachedFile, 'base64'));
            }
            $mail->Send();
        }
    }

    public function setupEmailConfig()
    {
        $emailConfig = EmailConfig::where(['email_protocol' => 'smtp', 'status' => '1'])->first();

        Config::set([
            'mail.driver'     => isset($emailConfig->email_protocol) ? $emailConfig->email_protocol : '',
            'mail.host'       => isset($emailConfig->smtp_host) ? $emailConfig->smtp_host : '',
            'mail.port'       => isset($emailConfig->smtp_port) ? $emailConfig->smtp_port : '',
            'mail.from'       => ['address' => isset($emailConfig->from_address) ? $emailConfig->from_address : '', 'name' => isset($emailConfig->from_name) ? $emailConfig->from_name : ''],
            'mail.encryption' => isset($emailConfig->email_encryption) ? $emailConfig->email_encryption : '',
            'mail.username'   => isset($emailConfig->smtp_username) ? $emailConfig->smtp_username : '',
            'mail.password'   => isset($emailConfig->smtp_password) ? $emailConfig->smtp_password : '',
        ]);
    }
}
