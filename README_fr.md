<p align="center"><a href="https://soosyze.com/" rel="noopener" target="_blank"><img src="https://soosyze.com/assets/files/logo/soosyze-name.png"></a></p>

[![Licence](https://img.shields.io/github/license/soosyze/soosyze.svg)](https://github.com/soosyze/soosyze/blob/master/LICENSE "Licence")
[![PHP from Packagist](https://img.shields.io/badge/php-%3E%3D5.4-blue.svg)](/README.md#version-php "PHP version 5.4 minimum")
[![Télécharger Soosyze CMS](https://img.shields.io/badge/download-releases%20latest-blue.svg)](https://github.com/soosyze/soosyze/releases/latest/download/soosyze.zip "Télécharger Soosyze CMS")

* :gb: [README en Anglais](README.md)
* :fr: [README en Français](README_fr.md)

# À propos

Soosyze CMS est un micro système de gestion de contenu sans base de données. Il permet de créer et gérer votre site web facilement avec peu ou aucune connaissance technique. Il est basé sur un micro framework MVC en PHP orienté objet et une bibliothèque noSQL pour assurer sa stabilité et son évolution.

Pour nous encourager à poursuivre le développement de Soosyze CMS n'hésitez pas à mettre une étoile :star: Github. Merci :heart:

* :globe_with_meridians: [Site](https://soosyze.com)
* :eyes: [Démo](https://demo.soosyze.com)
* :dizzy: [Extensions et thèmes](https://github.com/soosyze-extension)
* :speech_balloon: [Forum](https://community.soosyze.com)
* :mortar_board: [Documentations](https://github.com/soosyze/documentations)
* :green_book: [PHP Doc](https://api.soosyze.com)

Vous pouvez également nous trouver sur les réseaux :

* :busts_in_silhouette: [Mastodon](https://mamot.fr/@soosyze)
* :telephone_receiver: [Discord](https://discordapp.com/invite/parFfTt)
* :newspaper: [Diaspora](https://framasphere.org/people/10978ab0dd6301362e322a0000053625)

# Sommaire

* [Captures d'écrans](#captures-décrans)
* [Exigences d'installation](#exigences-dinstallation)
* [Installation](#installation)
* [Configuration](#configuration)
* [Licence](#licence)

# Captures d'écrans

[![Screenshot de Soosyze CMS](https://soosyze.com/assets/files/screen/devices-accueil.png)](https://soosyze.com/#screenshot)

# Exigences d'installation

## Serveur Web

| Serveur Web             | Soosyze 1.x |
|-------------------------|-------------|
| Apache HTTP Server 2.2+ | ✓ Supporté  |
| Ngnix 1+                | ✓ Supporté* |
| IIS                     | Need test   |

*Pour Ngnix voir la [recommandation d'installation](#ngnix)

## Version PHP

| Version PHP           | Soosyze 1.x    |
|-----------------------|----------------|
| <= 5.3                | ✗ Non supporté |
| 5.4 / 5.5 / 5.6       | ✓ Supporté     |
| 7.0 / 7.1 / 7.2 / 7.3 | ✓ Supporté     |

En choisissant les versions PHP 7.x vous aurez un gain de performance sur la mémoire et le temps d'exécution de 30% à 45%. Votre site en sera plus rapide et mieux référencé.

## Extensions requises

* `date` pour le format des dates,
* `fileinfo` pour la validation de fichier,
* `filter` pour valider vos données,
* `gd` pour le traitement d'image,
* `json` pour sauvegarder les données et les configurations,
* `mbstring` pour vos emails,
* `session` pour garder en mémoire vos données (coté serveur) d'une page à l'autre.

Ces extensions sont généralement actives par défaut. Mais si l'une venait à manquer un message d'erreur viendrait vous en informer.

## Mémoire requise

Soosyze (hors modules contributeurs) nécessite 8MB de mémoire.

## Navigateurs supportés

Le thème d'administration est réalisé avec le framework Bootstrap 3 :
* [Navigateurs supportés](https://getbootstrap.com/docs/3.3/getting-started/#desktop-browsers)
* [Navigateurs mobiles supportés](https://getbootstrap.com/docs/3.3/getting-started/#mobile-devices)

## Connexion à internet

Les thèmes de base utilisent les CND suivants :

* Bootstrap 3.4.1,
* JQuery 3.2.1,
* JQuery UI 1.12.0,
* Sortable 1.8.3,
* Font Awesome 5.8.1

# Installation

### :bike: Téléchargement simple

Pour installer **la version de production de Soosyze CMS**, télécharger et décompresser l’archive de la [dernière version du CMS](https://github.com/soosyze/soosyze/releases/latest/download/soosyze.zip) dans le répertoire qui hébergera votre site.

### :car: Téléchargement via Composer

Pour installer **la version de production de Soosyze CMS** via Composer il est faut avoir :

* L’installateur ou le fichier binaire [Composer](https://getcomposer.org/download/),
* Et la commande `php` dans vos variables d’environnement.

Rendez-vous dans le répertoire de votre serveur, ouvrez une invite de commandes et lancer la commande suivante :
(*Remplacer le terme `[my-directory]` par le répertoire qui hébergera votre site.*)

```sh
php composer.phar create-project soosyze/soosyze [my-directory] --stability=alpha --no-dev
```

### :airplane: Téléchargement via Git & Composer

Pour installer **la version de production de Soosyze CMS** via Git et Composer il est faut avoir :

* L'outil de versionning Git pour :
  * [Windows](https://gitforwindows.org/),
  * [Mac](http://sourceforge.net/projects/git-osx-installer/)
  * Debian, Ubuntu et autres dérivées `sudo apt install git`,
  * Red Hat, Fedora, CentOS et autres dérivées `sudo yum install git`,
* L’installateur ou le fichier binaire [Composer](https://getcomposer.org/download/),
* Et la commande `php` dans vos variables d’environnement.

Rendez-vous dans le répertoire de votre serveur, ouvrer une invite de commandes et lancer les commandes suivantes :
(*Remplacer le terme `[my-directory]` par le répertoire qui hébergera votre site.*)

Cloner le repo avec Git sur votre serveur,
```sh
git clone https://github.com/soosyze/soosyze.git [my-directory]
cd [my-directory]
```

Installer les dépendances avec Composer (assurez-vous que l'exécutable php.exe est dans votre PATH),
```sh
composer install --no-dev
```

Ou, si vous utilisez le fichier PHAR,
```sh
php composer.phar install --no-dev
```

Pour suivre les tutoriels, je vous invite à installer le CMS à la racine de votre serveur local et conserver le répertoire par défaut `soosyze`.

### Installation du CMS

Maintenant que les fichiers sources sont au bon endroit, ouvrez un navigateur web (Firefox, Chrome, Opéra, Safarie, Edge…) et dans la barre d’adresse, entrer la valeur suivante :

*   en local, [127.0.0.1/soosyze](http://127.0.0.1/soosyze),
*   en ligne, votre nom de domaine.

La page suivante se présentera à vous. Suivez les instructions pour installer le CMS.

![Screenshot de la page d’instalaltion de SoosyzeCMS](https://soosyze.com/assets/files/screen/install-desktop.png)

Et voilà, le CMS est installé.

## Configuration

### Ngnix

Si vous utilisez Nginx, ajouter les éléments suivants au bloc de configuration de votre serveur pour assurer la sécurité de CMS Soosyze:

```
include path\soosyze\.nginx.conf;
```

# Licence

Soosyze CMS est sous licence MIT. Voir le [fichier de licence])(https://github.com/soosyze/soosyze/blob/master/LICENSE "LICENCE") pour plus d'informations.