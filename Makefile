# SPDX-FileCopyrightText: Bernhard Posselt <dev@bernhard-posselt.com>
# SPDX-License-Identifier: AGPL-3.0-or-later

# Generic Makefile for building and packaging a Nextcloud app which uses Composer.
#
# Dependencies:
# * make
# * which
# * curl: used if phpunit and composer are not installed to fetch them from the web
# * tar: for building the archive
#
# If no composer.json is in the app root directory, the Composer step
# will be skipped.
#
# The idea behind this is to be completely testing and build tool agnostic. All
# build tools and additional package managers should be installed locally in
# your project, since this won't pollute people's global namespace.

app_name=$(notdir $(CURDIR))
build_tools_directory=$(CURDIR)/build/tools
source_build_directory=$(CURDIR)/build/artifacts/source
source_package_name=$(source_build_directory)/$(app_name)
appstore_build_directory=$(CURDIR)/build/artifacts/appstore
appstore_package_name=$(appstore_build_directory)/$(app_name)
composer=$(shell which composer 2> /dev/null)

all: build

# Fetches the PHP dependencies. If no composer.json
# is present, the composer step is skipped, if no package.json or js/package.json
.PHONY: build
build:
ifneq (,$(wildcard $(CURDIR)/composer.json))
	make composer
endif

.PHONY: check-composer
check-composer:
ifeq (, $(composer))
	@echo "No composer command available, downloading a copy from the web"
	mkdir -p $(build_tools_directory)
	curl -sS https://getcomposer.org/installer | php
	mv composer.phar $(build_tools_directory)
	php $(build_tools_directory)/composer.phar install --prefer-dist
	@exit 1
endif


.PHONY: autoload
autoload: check-composer
	@rm -rf vendor/composer vendor/autoload.php
	composer dump-autoload

# Installs and updates the composer dependencies. If composer is not installed
# a copy is fetched from the web
.PHONY: composer
composer: autoload
	composer install --prefer-dist

# Removes the appstore build
.PHONY: clean
clean:
	rm -rf ./build

# Same as clean but also removes dependencies installed by composer, bower
.PHONY: distclean
distclean: clean
	rm -rf vendor

# Builds the source and appstore package
.PHONY: dist
dist:
	make source
	make appstore

# Builds the source package
.PHONY: source
source:
	rm -rf $(source_build_directory)
	mkdir -p $(source_build_directory)
	tar cvzf $(source_package_name).tar.gz \
	--exclude-vcs \
	../$(app_name)/appinfo \
	../$(app_name)/img \
	../$(app_name)/lib \
	../$(app_name)/LICENSE \
	../$(app_name)/README.md

# Builds the source package for the app store, ignores php tests
.PHONY: appstore
appstore:
	rm -rf $(appstore_build_directory)
	mkdir -p $(appstore_build_directory)
	tar cvzf $(appstore_package_name).tar.gz \
	--exclude-vcs \
	--exclude="../$(app_name)/build" \
	--exclude="../$(app_name)/tests" \
	--exclude="../$(app_name)/Makefile" \
	--exclude="../$(app_name)/*.log" \
	--exclude="../$(app_name)/phpunit*xml" \
	--exclude="../$(app_name)/composer.*" \
	--exclude="../$(app_name)/bower.json" \
	--exclude="../$(app_name)/karma.*" \
	--exclude="../$(app_name)/protractor\.*" \
	--exclude="../$(app_name)/.*" \
	../$(app_name) \

.PHONY: test
test: composer
	$(CURDIR)/vendor/phpunit/phpunit/phpunit -c phpunit.xml
	$(CURDIR)/vendor/phpunit/phpunit/phpunit -c phpunit.integration.xml
