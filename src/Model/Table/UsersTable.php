<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class UsersTable extends Table
{

    public $model = 'Users';
    public $contain = [];

    public function getCount(){
        return $this->find('all')->count();
    }

    public function saveRecord($data = null)
    {
        if (!is_null($data)) {
            $record = $this->getByTelegramId($data['telegram_id']);
            if (empty($record)) {
                $entity = $this->newEmptyEntity();
                $entity->token = randomString(60);
                $entity->created = time();
            } else $entity = $this->get($record['id']);

            foreach ($data as $key => $value) $entity->{$key} = $value;

            return $this->save($entity)->toArray();

        } else return false;

    }

    public function getByTelegramId($telegram_id = null)
    {
        if (!is_null($telegram_id)) {
            $options = ['conditions' => [$this->model . '.telegram_id' => $telegram_id]];
            $results = $this->find('all', $options)->first();
            if (!empty($results)) return $results->toArray();
            else return [];
        } else return false;
    }

    public function getTelegramIdsList()
    {
        $options = [
            'valueField' => 'telegram_id'
        ];
        $results = $this->find('list', $options);
        return (!empty($results)) ? $results->toArray() : [];
    }

    public function getCaffeList()
    {
        return $this->find('list', ['conditions' => [$this->model . '.is_caffe' => 1], 'valueField' => 'telegram_id'])->toArray();
    }
}
