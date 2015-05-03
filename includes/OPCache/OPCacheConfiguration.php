<?php

namespace OPCache;

class OPCacheConfiguration {

  private $configurationData;

  public function __construct() {
    $this->configurationData = opcache_get_configuration();
  }

  public function getDirectives() {
    return $this->configurationData['directives'];
  }

  public function getDirective($directive) {
    if (strpos($directive, 'opcache.') === FALSE) {
      $directive = 'opcache.' . $directive;
    }

    return ini_get($directive);
  }

  public function getDirectiveKeys() {
    return array(
      'opcache.enable',
      'opcache.enable_cli',
      'opcache.memory_consumption',
      'opcache.interned_strings_buffer',
      'opcache.max_accelerated_files',
      'opcache.wasted_percentage',
      'opcache.validate_timestamps',
      'opcache.revalidate_freq',
      'opcache.revalidate_path',
      'opcache.save_comments',
      'opcache.load_comments',
      'opcache.fast_shutdown',
      'opcache.enable_file_override',
      'opcache.optimization_level',
      'opcache.inherited_hack',
      'opcache.dups_fix',
      'opcache.blacklist_filename',
      'opcache.max_file_size',
      'opcache.consistency_checks',
      'opcache.force_restart_timeout',
      'opcache.error_log',
      'opcache.log_verbosity_level',
      'opcache.preferred_memory_model',
      'opcache.protect_memory',
      'opcache.mmap_base',
    );
  }

  public function getBlacklist() {
    return $this->configurationData['blacklist'];
  }

  public function getVersion() {
    return $this->configurationData['version'];
  }

  public function checkDirectives() {
    $recommended = array();
    $required = array();
    $severity = REQUIREMENT_OK;
    foreach ($this->getDirectiveKeys() as $directive) {
      if ($message = $this->recommendedDirectiveSetting($directive)) {
        $recommended[$directive] = $message;
      }
      if ($message = $this->requiredDirectiveSetting($directive)) {
        $required[$directive] = $message;
      }
    }

    if (!empty($recommended)) {
      $severity = REQUIREMENT_WARNING;
    }
    if (!empty($required)) {
      $severity = REQUIREMENT_ERROR;
    }

    $messages = array_merge($recommended, $required);
    return array(
      'messages' => $messages,
      'severity' => $severity,
    );
  }

  private function recommendedDirectiveSetting($directive) {
    $setting = $this->getDirective($directive);

    switch ($directive) {
      case 'opcache.validate_timestamps':
        if ($setting == TRUE) {
          return t('OPcache is still checking the timestamps of files to see if they have been updated. For maximum performance, set <tt>opcache.validate_timestamps = 0</tt> in your PHP installation&rsquo;s configuration file, and use the &ldquo;Clear all caches&rdquo; button on the <a href="!perf_path">Performance page</a> to clear the OPcache cache after updating files (or use Drush).', array('!perf_path' => url('admin/config/development/performance')));
        }
        break;
      default:
        break;
    }
  }

  private function requiredDirectiveSetting($directive) {
    $setting = $this->getDirective($directive);

    switch ($directive) {
      case 'opcache.save_comments':
      case 'opcache.load_comments':
        if ($setting == FALSE) {
          return t('Comment saving and/or loading is disabled. This functionality <em>must</em> be enabled, or Drupal will not function correctly. Ensure that you have <tt>opcache.save_comments = 1</tt> and <tt>opcache.load_comments = 1</tt> in this PHP installation&rsquo;s configuration file.');
        }
        break;
      default:
        break;
    }
  }
}
