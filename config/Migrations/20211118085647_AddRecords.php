<?php

use Migrations\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;


class AddRecords extends AbstractMigration
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
        $table_name = 'records';

        if (!$this->hasTable($table_name)) {
            $table = $this->table($table_name);
            $table->addColumn('user_id', 'integer', ['default' => null, 'null' => false]);
            $table->addColumn('type_id', 'integer', ['default' => null, 'null' => false]);
            $table->addColumn('telegram_file_id', 'text', ['default' => null, 'limit' => MysqlAdapter::TEXT_LONG, 'null' => true]);
            $table->addColumn('file_name', 'text', ['default' => null, 'limit' => MysqlAdapter::TEXT_LONG, 'null' => true]);
            $table->addColumn('file_preview', 'text', ['default' => null, 'limit' => MysqlAdapter::TEXT_LONG, 'null' => true]);
            $table->addColumn('text', 'text', ['default' => null, 'limit' => MysqlAdapter::TEXT_LONG, 'null' => true]);
            $table->addColumn('pin', 'text', ['default' => null, 'limit' => MysqlAdapter::TEXT_LONG, 'null' => false]);
            $table->addColumn('pin_attempts_count', 'integer', ['default' => 0, 'null' => false]);
            $table->addColumn('link_token', 'string', ['default' => null, 'limit' => 10, 'null' => false]);
            $table->addColumn('token', 'string', ['default' => null, 'limit' => 60, 'null' => false]);
            $table->addColumn('created', 'integer', ['default' => null, 'null' => false]);
            $table->addColumn('expiration_time', 'integer', ['default' => null, 'null' => false]);
            $table->create();
        }
    }
}
