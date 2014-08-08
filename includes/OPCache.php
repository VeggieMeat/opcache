<?php

class OPCache {

  public function __construct() {
    $this->configuration = new OPCacheConfiguration();
    $this->status = new OPCacheStatus();
  }

  private function drushBuildUrl($server, $params) {
    $token = $this->getToken();

    $url = url('http://' . $server . '/opcache/' . REQUEST_TIME . '/' . $token . '/' . $params['op'], array('external' => TRUE));

    if (isset($params['script'])) {
      $url .= '/' . $params['script'];
    }

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

  private function drushRequest($params = array()) {
    if (!in_array('curl', get_loaded_extensions())) {
      drush_log('cURL is not installed on this server. In order to clear OPCache for Drupal from Drush, you must have cURL installed.', 'warning');
    }

    global $base_url;
    $urldata = @parse_url($base_url);
    $servers = variable_get('opcache_backends', array('127.0.0.1'));

    foreach ($servers as $server) {
      $url = $this->drushBuildUrl($server, $params);
      $cc = curl_init();
      $headers = array("HOST: " . $urldata['host']);
      curl_setopt($cc, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($cc, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($cc, CURLOPT_HEADER, 0);
      curl_setopt($cc, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($cc, CURLOPT_URL, $url);
      $cr = curl_exec($cc);
      $status = curl_getinfo($cc, CURLINFO_HTTP_CODE);
      curl_close($cc);
      if (drush_get_context('DRUSH_DEBUG') && !drush_get_context('DRUSH_QUIET')) {
        if ($status == 200 && $params['op'] == 'reset') {
          drush_log('OPCache was cleared on ' . $server . '.', 'notice');
        }
        elseif ($status == 200 && $params['op'] == 'invalidate') {
          drush_log($params['script'] . ' was invalidated in OPCache on ' . $server . '.', 'notice');
        }
        else {
          drush_log('OPCache request failed. Error code: ' . $status, 'warning');
        }
      }
    }
  }

  public function getToken($request_time = REQUEST_TIME) {
    return drupal_hmac_base64('opcache:' . $request_time, drupal_get_private_key() . drupal_get_hash_salt());
  }

  public function isEnabled() {
    $info = $this->status->getCurrentStatus();
    if ($info['opcache_enabled']) {
      return TRUE;
    }
  }

  public function invalidate($script, $force = FALSE) {
    return opcache_invalidate($script, $force);
  }

  public function reset() {
    return opcache_reset();
  }

  public function verifyToken($request_time, $token) {
    return $token === $this->getToken($request_time);
  }

}
