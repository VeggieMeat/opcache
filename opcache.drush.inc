<?php
/**
 * @file
 * Contains Drush commands and hook implementations for the OPcache module.
 */

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
