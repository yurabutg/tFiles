<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class RecordsTypesTable extends Table
{

    public $model = 'RecordsTypes';
    public $contain = [];

    public function getIdBySlug($slug = null)
    {
        if (!is_null($slug)) {
            $options = ['conditions' => [$this->model . '.slug' => $slug]];
            $result = $this->find('all', $options)->first();
            if (!empty($results)) return $result->id;
            else return [];
        } else return [];
    }

    public function getList()
    {
        return $this->find('list', ['valueField' => 'slug'])->toArray();
    }
}


