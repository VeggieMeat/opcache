<?php

class OPCacheStatus {

  private $statusData;
  private $scripts = array();

  public function __construct($get_scripts = FALSE) {
    $this->statusData = opcache_get_status($get_scripts);
    if ($get_scripts) {
      $this->scripts = $this->statusData['scripts'];
    }
  }

  public function getStatusData() {
    return $this->statusData;
  }

  public function getCurrentStatus() {
    return array(
      'opcache_enabled' => $this->statusData['opcache_enabled'],
      'cache_full' => $this->statusData['cache_full'],
      'restart_pending' => $this->statusData['restart_pending'],
      'restart_in_progress' => $this->statusData['restart_in_progress'],
    );
  }

  public function getMemoryUsage() {
    return $this->statusData['memory_usage'];
  }

  public function getStatistics() {
    return $this->statusData['opcache_statistics'];
  }

  public function getScripts() {
    return $this->scripts;
  }

}
