<?php

namespace OPCache;

class HTTPRequest extends \GuzzleHttp\Client {
  const VERSION = '7.x-1.x';

  public function getDefaultUserAgent() {
    return 'OPCache/' . self::VERSION . ' ' . parent::getDefaultUserAgent();
  }
}