<?php

namespace OPCache;

class HTTPRequest extends \GuzzleHttp\Client {
  public static function getDefaultUserAgent() {
    return 'OPCache/' . self::getDrupalModuleVersion() . ' ' . parent::getDefaultUserAgent();
  }

  private static function getDrupalModuleVersion() {
    $info = system_get_info('module', 'opcache');
    return $info['version'];
  }
}
