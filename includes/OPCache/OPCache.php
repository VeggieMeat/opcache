<?php

namespace OPCache;

use OPCache\FCGIRequest;

class OPCache {

  private $queryString;
  private $servers;
  private $uri;

  public function __construct() {
    $this->servers = variable_get('opcache_backends', NULL);
  }

  private function buildQueryString($params) {
    $this->buildUri($params);
    $this->queryString = 'q=' . $this->uri;
  }

  private function buildUri($params) {
    $token = $this->getToken();
    $this->uri = 'opcache/' . REQUEST_TIME . '/' . $token . '/' . $params['op'];
    if (isset($params['script'])) {
      $this->uri .= '/' . $params['script'];
    }
  }

  public function cacheClear() {
    if (!$this->servers) {
      return $this->reset();
    }

    // Multiple backends must be cleared.
    $params['op'] = 'reset';
    $this->multiBackendRequest($params);
  }

  private function drushBuildUrl($server, $params) {
    $this->buildQueryString($params);
    $url = "{$server}?{$this->queryString}";
    return $url;
  }

  public function drushInvalidate($script) {
    $params = array();
    $params['op'] = 'invalidate';
    $params['script'] = $script;
    $this->drushRequest($params);
  }

  public function drushReset() {
    $params = array();
    $params['op'] = 'reset';
    $this->drushRequest($params);
  }

  public function drushStatus() {
    $params = array();
    $params['op'] = 'status';
    return $this->drushRequest($params);
  }

  private function drushRequest($params = array()) {
    global $base_url;
    if (preg_match('/default$/', $base_url)) {
      drush_log(dt("In order to properly reset the OPcache cache, please use the -l/--uri flag to specify the correct URL of this Drupal installation, or specify paths to the PHP proxy servers in the OPcache module's settings form."), 'error');
      return;
    }
    if (!$this->servers) {
      $server = array(url('<front>', array('absolute' => TRUE)));
      $this->httpRequest($server, $params);
    }
    else {
      $this->multiBackendRequest($params);
    }
  }

  private function fcgiRequest($server, $params) {
    global $base_url;
    $fcgi = substr($server, 7);
    $command = new FCGIRequest($fcgi, $this->uri, $this->queryString);
    $exit = $command->run();

    if ($exit == 127) {
      watchdog('opcache', 'cgi-fcgi not found', array(), WATCHDOG_ERROR);
    }
    elseif ($exit == 2) {
      $this->logResponse($server, 0, $params);
    }
    elseif ($exit === 0) {
      $status = substr($response[0], 8, 3);
      $this->logResponse($server, $status, $params);
    }
    else {
      watchdog('opcache', 'An error was encountered running cgi-fcgi.', array(), WATCHDOG_ERROR);
    }
  }

  public function getToken($request_time = REQUEST_TIME) {
    return drupal_hmac_base64('opcache:' . $request_time, drupal_get_private_key() . drupal_get_hash_salt());
  }

  private function httpRequest($server, $params) {
    if (!extension_loaded('curl')) {
      watchdog('opcache', 'The cURL PHP extension is not installed on this server. In order to clear OPcache for Drupal from Drush, you must have cURL installed.', array(), WATCHDOG_ERROR);
      return;
    }

    global $base_url;
    $urldata = @parse_url($base_url);

    $url = $this->drushBuildUrl($server, $params);
    $cc = curl_init();
    $headers = array("Host: " . $urldata['host']);
    curl_setopt($cc, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($cc, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($cc, CURLOPT_HEADER, 0);
    curl_setopt($cc, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($cc, CURLOPT_URL, $url);
    curl_setopt($cc, CURLOPT_TIMEOUT, 10);
    curl_setopt($cc, CURLOPT_MAXREDIRS, 4);
    $cr = curl_exec($cc);
    $status = curl_getinfo($cc, CURLINFO_HTTP_CODE);
    curl_close($cc);
    $this->logResponse($server, $status, $params);
  }

  public function isEnabled() {
    $status = new OPCacheStatus();
    $info = $status->getCurrentStatus();
    if ($info['opcache_enabled']) {
      return TRUE;
    }
  }

  public function invalidate($script, $force = FALSE) {
    return opcache_invalidate($script, $force);
  }

  private function logResponse($server, $status, $params, $cr = NULL) {
    switch ($status) {
      case 200:
        if ($params['op'] === 'reset') {
          watchdog('opcache', 'OPcache was reset at @server.', array('@server' => $server), WATCHDOG_INFO);
        }
        elseif ($params['op'] === 'invalidate') {
          watchdog('opcache', '@script was invalidated in OPcache at @server.', array('@script' => $params['script'], '@server' => $server), WATCHDOG_INFO);
        }
        //elseif ($params['op'] === 'status' && isset($cr)) {
        //  return $cr;
        //}
        break;
      case 404:
        watchdog('opcache', 'OPcache operation at @server failed; the reset path could not be found (404).', array('@server' => $server), WATCHDOG_ERROR);
        break;
      case 403:
        watchdog('opcache', 'OPcache operation at @server failed; access to the reset path was denied (403). This may happen if too much time elapsed during the request process. Please try again.', array('@server' => $server), WATCHDOG_ERROR);
        break;
      case 0:
        watchdog('opcache', 'OPcache operation at @server failed; server could not be reached.', array('@server' => $server), WATCHDOG_ERROR);
        break;
      default:
        watchdog('opcache', 'OPcache operation at @server failed; status code @code.', array('@server' => $server, '@code' => $status), WATCHDOG_ERROR);
    }
  }

  private function multiBackendRequest($params) {
    foreach ($this->servers as $server) {
      if (substr($server, 0, 7) == 'fcgi://') {
        $method = 'fcgiRequest';
      }
      else {
        $method = 'httpRequest';
      }

      if (!$this->parallelRequest($method, $server, $params)) {
        $this->{$method}($server, $params);
      }
    }
  }

  private function parallelRequest($method, $server, $params) {
    if (!function_exists('pcntl_fork')) {
      return FALSE;
    }

    $pid = pcntl_fork();
    if ($pid) {
      return TRUE;
    }

    //Database::closeConnection();
    $this->{$method}($server, $params);
    exit(0);
  }

  public function reset() {
    return opcache_reset();
  }

  public function status() {
    $status = new OPCacheStatus();
    return $status->getStatusData();
  }

  public function verifyToken($request_time, $token) {
    return $token === $this->getToken($request_time);
  }

}
