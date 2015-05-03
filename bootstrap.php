<?php

namespace OPCache;

require_once 'vendor/autoload.php';

define('REQUEST_TIME', time());
define('DRUPAL_ROOT', '/');
define('WATCHDOG_ERROR', 3);

global $base_url;
$base_url = 'http://www.example.com';

function drupal_get_private_key() {
  return '7Y1bi4529QyGWmlj7poveFLNx6iT5qx51ig8duEXwAo';
}

function drupal_get_hash_salt() {
  return '7Y1bi4529QyGWmlj7poveFLNx6iT5qx51ig8duEXwAo';
}

function drupal_hmac_base64($data, $key) {
  $hmac = base64_encode(hash_hmac('sha256', (string) $data, (string) $key, TRUE));
  return strtr($hmac, array('+' => '-', '/' => '_', '=' => ''));
}

function variable_get($variable, $default = NULL) {
  switch ($variable) {
    case 'opcache_backends':
      return array(
        'fcgi://192.168.1.1',
        '192.168.1.7',
        '127.0.0.1',
      );
  }
}

function watchdog($type, $message, $variables = array(), $severity = WATCHDOG_NOTICE, $link = NULL) {
}
