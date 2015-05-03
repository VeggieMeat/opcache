<?php

namespace OPCache;

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

  public function getMemoryInfo() {
    $memory = $this->getMemoryUsage();

    return array(
      'current_wasted_percentage' => round($memory['current_wasted_percentage'], 2) . '%',
      'free_memory' => $this->format($memory['free_memory']),
      'used_memory' => $this->format($memory['used_memory']),
      'wasted_memory' => $this->format($memory['wasted_memory']),
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

  private function format($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    $base = log($size) / log(1024);
    return round(pow(1024, $base - floor($base)), $precision) . $units[floor($base)];
  }

}
