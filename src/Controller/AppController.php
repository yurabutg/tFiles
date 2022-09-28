<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Controller;

use Cake\Cache\Cache;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Filesystem\Folder;
use Cake\Http\Client;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use DateTime;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/4/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Telegram');

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/4/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');

        $this->_setTableVariables();
        $this->_setAppVariables();
        $this->_setConfigVariables();

        if ($this->_checkAppVersionChanged()) {
            $this->_doMigrations();
            $this->_setCurrentAppVersionCached();
            $this->_sendNotificationNewVersion();
        }

        $this->_setTelegramUser();
        $this->_deleteExpiredRecords();

        $this->_setStatisticsVariables();
    }

    public function getConfig($name)
    {
        return Configure::read($name);
    }

    public function setConfig($key, $value)
    {
        Configure::write($key, $value);
    }

    public function getError404()
    {
        throw new NotFoundException('404');
    }

    private function _setStatisticsVariables(){
        $statistics = $this->statistics_table->getStatistics();
        $statistics['users_count'] = $this->users_table->getCount();
        $this->set('statistics', $statistics);
    }

    private function _setTelegramUser()
    {
        if ($this->_checkCookie('tg_user')) {
            $this->telegram_logged_user_data = json_decode($_COOKIE['tg_user'], true);
        } else $this->telegram_logged_user_data = null;
        $this->set('telegram_logged_user_data', $this->telegram_logged_user_data);
    }

    private function _checkCookie($cookie_name)
    {
        return (isset($_COOKIE[$cookie_name]));
    }

    private function _deleteExpiredRecords()
    {
        $this->records_table->deleteExpired();
    }

    private function _setTableVariables()
    {
        $dir = new Folder('../src/Model/Table');
        $files = $dir->find('.*\.php');
        foreach ($files as $file) {
            $variable = Inflector::underscore(str_replace(".php", "", $file));
            $this->$variable = TableRegistry::getTableLocator()->get(str_replace("Table.php", "", $file));
        }
    }

    private function _checkAppVersionChanged()
    {
        $app_version_cached = Cache::read('APP_VERSION_INFO', APP_VERSION_INFO);
        $current_app_version = $this->app_files_version;
        return ($app_version_cached !== $current_app_version);
    }

    private function _setCurrentAppVersionCached()
    {
        $current_app_version = $this->app_files_version;
        Cache::write('APP_VERSION_INFO', $current_app_version, APP_VERSION_INFO);
    }

    private function _doMigrations()
    {
        $this->loadComponent('Migration');
        $migrations_statuses = $this->Migration->status();

        if (!empty($migrations_statuses)) {
            foreach ($migrations_statuses as $migration) {
                if ($migration['status'] !== 'up') {
                    $this->Migration->migrate();
                    break;
                }
            }
        }
    }

    private function _setAppVariables()
    {
        $this->set('current_controller', $this->current_controller = $this->request->getParam('controller'));
        $this->set('current_action', $this->current_controller = $this->request->getParam('action'));
    }

    private function _setConfigVariables()
    {
        foreach (Configure::read('app_variables') as $key => $value) {
            $this->set($key, $this->{$key} = $value);
        }
    }

    private function _sendNotificationNewVersion()
    {
        $users_telegram_ids = $this->users_table->getTelegramIdsList();
        foreach ($users_telegram_ids as $telegram_id) {
            $this->Telegram->sendMessage($telegram_id, vsprintf(__('New app version released! %s'), [$this->app_files_version]) . ' ' . $this->app_files_version_description);
        }
    }
}
