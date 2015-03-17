<?php

namespace OPCache;

use mikehaertl\shellcommand\Command;

class FCGIRequest {

  public function __construct($fcgi, $uri, $queryString) {
    $this->fcgi = $fcgi;
    $this->uri = $uri;
    $this->queryString = $queryString;
  }

  public function run() {
    global $base_url;
    $this->command = new Command(
      array(
        'command' => 'cgi-fcgi -bind -connect ' . $this->fcgi,
        'procEnv' => array(
          'SCRIPT_NAME' => '/index.php',
          'SCRIPT_FILENAME' => DRUPAL_ROOT . '/index.php',
          'HTTP_HOST' => $base_url,
          'REMOTE_ADDR' => '127.0.0.1',
          'QUERY_STRING' => $this->queryString,
          'REQUEST_METHOD' => 'GET',
          'SERVER_SOFTWARE' => 'Drupal',
          'REQUEST_URI' => $this->uri,
        ),
      )
    );

    if ($this->command->execute()) {
      return substr($this->command->getOutput(), 8, 3);
    }
    else {
      return $this->command->getExitCode();
    }
  }

}
