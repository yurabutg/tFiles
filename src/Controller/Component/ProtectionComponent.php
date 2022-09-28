<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use \Cake\Core\Configure;
use Cake\Utility\Security;


class ProtectionComponent extends Component
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->security_key = Configure::read('security_key');
        $this->security_salt = Configure::read('security_salt');
        $this->hash_algorithm = 'sha256';
    }

    public function encrypt($string)
    {
        return base64_encode(Security::encrypt($string, $this->security_key, $this->security_salt));
    }

    public function decrypt($string)
    {
        return Security::decrypt(base64_decode($string), $this->security_key, $this->security_salt);
    }

    public function getHash($string)
    {
        return Security::hash($string, $this->hash_algorithm);
    }

    public function checkHash($string_to_check, $string_original)
    {
        return Security::constantEquals($string_original, $this->getHash($string_to_check));
    }
}

