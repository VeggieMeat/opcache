<?php

namespace OPCache;

class FCGIRequest {

  public function __construct($fcgi, $uri, $queryString) {
    $this->fcgi = $fcgi;
    $this->uri = $uri;
    $this->queryString = $queryString;
  }

  public function run() {
    $fastcgi = new \Crunch\FastCGI\Client($this->fcgi);
    $connection = $fastcgi->connect();
    $request = $connection->newRequest(
      array(
        'GATEWAY_INTERFACE' => 'FastCGI/1.0',
        'REQUEST_METHOD' => 'GET',
        'SERVER_SOFTWARE' => 'Drupal',
        'SCRIPT_NAME' => '/index.php',
        'SCRIPT_FILENAME' => DRUPAL_ROOT . '/index.php',
        'QUERY_STRING' => $this->queryString,
        'REQUEST_URI' => $this->uri,
        'REMOTE_ADDR' => '127.0.0.1',
      )
    );

    $connection->request($request);
  }
}
