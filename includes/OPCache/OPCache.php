<?php

namespace OPCache;

use OPCache\FCGIRequest;
use OPCache\HTTPRequest;
use OPCache\OPCacheConfiguration;
use OPCache\OPCacheStatus;

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
    $params = [];
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
    if (preg_match('/default$/', $base_url) && !$this->servers) {
      drush_log(dt("In order to properly reset the OPcache cache, please use the -l/--uri flag to specify the correct URL of this Drupal installation, or specify paths to the PHP proxy servers in the OPcache module's settings form."), 'error');
      return;
    }
    if (!$this->servers) {
      $server = url('<front>', array('absolute' => TRUE));
      $this->httpRequest($server, $params);
    }
    else {
      $this->multiBackendRequest($params);
    }
  }

  private function fcgiRequest($server, $params) {
    $fcgi = substr($server, 7);
    try {
      $command = new FCGIRequest($fcgi, $this->uri, $this->queryString);
      $command->run();
    } catch (\Exception $e) {
      watchdog('opcache', 'An error was encountered clearing OPCache on %server. Message: %error', array('%server' => $server, '%error' => $e->getMessage()), WATCHDOG_ERROR);
    }
  }

  public function getToken($request_time = REQUEST_TIME) {
    return drupal_hmac_base64('opcache:' . $request_time, drupal_get_private_key() . drupal_get_hash_salt());
  }

  private function httpRequest($server, $params) {
    global $base_url;
    $urldata = @parse_url($base_url);

    $url = $this->drushBuildUrl($server, $params);
    try {
      $client = new HTTPRequest();
      $request = $client->createRequest('GET', $url);
      $request->setHeader('Host', $urldata['host']);
      $response = $client->send($request);
      $status = $response->getStatusCode();
      $this->logResponse($server, $status, $params);
    } catch (\Exception $e) {
      watchdog('opcache', 'An error was encountered clearing OPCache on %server. Message: %error', array('%server' => $server, '%error' => $e->getMessage()), WATCHDOG_ERROR);
    }
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

  private function logResponse($server, $status, $params) {
    switch ($status) {
      case 200:
        if ($params['op'] === 'reset') {
          watchdog('opcache', 'OPcache was reset at @server.', array('@server' => $server), WATCHDOG_INFO);
        }
        elseif ($params['op'] === 'invalidate') {
          watchdog('opcache', '@script was invalidated in OPcache at @server.', array('@script' => $params['script'], '@server' => $server), WATCHDOG_INFO);
        }
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

      $this->{$method}($server, $params);
    }
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
