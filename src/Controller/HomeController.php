<?php

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Cookie\Cookie;
use Cake\Utility\Inflector;

class HomeController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Security');
        $this->loadComponent('Protection');

        $this->_setTextVariables();
        $this->reCaptcha_site_key = Configure::read('reCaptcha_site_key');
        $this->reCaptcha_secret_key = Configure::read('reCaptcha_secret_key');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $this->Security->setConfig('unlockedActions', ['telegram', 'saveTelegramAuthUser', 'deleteTelegramAuthUser']);
    }

    public function welcome()
    {
        $this->set('result', $this->welocome_page_msg);
    }

    public function index($link_token = null)
    {
        if (!is_null($link_token)) {

            $record = $this->records_table->getByLinkToken($link_token);

            if (empty($record)) {
                $this->Flash->error($this->text_wrong_link);
                $this->redirect(['controller' => 'Home', 'action' => 'welcome']);
            } else {

                /* reCaptcha */
                $this->set('reCaptcha_site_key', $this->reCaptcha_site_key);

                if ($this->request->is('post')) {
                    $data = $this->request->getData();

                    if ($this->_checkReCaptcha($data)) {

                        if (!empty($data['pin'])) {
                            $result = [];

                            /* check pin */
                            if ($this->Protection->checkHash($data['pin'], $record['pin'])) {
                                $this->records_table->resetPinAttemptsCount($record['id']);
                                $result['expiration_time'] = $record['expiration_time'];
                                $result['text'] = (!is_null($record['text'])) ? $this->Protection->decrypt($record['text']) : null;
                                $result['file_name'] = (!is_null($record['file_name'])) ? $this->Protection->decrypt($record['file_name']) : null;
                                $result['file_preview'] = (!is_null($record['file_preview'])) ? $this->Protection->decrypt($record['file_preview']) : null;
                                $result['token'] = $record['token'];
                            } else {
                                if ($record['pin_attempts_count'] >= Configure::read('pin_attempts_count_before_delete')) {
                                    $this->records_table->deleteByToken($record['token']);
                                    $this->redirect(['controller' => 'Home', 'action' => 'welcome']);
                                } else {
                                    $this->Flash->error(sprintf($this->pin_attempts_count_ban_warning, Configure::read('pin_attempts_count_before_delete') - $record['pin_attempts_count']));
                                    $this->records_table->incrementPinAttemptsCount($record['id']);
                                }
                            }
                            $this->set('result', $result);
                        }
                    }
                }
            }
        } else $this->redirect(['controller' => 'Home', 'action' => 'welcome']);
    }

    public function sendText()
    {
        /* reCaptcha */
        $this->set('reCaptcha_site_key', $this->reCaptcha_site_key);

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            if ($this->_checkReCaptcha($data)) {
                if (isset($data['telegram_id']) && !empty($data['telegram_id'])) {
                    $user = $this->users_table->getByTelegramId($data['telegram_id']);
                    if (!empty($user)) {
                        if (isset($data['text']) && !empty($data['text'])) {
                            $record_type = $this->_getMessageType(['text' => true]);
                            $record_data = [
                                'user_id' => $user['id'],
                                'type_id' => $record_type['id'],
                                'text' => $data['text']
                            ];
                            $this->_saveTelegramRecord($record_data, $data['telegram_id']);
                            if (isset($this->result_msg)) $this->Flash->success($this->result_msg, ['escape' => false]);
                            $this->statistics_table->updateMessagesCount();
                            $this->redirect($this->referer());
                        } else $this->Flash->error($this->text_empty_message);
                    } else $this->Flash->error($this->text_wrong_user);
                } else $this->Flash->error($this->text_empty_telegram_id);
            } else $this->getError404();
        }
    }

    public function sendFile()
    {
        /* reCaptcha */
        $this->set('reCaptcha_site_key', $this->reCaptcha_site_key);

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            if ($this->_checkReCaptcha($data)) {

                if (isset($data['telegram_id']) && !empty($data['telegram_id'])) {
                    $user = $this->users_table->getByTelegramId($data['telegram_id']);
                    if (!empty($user)) {

                        if (!empty($_FILES['file'])) {
                            $file = $_FILES['file'];
                            $upload_dir = WWW_ROOT . '_tmp';
                            $tmp_file_name = basename($file['name']);
                            $tmp_file = $upload_dir . DS . $tmp_file_name;
                            if (move_uploaded_file($file['tmp_name'], $tmp_file)) {
                                $result = $this->Telegram->sendFile($tmp_file, $data['telegram_id']);
                                $ext = pathinfo($tmp_file, PATHINFO_EXTENSION);

                                unlink($tmp_file);
                                if (isset($result['ok']) && $result['ok']) {
                                    if (isset($result['result']['document']['file_id']) && !empty($result['result']['document']['file_id'])) {

                                        if (in_array(Inflector::underscore($ext), ['png', 'jpg', 'jpeg', 'webp'])) $record_type = $this->_getMessageType(['photo' => true]);
                                        else $record_type = $this->_getMessageType(['document' => true]);
                                        $record_data = [
                                            'user_id' => $user['id'],
                                            'type_id' => $record_type['id'],
                                            'telegram_file_id' => $result['result']['document']['file_id'],
                                            'file_name' => $tmp_file_name
                                        ];
                                        if ($record_type['type'] == 'photo') {
                                            $record_data['file_preview'] = $this->_getBase64Preview($record_data['telegram_file_id'], $ext);
                                        }
                                        $this->_saveTelegramRecord($record_data, $data['telegram_id']);
                                        if (isset($this->result_msg)) $this->Flash->success($this->result_msg, ['escape' => false]);

                                        $this->statistics_table->updateFilesCount();
                                        $this->statistics_table->updateFilesTotalSize($file['size']);

                                        $this->redirect($this->referer());
                                    } else $this->Flash->error($this->text_error);
                                } else $this->Flash->error($this->text_error);
                            } else $this->Flash->error($this->text_error);
                        } else $this->Flash->error($this->text_empty_file);
                    } else $this->Flash->error($this->text_wrong_user);
                } else $this->Flash->error($this->text_empty_telegram_id);
            } else $this->getError404();
        }
    }

    public function telegram()
    {
        /** Ban condition */
        /** cron delete expired records */

        $this->autoRender = false;

        if ($this->request->is('post')) {

            $commands = ['/start', '/delete_all', '/get_telegram_id'];

            $data = $this->request->getData();

            $telegram_id = $data['message']['from']['id'];

            $record_type = $this->_getMessageType($data['message']);

            if (!is_null($record_type)) {
                $user = $this->users_table->saveRecord(['telegram_id' => $telegram_id]);

                /** Ban check */
                if ($this->users_bans_table->isBaned($user['id'])) {
                    $this->Telegram->sendMessage($telegram_id, $this->text_you_are_banned);
                    exit();
                }

                $message_text = $data['message']['text'];

                if (in_array($message_text, $commands)) {
                    $this->_getCommand($message_text, $telegram_id);
                } elseif ($this->_isHTML($message_text)) $this->Telegram->sendMessage($telegram_id, $this->text_html_non_allowed);
                else {
                    $record_data = [
                        'user_id' => $user['id'],
                        'type_id' => $record_type['id'],
                        'text' => ($record_type['type'] == 'text') ? $message_text : null,
                        'telegram_file_id' => ($record_type['type'] !== 'text') ? $this->_getTelegramFileId($data['message'], $record_type['type']) : null,
                        'file_name' => ($record_type['type'] !== 'text') ? $this->_getTelegramFileName($data['message'], $record_type['type']) : null
                    ];

                    if ($record_type['type'] == 'photo') {
                        $record_data['file_preview'] = $this->_getBase64Preview($record_data['telegram_file_id'], 'jpeg');
                    }
                    $this->_saveTelegramRecord($record_data, $telegram_id);
                    $this->statistics_table->updateMessagesCount();
                }
            }
            exit();
        }
    }

    public function saveTelegramAuthUser()
    {
        $this->autoRender = false;
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            setcookie('tg_user', json_encode($data, JSON_FORCE_OBJECT), time() + (86400 * 10), "/");
        }
        $this->redirect($this->referer());
    }

    public function deleteTelegramAuthUser()
    {
        $this->autoRender = false;
        if ($this->request->is('post')) {
            setcookie('tg_user', null, -1, '/');
        }
        $this->redirect($this->referer());
    }

    public function download($token)
    {
        $this->autoRender = false;
        $record = $this->records_table->getByToken($token);
        if (!empty($record)) {
            $file_id = $this->Protection->decrypt($record['telegram_file_id']);
            $file_path = $this->Telegram->getFilePath($file_id);
            if (!empty($file_path['result']['file_path'])) {
                $this->Telegram->getFile($file_path['result']['file_path']);
            }
        } else $this->getError404();
    }

    public function delete()
    {
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            if (!empty($data['token'])) {
                if ($this->records_table->deleteByToken($data['token'])) {
                    $this->Flash->success($this->text_success);
                } else $this->Flash->error($this->text_error);
            } else $this->Flash->error($this->text_error);
        } else $this->Flash->error($this->text_error);
        $this->redirect(['controller' => 'Home', 'action' => 'welcome']);
    }

    public function comingSoon(){
        $this->viewBuilder()->disableAutoLayout();
    }

    private function _saveTelegramRecord($record_data, $telegram_id)
    {
        $save_record = $this->records_table->saveRecord($record_data);
        $link = $this->_createRecordLink($save_record);
        $this->Telegram->sendMessage($telegram_id, $link . ' PIN: ' . $save_record['pin_tmp']);
        $this->result_msg = '<a href="' . $link . '">' . $link . '</a>' . ' PIN: ' . $save_record['pin_tmp'];
    }

    private function _checkReCaptcha($data)
    {
        if (!empty($data['g-recaptcha-response'])) {
            $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($this->reCaptcha_secret_key) . '&response=' . urlencode($data['g-recaptcha-response']);
            $response = file_get_contents($url);
            $response_keys = json_decode($response, true);
            if (isset($response_keys['success']) && $response_keys['success'] == true) {
                if ($response_keys['score'] >= 0.7) return true;
            }
        }
        return false;
    }

    private function _getMessageType($data)
    {
        $records_types = $this->records_types_table->getList();
        foreach ($records_types as $id => $type) {
            if (isset($data[$type])) return ['id' => $id, 'type' => $type];
        }
        return null;
    }

    private function _createRecordLink($data)
    {
        return Configure::read('https_domain') . DS . $data['link_token'];
    }

    private function _isHTML($string)
    {
        return $string != strip_tags($string);
    }

    private function _getTelegramFileId($data, $type)
    {
        switch ($type) {
            case 'photo':
                $result = end($data[$type]);
                return $result['file_id'];
            case 'voice':
            case 'video':
            case 'sticker':
            case 'animation':
            case 'document':
                return $data[$type]['file_id'];
            case 'contact':
            case 'location':
            default:
                return null;
        }
    }

    private function _getTelegramFileName($data, $type)
    {
        switch ($type) {
            case 'document':
                return $data['document']['file_name'];
            default:
                return $type;
        }
    }

    private function _getCommand($command, $telegram_id)
    {
        switch ($command) {
            case '/start':
                $this->Telegram->sendMessage($telegram_id, $this->text_welcome);
                break;
            case '/delete_all':
                $user = $this->users_table->getByTelegramId($telegram_id);
                if (!empty($user)) {
                    $this->records_table->deleteByUserId($user['id']);
                    $this->Telegram->sendMessage($telegram_id, $this->text_all_records_have_been_deleted_msg);
                }
                break;
            case '/get_telegram_id':
                $this->Telegram->sendMessage($telegram_id, vsprintf($this->text_your_telegram_id, [$telegram_id]));
                break;
            default:
                break;
        }
    }

    private function _getBase64Preview($telegram_file_id, $file_ext)
    {
        $telegram_file_path = $this->Telegram->getFilePath($telegram_file_id);
        if ($telegram_file_path['ok']) {
            $dir = '_tmp/' . randomString(8) . DS;
            $file_full_path = $this->Telegram->createTmpFile($telegram_file_path['result']['file_path'], $dir);
            $base64 = $this->Telegram->getImageBase64Preview($file_full_path, $file_ext);
            $this->Telegram->deleteFile($file_full_path, $dir);
            return $base64;
        } else return null;
    }

    private function _setTextVariables()
    {
        $this->set('text_get_telegram_id', __('Get Telegram ID'));
        $this->set('text_send_text', __('Send text'));
        $this->set('text_send_file', __('Send file'));
        $this->set('text_enter_telegram_id', __('Enter your Telegram ID'));
        $this->set('text_enter_message', __('Enter your Message'));
        $this->set('text_max_file_size', __('Max file size: %s'));
        $this->set('text_download', __('Download'));
        $this->set('text_download_file', __('Download file'));
        $this->set('text_record_expiration', __('Record expiration: '));
        $this->set('text_validate', __('Validate'));
        $this->set('text_pin', __('Pin'));
        $this->set('text_delete_record', __('Delete record'));
        $this->set('text_delete_file', __('Delete file'));
        $this->set('text_file_name', __('File name'));
        $this->set('text_logout', __('Logout'));

        $this->text_wrong_link = __('Not exist');
        $this->text_empty_telegram_id = __('Empty Telegram ID');
        $this->text_empty_message = __('Empty Message');
        $this->text_empty_file = __('Empty file');
        $this->text_wrong_user = __('User does not exist');
        $this->text_your_telegram_id = __('Your Telegram ID: %s');
        $this->text_welcome_msg = __('Welcome');
        $this->text_all_records_have_been_deleted_msg = __('All Records have been deleted');
        $this->text_html_non_allowed = __('HTML not allowed');
        $this->text_you_are_banned = __('You are banned');
        $this->text_error = __('Error');
        $this->text_success = __('Success');
        $this->text_wrong_pin = __('Pin error');
        $this->pin_attempts_count_ban_warning = __('Attention! %s attempts left before delete record');
        $this->welocome_page_msg = __('Welcome to tFiles');
        $this->text_your_caffe_turn = __('It\'s your turn to make coffee');
    }

}
