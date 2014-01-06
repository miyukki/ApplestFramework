#!/bin/sh

# Copy test configuration
cp test/Config.php Config.php

# MySQL initializations
mysql -e 'CREATE DATABASE travis_test'
mysql -e 'CREATE TABLE `users` (\
 `id` bigint(20) NOT NULL AUTO_INCREMENT,\
 `created_at` bigint(20) NOT NULL,\
 `updated_at` bigint(20) NOT NULL,\
 `deleted_at` bigint(20) NOT NULL,\
 `name` text NOT NULL,\
 PRIMARY KEY (`id`)\
) ENGINE=MyISAM DEFAULT CHARSET=utf8'

php test/Test.php

exit $?
