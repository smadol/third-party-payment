<?php
namespace App\Services\Lib;

use Illuminate\Support\Facades\Log;

class WinicSMSService extends SMSAbstract
{
    protected $id;
    protected $pwd;
    protected $url = 'http://service.winic.org:8009/sys_port/gateway/index.asp?';

    public function __construct()
    {
        $this->id = iconv('UTF-8', 'GB2312//IGNORE', config('sms.sms_id'));
        $this->pwd = config('sms.sms_pwd');
    }

    public function sendSMS($to, $content)
    {
        $postData = [
            'id' => $this->id,
            'pwd' => $this->pwd,
            'to' => $to,
            'content' => iconv('UTF-8', 'GB2312//IGNORE', $content)
        ];
        $res = curlHttp($this->url, $postData, 'post');
        if ($res['http_code'] == 200) {
            $sms_code = substr($res['body'], 0, 3);
            if ($sms_code == '000') {
                return true;
            }
        }
        Log::useFiles(storage_path().'/logs/sms/'.date('YmdHis').'.log');
        Log::info("【winic】短信接口出错! \t [http_code]" . $res['http_code'] . "\t[body]:".$res['body']);
        return false;
    }
}
