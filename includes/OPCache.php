<?php

class OPCache {

  private function drushBuildUrl($server, $params) {
    $token = $this->getToken();
    // @todo Tokens are only valid for 30 seconds after the time in the second
    // parameter, and we're resetting them one at a time. If there are many
    // servers and/or the resets take too long (perhaps because of a timeout),
    // later servers will have invalid tokens. Easy solution: use time() instead
    // of REQUEST_TIME. Harder solution: make asyncrhonous cURL requests that
    // fire all at once.
    $url = $server . '/opcache/' . REQUEST_TIME . "/{$token}/{$params['op']}";
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
    if (!extension_loaded('curl')) {
      drush_log('The cURL PHP extension is not installed on this server. In order to clear OPcache for Drupal from Drush, you must have cURL installed.', 'error');
      return;
    }

    global $base_url;
    $urldata = @parse_url($base_url);
    $servers = variable_get('opcache_backends', NULL);
    if (!$servers) {
      if (preg_match('/default$/', $base_url)) {
        drush_log(dt("In order to properly reset the OPcache cache, please use the -l/--uri flag to specify the correct URL of this Drupal installation, or specify paths to the PHP proxy servers in the OPcache module's settings form."), 'error');
        return;
      }
      $servers = array(url('<front>', array('absolute' => TRUE)));
    }

    foreach ($servers as $server) {
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
      switch ($status) {
        case 200:
          if ($params['op'] === 'reset') {
            drush_log(dt('OPcache was reset at @server.', array('@server' => $server)), 'success');
          }
          elseif ($params['op'] === 'invalidate') {
            drush_log(dt('@script was invalidated in OPcache at @server.', array('@script' => $params['script'], '@server' => $server)), 'success');
          }
          break;
        case 404:
          drush_log(dt('OPcache operation at @server failed; the reset path could not be found (404).', array('@server' => $server)), 'error');
          break;
        case 403:
          drush_log(dt('OPcache operation at @server failed; access to the reset path was denied (403). This may happen if too much time elapsed during the request process. Please try again.', array('@server' => $server)), 'error');
          break;
        case 0:
          drush_log(dt('OPcache operation at @server failed; server could not be reached.', array('@server' => $server)), 'error');
          break;
        default:
          drush_log(dt('OPcache operation at @server failed; status code @code.', array('@server' => $server, '@code' => $status)), 'error');          
      }
    }
  }

  public function getToken($request_time = REQUEST_TIME) {
    return drupal_hmac_base64('opcache:' . $request_time, drupal_get_private_key() . drupal_get_hash_salt());
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

  public function reset() {
    return opcache_reset();
  }

  public function verifyToken($request_time, $token) {
    return $token === $this->getToken($request_time);
  }

}
