#!/bin/bash
# Setup APP script

# Install composer dependencies
composer install;

# Install yarn dependencies
cd web;
yarn --no-bin-links;
node_modules/webpack/bin/webpack.js;
cd ../;

# Create database file
mkdir -p var/databases/;
php bin/console doctrine:database:create;

# Create database tables
### We execute the following command twice because if it's only executed once, 
### the foreign keys are not being applied at all.
### When executed the second time, it executes the correct SQL for the foreign keys.
### I don't know why it happens.
php bin/console doctrine:schema:update --force;
php bin/console doctrine:schema:update --force;

# Populate the database with default, minimum data needed for running the application
php bin/console db:populate;

# Run the server on port 8000
php bin/console server:run 0.0.0.0:8000;