OPCache
=======

This module allows Drupal to report status information about the cache and reset the cache.

REQUIREMENTS
------------

- PHP 5.3
- OPCache extension (see [official installation instructions](http://php.net/manual/en/opcache.installation.php)).

FEATURES
--------

- Drush integration
- Debugging tools (not yet implemented)

DRUSH COMMANDS
--------------

- opcache-reset
  Flush OPcache. Works across multiple webservers.
- opcache-invalidate
  Invalidate scripts currently cached in OPcache. Works across multiple webservers.
  Not yet implemented.
- opcache-status
  Get current OPcache status.
  Not yet implemented.
- opcache-configuration
  Get current OPcache configuration.
  Not yet implemented.

DEBUGGING TOOLS
---------------

Goal is to present a similar interface as the Memcache Admin module.
Not yet implemented.

