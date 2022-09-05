SHELL=bash
SOURCE_DIR = $(shell pwd)
PACK_DIR = ./soosyze
BIN_DIR = ${SOURCE_DIR}/bin
COMPOSER = composer

_CYAN=\033[36m
_GREEN=\033[32m
_END=\033[0m

define printSection
	@printf "${_CYAN}\n══════════════════════════════════════════════════\n${_END}"
	@printf "${_CYAN} $1 ${_END}"
	@printf "${_CYAN}\n══════════════════════════════════════════════════\n${_END}"
endef

#  _   _      _
# | | | |    | |
# | |_| | ___| |_ __
# |  _  |/ _ \ | '_ \
# | | | |  __/ | |_) |
# \_| |_/\___|_| .__/
#              | |
#              |_|

.PHONY: help
help: ## Displays the list of commands
	$(call printSection,HELP)
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) \
	| sort \
	| awk 'BEGIN {FS = ":.*?## "}; {printf "${_GREEN}%-20s${_END} %s\n", $$1, $$2}' \
	| sed -e 's/##//'

.PHONY: pack
pack: ## Displays the list of commands
	$(call printSection,PACK)
	rm -rf ${PACK_DIR} ${PACK_DIR}.zip
	$(COMPOSER) create-project soosyze/soosyze ${PACK_DIR} --no-dev
	rm -rf \
		${PACK_DIR}/app/config/test \
		${PACK_DIR}/app/data/test \
		${PACK_DIR}/tests \
		${PACK_DIR}/composer.lock \
		${PACK_DIR}/vendor/autoload.php \
		${PACK_DIR}/vendor/composer/*.{php,json}
	mv -f ${PACK_DIR}/vendor/phpmailer/phpmailer/language/phpmailer.lang-fr.php ${PACK_DIR}/vendor/phpmailer/phpmailer/phpmailer.lang-fr.php
	rm -f ${PACK_DIR}/vendor/phpmailer/phpmailer/language/*.php
	mv -f ${PACK_DIR}/vendor/phpmailer/phpmailer/phpmailer.lang-fr.php ${PACK_DIR}/vendor/phpmailer/phpmailer/language/phpmailer.lang-fr.php
	zip -qq ${PACK_DIR}.zip -r ${PACK_DIR}
	rm -rf ${PACK_DIR}

#  _____
# /  __ \
# | /  \/ ___  _ __ ___  _ __   ___  ___  ___ _ __
# | |    / _ \| '_ ` _ \| '_ \ / _ \/ __|/ _ \ '__|
# | \__/\ (_) | | | | | | |_) | (_) \__ \  __/ |
#  \____/\___/|_| |_| |_| .__/ \___/|___/\___|_|
#                       | |
#                       |_|

.PHONY: install
install: clean-vendor install-vendor ## Install the project

.PHONY: clean-vendor
clean-vendor: ## Remove composer dependencies
	$(call printSection,CLEAN VENDOR)
	rm -rf ${SOURCE_DIR}/vendor

.PHONY: install-vendor
install-vendor: ${SOURCE_DIR}/vendor/composer/installed.json ## Install composer dependencies

${SOURCE_DIR}/vendor/composer/installed.json:
	$(call printSection,INSTALL VENDOR)
	$(COMPOSER) --no-interaction install --ansi --no-progress --prefer-dist

#  _____             _ _ _ 
# |  _  |           | (_) |
# | | | |_   _  __ _| |_| |_ _   _
# | | | | | | |/ _` | | | __| | | |
# \ \/' / |_| | (_| | | | |_| |_| |
#  \_/\_\\__,_|\__,_|_|_|\__|\__, |
#                             __/ |
#                            |___/

.PHONY: cs-fix
cs-fix: ## Checks if code style is compliant
	$(call printSection,PHP-CS-FIXER)
	${BIN_DIR}/php-cs-fixer fix

.PHONY: rector
rector: ## Checks if the quality of the code is compliant
	$(call printSection,RECTOR)
	${BIN_DIR}/rector process --dry-run

.PHONY: phpstan
phpstan: ## Check if the data types are compliant
	$(call printSection,PHPSTAN)
	${BIN_DIR}/phpstan --memory-limit=1G analyse

#  _____         _
# |_   _|       | |
#   | | ___  ___| |_
#   | |/ _ \/ __| __|
#   | |  __/\__ \ |_
#   \_/\___||___/\__|

.PHONY: test
test: ## Run unit tests
	$(call printSection,TEST phpunit)
	${BIN_DIR}/phpunit
