<?php

// Set WebHook
//https://api.telegram.org/bot2048279609:AAFyXyuQUCtalMKvQpjgq2_UTcLYvlhieho/setWebhook?url=https://tfiles.eu/home/c289ny7r9287ctb782t36br8c726tcb8qn27cn76tcr82b7t6br27c6ntr8236r89

// Get WebHook Info
//https://api.telegram.org/bot2048279609:AAFyXyuQUCtalMKvQpjgq2_UTcLYvlhieho/getWebhookInfo

$config['app_variables']['app_root'] = '/';
$config['app_variables']['app_files_version'] = '0.21';
$config['app_variables']['app_files_version_description'] = ' - update users & messages counts';
$config['app_variables']['upload_max_file_size'] = '100Mb';
$config['app_variables']['is_development_mode'] = false;

$config['https_domain'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
$config['telegram_token'] = '2048279609:AAFyXyuQUCtalMKvQpjgq2_UTcLYvlhieho';


$config['security_salt'] = base64_encode(md5('Vf83ru1dGVV' . $config['telegram_token'] . 'bwYeB6BfVUvLm0Z'));
$config['security_key'] = base64_encode('5uruJNdSbB63hg' . md5($config['telegram_token']) . 'P00XzKgIH4MM');
$config['reCaptcha_site_key'] = '6LfNPv8cAAAAAEETIvq2URoL0TMH7OrnwDPVmzVe';
$config['reCaptcha_secret_key'] = '6LfNPv8cAAAAAJGuGHFEK4spDXybP5_3bebvYYN_';


$config['default_ban_time'] = 600; // Seconds
$config['pin_attempts_count_before_delete'] = 5;
$config['record_lifetime'] = 60 * 60 * 2; // Seconds

return $config;
