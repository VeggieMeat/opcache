<?php

class OPCacheConfiguration {

  private $configurationData;

  public function __construct() {
    $this->configurationData = opcache_get_configuration();
  }

  public function getDirectives() {
    return $this->configurationData['directives'];
  }

  public function getBlacklist() {
    return $this->configurationData['blacklist'];
  }

  public function getVersion() {
    return $this->configurationData['version'];
  }
}
