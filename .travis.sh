#!/bin/sh

XP_RUNNERS=https://dl.bintray.com/xp-runners/generic/xp-run-master.sh
HHVM_VERSION=latest

case $1 in
  install)
    printf "\033[33;1mInstalling XP Runners\033[0m\n"
    echo $XP_RUNNERS
    echo test.xar > test.pth
    curl -sSL $XP_RUNNERS > xp-run
    echo

    # Run HHVM inside Docker as the version provided by Travis-CI is too old
    # For official PHP versions, there's nothing to do
    case "$TRAVIS_PHP_VERSION" in
      hhvm*)
        printf "\033[33;1mReplacing HHVM\033[0m\n"

        echo "hhvm.php7.all = 1" > php.ini
        echo "hhvm.hack.lang.look_for_typechecker = 0" >> php.ini

        docker pull hhvm/hhvm:$HHVM_VERSION
        docker run --rm hhvm/hhvm:$HHVM_VERSION hhvm --version
        echo

        printf "\033[33;1mRunning Composer\033[0m\n"
        docker run --rm -v $(pwd):/opt/src -v $(pwd)/php.ini:/etc/hhvm/php.ini -w /opt/src hhvm/hhvm:latest hhvm --php .phpenv/versions/hhvm/bin/composer install

        mv xp-run xp-run.in
        echo "#!/bin/sh" > xp-run
        echo "docker run --rm -v $(pwd):/opt/src -v $(pwd)/php.ini:/etc/hhvm/php.ini -w /opt/src hhvm/hhvm:latest /bin/sh xp-run.in \$@" >> xp-run
      ;;

      *)
        printf "\033[33;1mRunning Composer\033[0m\n"
        composer install
      ;;
    esac
  ;;

  run-tests)
    result=0
    for file in `ls -1 src/test/config/unittest/*.ini`; do
      printf "\033[33;1mTesting %s\033[0m\n" $file
      sh xp-run xp.unittest.Runner $file || result=1
      echo
    done
    exit $result
  ;;
esac