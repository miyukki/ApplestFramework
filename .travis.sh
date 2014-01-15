#!/bin/sh

# Copy test configuration
cp test/Config.php Config.php

# MySQL initializations
mysql -e 'CREATE DATABASE travis_test'
php test/Test.php

RESULT=$?

cat log/*

exit $RESULT
