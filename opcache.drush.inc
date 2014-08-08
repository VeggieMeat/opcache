<?php

/**
 * @file
 * Contains Drush commands for OPCache module.
 */

/**
 * Implements hook_drush_command().
 */
function opcache_drush_command() {
  $commands = array(
    'opcache-configuration' => array(
      'description' => dt('Get current OPCache configuration.'),
    ),
    'opcache-invalidate' => array(
      'description' => dt('Invalidate scripts in OPCache.'),
      'arguments' => array(
        'scripts' => dt('Required. A space-delimited list of scripts to invalidate in OPCache.'),
      ),
      'required-arguments' => 1,
    ),
    'opcache-reset' => array(
      'description' => dt('Flush OPCache.'),
    ),
    'opcache-status' => array(
      'description' => dt('Get current OPCache status.'),
    ),
  );

  return $commands;
}

/**
 * Callback to invalidate scripts in OPCache.
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
 * Callback to flush OPCache.
 */
function drush_opcache_reset() {
  $opcache = new OPCache();
  $opcache->drushReset();
}
