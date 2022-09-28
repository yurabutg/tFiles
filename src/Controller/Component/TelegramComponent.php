<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use \Cake\Core\Configure;
use Cake\Http\Client;
use CURLFile;


class TelegramComponent extends Component
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->telegram_token = Configure::read('telegram_token');
        $this->send_msg_url = 'https://api.telegram.org/bot' . $this->telegram_token . '/sendMessage';
        $this->send_file_url = 'https://api.telegram.org/bot' . $this->telegram_token . '/sendDocument';
        $this->get_file_path_url = 'https://api.telegram.org/bot' . $this->telegram_token . '/getFile';
        $this->get_file = 'https://api.telegram.org/file/bot' . $this->telegram_token . '/';

    }

    public function sendMessage($chat_id, $message = null, $parse_mode = 'html')
    {
        if (is_null($message)) $message = 'Welcome!';
        $data = [
            "chat_id" => $chat_id,
            "text" => $message,
            "parse_mode" => $parse_mode
        ];
        $this->_sendRequest($this->send_msg_url, $data);
    }

    public function sendFile($file_path, $telegram_id)
    {
        $filepath = realpath($file_path);
        $post = ['chat_id' => $telegram_id, 'document' => new CurlFile($filepath)];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $this->send_file_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output, true);
    }

    public function getFilePath($file_id)
    {
        $data = ["file_id" => $file_id];
        return $this->_sendRequest($this->get_file_path_url, $data);
    }

    public function getFile($telegram_file_path)
    {
        $dir = '_tmp/' . randomString(8) . DS;
        $save_file_loc = $this->createTmpFile($telegram_file_path, $dir);

        if (file_exists($save_file_loc)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($save_file_loc) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($save_file_loc));
            readfile($save_file_loc);
            $this->deleteFile($save_file_loc, $dir);
            exit;
        }
    }

    public function getImageBase64Preview($file_path, $ext)
    {
        $image = null;
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($file_path);
                break;
            case 'png':
                $image = imagecreatefrompng($file_path);
                break;
            case 'webp':
                $image = imagecreatefromwebp($file_path);
                break;
            default:
                break;
        }

        if (!is_null($image)) {
            $preview = imagescale($image, 400);
            ob_start();
            imagepng($preview);
            $contents = ob_get_clean();
            $preview_base64 = 'data:image/png;base64,' . base64_encode($contents);
            imagedestroy($image);
            imagedestroy($preview);
            return $preview_base64;
        } else return null;
    }

    private function _sendRequest($url, $content)
    {
        $options = ['http' => ['method' => 'POST', 'header' => "Content-Type:application/x-www-form-urlencoded\r\n", 'content' => http_build_query($content)]];
        $context = stream_context_create($options);
        return json_decode(file_get_contents($url, false, $context), JSON_FORCE_OBJECT);
    }

    public function createTmpFile($telegram_file_path, $dir)
    {
        $url = $this->get_file . $telegram_file_path;
        $ch = curl_init($url);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $file_name = basename($url);
        $save_file_loc = $dir . $file_name;

        $fp = fopen($save_file_loc, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        return $save_file_loc;
    }

    public function deleteFile($file_path = null, $dir = null)
    {
        if (!is_null($file_path)) unlink($file_path);
        if (!is_null($dir)) rmdir($dir);
    }
}

