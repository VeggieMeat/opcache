[![Build Status](https://travis-ci.org/VeggieMeat/opcache.svg?branch=8.x-1.x)](https://travis-ci.org/VeggieMeat/opcache)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/VeggieMeat/opcache/badges/quality-score.png?b=8.x-1.x)](https://scrutinizer-ci.com/g/VeggieMeat/opcache/?branch=8.x-1.x)
[![Coverage Status](https://coveralls.io/repos/VeggieMeat/opcache/badge.svg?branch=8.x-1.x)](https://coveralls.io/r/VeggieMeat/opcache?branch=8.x-1.x)

OPcache
=======

This module allows Drupal to report status information about the cache and reset the cache.

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

