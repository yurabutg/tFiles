<?php

namespace App\Model\Table;

use App\Controller\Component\ProtectionComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\ORM\Table;

class RecordsTable extends Table
{
    public $model = 'Records';
    public $contain = ['RecordsTypes'];

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->Protection = new ProtectionComponent(new ComponentRegistry(), []);

        $this->hasOne('RecordsTypes', [
            'className' => 'RecordsTypes',
            'foreignKey' => 'id',
            'bindingKey' => 'type_id',
            'propertyName' => 'type',
        ]);
    }

    public function saveRecord($data = null)
    {
        if (!is_null($data)) {
            $entity = $this->newEmptyEntity();
            $entity->link_token = $this->_createLinkToken();
            $entity->token = randomString(60);
            $entity->pin_tmp = randomPin();
            $entity->pin = $this->Protection->getHash($entity->pin_tmp);
            $entity->created = time();
            $entity->expiration_time = $entity->created + Configure::read('record_lifetime');

            if (isset($data['text']) && !empty($data['text'])) $data['text'] = $this->Protection->encrypt($data['text']);
            if (isset($data['telegram_file_id']) && !empty($data['telegram_file_id'])) $data['telegram_file_id'] = $this->Protection->encrypt($data['telegram_file_id']);
            if (isset($data['file_name']) && !empty($data['file_name'])) $data['file_name'] = $this->Protection->encrypt($data['file_name']);
            if (isset($data['file_preview']) && !empty($data['file_preview'])) $data['file_preview'] = $this->Protection->encrypt($data['file_preview']);

            foreach ($data as $key => $value) $entity->{$key} = $value;

            return $this->save($entity)->toArray();

        } else return false;
    }

    public function incrementPinAttemptsCount($id)
    {
        $entity = $this->get($id);
        $entity->pin_attempts_count = $entity->pin_attempts_count + 1;
        $this->save($entity);
    }

    public function resetPinAttemptsCount($id)
    {
        $entity = $this->get($id);
        $entity->pin_attempts_count = 0;
        $this->save($entity);
    }

    public function getByLinkToken($link_token = null)
    {
        if (!is_null($link_token)) {
            $options = [
                'conditions' => [
                    $this->model . '.link_token' => $link_token
                ],
                'contain' => $this->contain
            ];
            $results = $this->find('all', $options)->first();
            if (!empty($results)) return $results->toArray();
            else return [];
        } else return [];
    }

    public function getByToken($token = null)
    {
        if (!is_null($token)) {
            $options = [
                'conditions' => [
                    $this->model . '.token' => $token
                ],
                'contain' => $this->contain
            ];
            $results = $this->find('all', $options)->first();
            if (!empty($results)) return $results->toArray();
            else return [];
        } else return [];
    }

    public function deleteByToken($token)
    {
        return ($this->deleteAll([$this->model . '.token' => $token]));
    }

    public function deleteByUserId($user_id)
    {
        return ($this->deleteAll([$this->model . '.user_id' => $user_id]));
    }

    public function deleteExpired()
    {
        return ($this->deleteAll([$this->model . '.expiration_time <' => time()]));
    }

    public function checkByLinkToken($link_token)
    {
        $options = [
            'conditions' => [
                $this->model . '.link_token' => $link_token
            ]
        ];
        return ($this->find('all', $options)->count() > 0);
    }

    private function _createLinkToken()
    {
        $length = 5;
        while ($length < 10) {
            $try_count = 100;
            while ($try_count > 0) {
                $token = randomString($length);
                if (!$this->checkByLinkToken($token)) return $token;
                else $try_count -= 1;
            }
            $length += 1;
        }
        return false;
    }
}
