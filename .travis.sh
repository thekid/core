#!/bin/sh

XP_RUNNERS=https://dl.bintray.com/xp-runners/generic/xp-run-master.sh

case $1 in
  install)
    echo test.xar > test.pth

    # Run HHVM inside Docker as the version provided by Travis-CI is too old
    # For official PHP versions, there's nothing to do
    case "$TRAVIS_PHP_VERSION" in
      hhvm*)
        curl -sSL $XP_RUNNERS > xp-run.in

        echo "hhvm.php7.all = 1" > php.ini
        echo "hhvm.hack.lang.look_for_typechecker = 0" >> php.ini

        docker pull hhvm/hhvm:latest
        docker run --rm hhvm/hhvm:latest hhvm --version

        $(which composer)
        cat $(which composer)
        cat "/home/travis/.phpenv/versions/hhvm/bin/composer"
        ls -al /home/travis/.phpenv/versions/hhvm/bin/ 

        cp $(which composer) composer.in
        docker run --rm -v $(pwd):/opt/src -v $(pwd)/php.ini:/etc/hhvm/php.ini -w /opt/src hhvm/hhvm:latest hhvm --php composer.in install

        echo "#!/bin/sh" > xp-run
        echo "docker run --rm -v $(pwd):/opt/src -v $(pwd)/php.ini:/etc/hhvm/php.ini -w /opt/src hhvm/hhvm:latest /bin/sh xp-run.in \$@" >> xp-run
      ;;

      *)
        curl -sSL $XP_RUNNERS > xp-run
        composer install
      ;;
    esac
  ;;

  run-tests)
    result=0
    for file in `ls -1 src/test/config/unittest/*.ini`; do
      echo "---> $file"
      sh xp-run xp.unittest.Runner $file || result=1
    done
    exit $result
  ;;
esac