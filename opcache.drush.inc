<?php
/**
 * @file
 * Contains Drush commands and hook implementations for the OPcache module.
 */

use OPCache\OPCache;
use OPCache\OPCacheConfiguration;
use OPCache\OPCacheStatus;

/**
 * Implements hook_drush_command().
 */
function opcache_drush_command() {
  $commands = array(
    'opcache-configuration' => array(
      'description' => dt('Get current OPcache configuration.'),
    ),
    'opcache-invalidate' => array(
      'description' => dt('Invalidate scripts in OPcache.'),
      'arguments' => array(
        'scripts' => dt('Required. A space-delimited list of scripts to invalidate in OPcache.'),
      ),
      'required-arguments' => 1,
    ),
    'opcache-status' => array(
      'description' => dt('Get current OPcache status.'),
    ),
  );

  return $commands;
}

/**
 * Implements hook_drush_cache_clear().
 *
 * Presents the OPcache as a "clearable" cache to Drush, so it appears in the
 * select list of caches when one runs `drush cache-clear`. The callback is
 * invoked when one selects "opcache" form that list or simply runs `drush cc
 * opcache`.
 */
function opcache_drush_cache_clear(&$types) {
  $types['opcache'] = 'drush_opcache_reset';
}

/**
 * Callback to invalidate scripts in OPcache.
 *
 * @see opcache_drush_command()
 */
function drush_opcache_invalidate() {
  $opcache = new OPCache();
  $args = func_get_args();
  if (count($args) > 0) {
    foreach ($args as $script) {
      $opcache->drushInvalidate($script);
    }
  }
}

/**
 * Callback to reset ("clear") OPcache.
 *
 * @see opcache_drush_cache_clear()
 */
function drush_opcache_reset() {
  $opcache = new OPCache();
  $opcache->drushReset();
}

/**
 * Implements… actually, I'm not sure what this implements.
 *
 * In the case of a `drush cc all`, Drush will clear its own cache, then tell
 * Drupal to clear all its caches - in the CLI context. Our implementation of
 * hook_flush_caches() will not even bother if drupal_is_cli() == TRUE. However,
 * Drush will invoke drush_[all_modules]_cache_clear(), so we'll use an
 * implementation of that to see if the "all" cache was cleared, and reset the
 * OPcache if that's the case. This should be the least surprising behavior.
 */
function drush_opcache_cache_clear($type = NULL) {
  if ($type === 'all') {
    drush_opcache_reset();
  }
}

function drush_opcache_status() {
  $opcache = new OPCache();
  $table = array();
  $status = json_decode($opcache->drushStatus());

  drush_print('Summary:');
  $table[] = array('ENABLED', 'CACHE FULL', 'RESTART PENDING', 'RESTART IN PROGRESS');
  $table[] = array(
    ($status->opcache_enabled) ? 'YES' : 'NO',
    ($status->cache_full) ? 'YES' : 'NO',
    ($status->restart_pending) ? 'YES' : 'NO',
    ($status->restart_in_progress) ? 'YES' : 'NO',
  );
  drush_print_table($table, TRUE);

  $table = array();
  drush_print('Memory Usage:');
  $table[] = array('USED', 'FREE', 'WASTED', 'WASTED %');
  $table[] = array(
    drush_format_size($status->memory_usage->used_memory),
    drush_format_size($status->memory_usage->free_memory),
    drush_format_size($status->memory_usage->wasted_memory),
    round($status->memory_usage->current_wasted_percentage) . '%',
  );
  drush_print_table($table, TRUE);

  $table = array();
  drush_print('Interned Strings Usage:');
  $table[] = array('BUFFER SIZE', 'USED MEMORY', 'FREE MEMORY', 'NUMBER OF STRINGS');
  $table[] = array(
    drush_format_size($status->interned_strings_usage->buffer_size),
    drush_format_size($status->interned_strings_usage->used_memory),
    drush_format_size($status->interned_strings_usage->free_memory),
    $status->interned_strings_usage->number_of_strings,
  );
  drush_print_table($table, TRUE);

  $table = array();
  drush_print('Statistics:');
  $table[] = array('Number of Cached Scripts', $status->opcache_statistics->num_cached_scripts);
  $table[] = array('Number of Cached Keys', $status->opcache_statistics->num_cached_keys);
  $table[] = array('Maximum Cached Keys', $status->opcache_statistics->max_cached_keys);
  $table[] = array('Hits', $status->opcache_statistics->hits);
  $table[] = array('Start Time', $status->opcache_statistics->start_time);
  $table[] = array('Last Restart Time', $status->opcache_statistics->last_restart_time);
  $table[] = array('OOM Restarts', $status->opcache_statistics->oom_restarts);
  $table[] = array('Hash Restarts', $status->opcache_statistics->hash_restarts);
  $table[] = array('Manual Restarts', $status->opcache_statistics->manual_restarts);
  $table[] = array('Misses', $status->opcache_statistics->misses);
  $table[] = array('Blacklist Misses', $status->opcache_statistics->blacklist_misses);
  $table[] = array('Blacklist Miss Ratio', $status->opcache_statistics->blacklist_miss_ratio);
  $table[] = array('Hit Rate', round($status->opcache_statistics->opcache_hit_rate, 3) . '%');
  drush_print_table($table, TRUE);

}

