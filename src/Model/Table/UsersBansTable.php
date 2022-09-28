<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\Table;

class UsersBansTable extends Table
{

    public $model = 'UsersBans';
    public $contain = [];

    public function saveRecord($user_id = null, $expiration = null)
    {
        if (!is_null($user_id)) {
            $record = $this->getByUserId($user_id);
            if (empty($record)) {
                $entity = $this->newEmptyEntity();
                $entity->user_id = $user_id;
                $entity->token = randomString(60);
                $entity->created = time();
            } else $entity = $this->get($record['id']);
            $entity->expiration = (!is_null($expiration)) ? $expiration : time() + Configure::read('default_ban_time');
            return $this->save($entity)->toArray();
        } else return false;
    }

    public function isBaned($user_id = null)
    {
        if (!is_null($user_id)) {
            $record = $this->getByUserId($user_id);
            if (!empty($record)) {
                if (time() > $record['expiration']) return false;
                else return true;
            } else return false;
        } else return null;
    }

    public function getByUserId($user_id = null)
    {
        if (!is_null($user_id)) {
            $options = ['conditions' => [$this->model . '.user_id' => $user_id]];
            $results = $this->find('all', $options)->first();
            if (!empty($results)) return $results->toArray();
            else return [];
        } else return false;
    }
}
