<?php

use Cake\ORM\TableRegistry;
use Migrations\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;


class AddStatisticsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table_name = 'statistics';

        if (!$this->hasTable($table_name)) {
            $table = $this->table($table_name);
            $table->addColumn('messages_count', 'integer', ['default' => 0, 'null' => false]);
            $table->addColumn('files_count', 'integer', ['default' => 0, 'null' => false]);
            $table->addColumn('files_total_size', 'integer', ['default' => 0, 'null' => false]);
            $table->create();

            /* add record values */
            $statistics_table = TableRegistry::getTableLocator()->get('Statistics');
            $entity = $statistics_table->newEmptyEntity();
            $entity->messages_count = 0;
            $entity->files_count = 0;
            $entity->files_total_size = 0;
            $statistics_table->save($entity);
        }
    }
}
