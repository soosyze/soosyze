<p align="center"><a href="https://soosyze.com/" rel="noopener" target="_blank"><img src="https://soosyze.com/assets/files/logo/soosyze-name.png"></a></p>

[![License](https://img.shields.io/github/license/soosyze/soosyze.svg)](https://github.com/soosyze/soosyze/blob/master/LICENSE "LICENSE")
[![PHP from Packagist](https://img.shields.io/badge/PHP-%3E%3D5.5-%238892bf)](/README.md#version-php "PHP version 5.5 minimum")
[![CII Best Practices](https://bestpractices.coreinfrastructure.org/projects/4102/badge)](https://bestpractices.coreinfrastructure.org/projects/4102)
[![Download Soosyze CMS](https://img.shields.io/badge/download-releases%20latest-blue.svg)](https://github.com/soosyze/soosyze/releases/latest/download/soosyze.zip "Download Soosyze CMS")

* :gb: [README in English](README.md)
* :fr: [README en Français](README_fr.md)

# About

Soosyze CMS is a micro content management system without a database. It's easy to create and manage your website easily with little or no technical knowledge. It is based on an MVC micro-framework in object-oriented PHP and on a noSQL library to ensure its stability and evolution.

To encourage us to continue the development of Soosyze CMS do not hesitate to put a star :star: Github. Thank you :heart:

* :point_right: [Site](https://soosyze.com)
* :eyes: [Demo](https://demo.soosyze.com)
* :dizzy: [Extensions and themes](https://github.com/soosyze-extension)
* :speech_balloon: [Forum](https://community.soosyze.com)
* :mortar_board: [Documentations](https://github.com/soosyze/documentations)
* :green_book: [PHP Doc](https://api.soosyze.com)
* :globe_with_meridians: [Translation](https://trad.framasoft.org/project/view/soosyze?dswid=-5497)

Find us on the networks :

* :busts_in_silhouette: [Mastodon](https://mamot.fr/@soosyze)
* :telephone_receiver: [Discord](https://discordapp.com/invite/parFfTt)

# Summary

* [Screenshots](#screenshots)
* [Installation requirements](#installation-requirements)
* [Installation](#installation)
* [Configuration](#configuration)
* [License](#license)

# Screenshots

[![Screenshot of Soosyze CMS](https://soosyze.com/assets/files/screen/devices-accueil.png)](https://soosyze.com/#screenshot)

# Installation requirements

## Web Server

| Web server              | Soosyze 1.x   |
|-------------------------|---------------|
| Apache HTTP Server 2.2+ | ✓ Supported   |
| Ngnix 1+                | ✓ Supported*  |
| IIS                     | ✓ Supported** |

*For Nginx, see the [installation recommendation](#ngnix)
**For IIS, see the [installation recommendation](#iis)

## PHP version

| PHP version                 | Soosyze 1.x   |
|-----------------------------|---------------|
| <= 5.4                      | ✗ Unsupported |
| 5.5 / 5.6                   | ✓ Supported   |
| 7.0 / 7.1 / 7.2 / 7.3 / 7.4 | ✓ Supported   |

With PHP 7.x, your performance in terms of memory and performance will increase by 30% to 45%. Your site will be faster and better referenced.

## Required PHP extensions

* `date` for the dates format,
* `fileinfo` for file validation,
* `filter` to validate your data,
* `gd` for image processing,
* `json` to save data and configurations,
* `mbstring` for your emails,
* `openssl` to query resources or flows in HTTPS,
* `session` to store your data (server side) from one page to another,
* `zip` to create backups and restore them in case of error.

These extensions are usually active by default. But if he missed an error message, he would come to inform you.

## Required memory

Soosyze (excluding contributor modules) requires 8MB of memory.

## Browsers support

| [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/edge/edge_48x48.png" alt="IE / Edge" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)<br/> Edge | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/firefox/firefox_48x48.png" alt="Firefox" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)<br/>Firefox | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/chrome/chrome_48x48.png" alt="Chrome" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)<br/>Chrome | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/safari/safari_48x48.png" alt="Safari" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)<br/>Safari | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/safari-ios/safari-ios_48x48.png" alt="iOS Safari" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)<br/>iOS Safari | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/samsung-internet/samsung-internet_48x48.png" alt="Samsung" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)<br/>Samsung | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/opera/opera_48x48.png" alt="Opera" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)<br/>Opera |
| --------- | --------- | --------- | --------- | --------- | --------- | --------- |
| Edge| last 10 versions| last 10 versions| last 2 versions| last 2 versions| last 2 versions| last 2 versions |

# Installation

### :bike: Simple download

To install **the production version of the Soosyze CMS**, download and uncompress the archive of the [latest version of the CMS](https://github.com/soosyze/soosyze/releases/latest/download/soosyze.zip) in the directory that will host your site.

### :car: Download via Composer

To install **the production version of Soosyze CMS** via Composer it is necessary to have:

* The installer or the binary file [Composer](https://getcomposer.org/download/),
* And the `php` command in your environment variables.

Go to the directory of your server, open a command prompt and run the command:

```sh
php composer.phar create-project soosyze/soosyze [my-directory] --stability=beta --no-dev
```

### :airplane: Download via Git & Composer

To install the production version of Soosyze CMS via Git and Composer it is necessary to have:

* Git :
  * [Windows](https://gitforwindows.org/),
  * [Mac](http://sourceforge.net/projects/git-osx-installer/)
  * Debian, Ubuntu... `sudo apt install git`,
  * Red Hat, Fedora, CentOS... `sudo yum install git`,
* The installer or the binary file [Composer](https://getcomposer.org/download/),
* And the `php` command in your environment variables.

Go to the directory of your server, open a command prompt and run the command:

Clone the repo with Git on your server,
```sh
git clone https://github.com/soosyze/soosyze.git [my-directory]
cd [my-directory]
```

Install dependencies with Composer,
```sh
composer install --no-dev
```

Or, if you use the binary file,
```sh
php composer.phar install --no-dev
```

To follow the tutorials, install the CMS at the root of your server and keep the `soosyze` default directory.

### CMS installation

Now that the source files are in the right place, open a web browser (Firefox, Chrome, Opera, Safari, Edge ...) and in the address bar, enter the following value :

* Local, [127.0.0.1/soosyze](http://127.0.0.1/soosyze),
* Online, your domain name.

The next page will come to you. Follow the instructions to install the CMS.

![Screenshot of Soosyze CMS installation page](https://soosyze.com/assets/files/screen/install-desktop.png)

That's it, the CMS is installed.

## Configuration

### Ngnix

If you use Nginx, add the following items to your server's configuration block to ensure the security of CMS Soosyze:
```
include path\soosyze\.nginx.conf;
```

### IIS

If you use IIS, **you must block access to the following directories**:

* `app/config`,
* `app/data`.

# License

Soosyze CMS is under MIT license. See the [license file](https://github.com/soosyze/soosyze/blob/master/LICENSE) for more information.