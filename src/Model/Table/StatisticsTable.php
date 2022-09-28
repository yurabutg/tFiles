<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class StatisticsTable extends Table
{

    public $model = 'Statistics';
    public $contain = [];

    public function getStatistics(){
        return $this->find('all')->enableHydration()->first()->toArray();
    }

    public function updateMessagesCount()
    {
        $entity = $this->get(1);
        $entity->messages_count = $entity->messages_count + 1;
        $this->save($entity);
    }

    public function updateFilesCount()
    {
        $entity = $this->get(1);
        $entity->files_count = $entity->files_count + 1;
        $this->save($entity);
    }

    public function updateFilesTotalSize($files_total_size = 0)
    {
        $entity = $this->get(1);
        $entity->files_total_size = $entity->files_total_size + $files_total_size;
        $this->save($entity);
    }
}
