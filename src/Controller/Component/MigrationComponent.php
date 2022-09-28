<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Migrations\Migrations;
use phpDocumentor\Reflection\Types\Void_;


/**
 *
 * The name of the migration files are prefixed with the date in which they were created,
 * in the format YYYYMMDDHHMMSS_MigrationName.php. Here are examples of migration filenames:
 * 20160121163850_CreateProducts.php 20160210133047_AddRatingToProducts.php
 *
 * Migrations file name
 * Migration names can follow any of the following patterns:
 * (/^(Create)(.*)/) Creates the specified table.
 * (/^(Drop)(.*)/) Drops the specified table. Ignores specified field arguments
 * (/^(Add).*(?:To)(.*)/) Adds fields to the specified table
 * (/^(Remove).*(?:From)(.*)/) Removes fields from the specified table
 * (/^(Alter)(.*)/) Alters the specified table. An alias for CreateTable and AddField.
 * (/^(Alter).*(?:On)(.*)/) Alters fields from the specified table.
 *
 * You can also use the underscore_form as the name for your migrations i.e. create_products.
 *
 */



class MigrationComponent extends Component
{

    public function initialize(array $config): void
    {
        $this->migrations = new Migrations();
    }

    public function status()
    {
        return $this->migrations->status();
    }

    public function migrate()
    {
        return $this->migrations->migrate();
    }

    public function rollback()
    {
        return $this->migrations->rollback();
    }

}
