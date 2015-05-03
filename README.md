[![Build Status](https://travis-ci.org/VeggieMeat/opcache.svg?branch=7.x-1.x)](https://travis-ci.org/VeggieMeat/opcache)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/VeggieMeat/opcache/badges/quality-score.png?b=7.x-1.x)](https://scrutinizer-ci.com/g/VeggieMeat/opcache/?branch=7.x-1.x)
[![Coverage Status](https://coveralls.io/repos/VeggieMeat/opcache/badge.svg?branch=7.x-1.x)](https://coveralls.io/r/VeggieMeat/opcache?branch=7.x-1.x)

OPcache
=======

This module allows Drupal to report status information about the cache and reset the cache.

REQUIREMENTS
------------

- PHP 5.4
- OPcache extension (see [official installation instructions](http://php.net/manual/en/opcache.installation.php)).
- [Composer Manager](https://www.drupal.org/project/composer_manager)

FEATURES
--------

- Drush integration
- Debugging tools (not yet implemented)

DRUSH COMMANDS
--------------

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

