#!/usr/bin/env bash

set -e

# Remove development files from release
rm -rf ./.git
rm -rf ./.github
rm -rf ./.claude
rm -rf ./bin
rm -rf ./tests
rm -rf ./vendor
rm -rf ./wp
rm -rf ./node_modules
rm -rf ./src
rm -f ./.gitattributes
rm -f ./.gitignore
rm -f ./.wp-env.json
rm -f ./.eslintrc
rm -f ./.stylelintrc.json
rm -f ./.phpunit.result.cache
rm -f ./composer.lock
rm -f ./package.json
rm -f ./package-lock.json
rm -f ./phpunit.xml
rm -f ./phpcs.ruleset.xml
rm -f ./CLAUDE.md