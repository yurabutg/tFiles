<?php

use Migrations\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;


class AddRecordsTypes extends AbstractMigration
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
        $table_name = 'records_types';

        if (!$this->hasTable($table_name)) {
            $table = $this->table($table_name);
            $table->addColumn('slug', 'string', ['default' => null, 'limit' => 10, 'null' => true]);
            $table->create();
        }
    }
}
