XP Framework Core
=================
[![Build Status on TravisCI](https://secure.travis-ci.org/xp-framework/core.png)](http://travis-ci.org/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_4plus.png)](http://php.net/)
[![Required HHVM 3.5+](https://raw.githubusercontent.com/xp-framework/web/master/static/hhvm-3_5plus.png)](http://hhvm.com/)
[![Latest Stable Version](https://poser.pugx.org/xp-framework/core/version.png)](https://packagist.org/packages/xp-framework/core)

This is the XP Framework's development checkout

Installation
------------
Clone this repository, e.g. using Git Read-Only:

```sh
$ cd /path/to/xp
$ git clone git://github.com/xp-framework/core.git
```

### Runners
The entry point for software written in the XP Framework is not the PHP
interpreter's CLI / web server API but either a command line runner or
a specialized *web* entry point. These runners can be installed by using
the following one-liner:

```sh
$ cd ~/bin
$ wget 'https://github.com/xp-framework/xp-runners/releases/download/v5.2.1/setup' -O - | php
```

### Using it
To use the the XP Framework development checkout, put the following
in your `~/bin/xp.ini` file:

```ini
use=/path/to/xp/core
```

Finally, start `xp -v` to see it working:

```sh
$ xp -v
XP 6.0.0-dev { PHP 5.6.4 & ZE 2.6.0 } @ Windows NT SLATE 6.2 build 9200 (Windows 8) i586
Copyright (c) 2001-2015 the XP group
FileSystemCL<...\xp\core\src\main\php\>
FileSystemCL<...\xp\core\src\test\php\>
FileSystemCL<...\xp\core\src\test\resources\>
FileSystemCL<...\home\Timm\devel\xp\core\>
```

**Enjoy!**

Contributing
------------
To contribute, use the GitHub way - fork, hack, and submit a pull request!
