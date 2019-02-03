<p align="center"><a href="https://soosyze.com/" rel="noopener" target="_blank"><img src="https://soosyze.com/assets/files/logo/soosyze-name.png"></a></p>

[![GitHub](https://img.shields.io/github/license/soosyze/soosyze.svg)](https://github.com/soosyze/soosyze/blob/master/LICENSE "LICENSE")
[![PHP from Packagist](https://img.shields.io/badge/php-%3E%3D5.4-blue.svg)](/README.md#version-php "PHP version 5.4 minimum")
[![Download Soosyze CMS](https://img.shields.io/badge/download-1.0.0--alpha4-blue.svg)](https://github.com/soosyze/soosyze/releases/download/1.0.0-alpha4/soosyze.zip "Download Soosyze CMS")

# À propos de Soosyze CMS

Soosyze CMS est un micro système de gestion de contenu sans base de données. Il permet de créer et gérer votre site web facilement avec peu ou aucune connaissance technique. Basé sur un micro-framework PHP et une bibliothèque noSQL pour assurer sa stabilité et son évolution.
* [Voir le site](https://soosyze.com/)

# Sommaire

* [Captures d'écrans](/README.md#captures-décrans)
* [Exigences d'installation](/README.md#exigences-dinstallation)
* [Installation](/README.md#installation)
* [License](/README.md#license)

# Captures d'écrans

![GitHub](https://soosyze.com/assets/files/screen/devices-accueil.png)

# Exigences d'installation

## Version PHP

| Version PHP                | Soosyze 1.x    |
|----------------------------|----------------|
| <= 5.3                     | ✗ Non supporté |
| 5.4 / 5.5 / 5.6            | ✓ Supporté     |
| 7.0 / 7.1 / 7.2 / 7.3      | ✓ Supporté     |

En choisissant les versions PHP 7.x vous aurez un gain de performance sur la mémoire et le temps d'exécution de 30% à 45%. Votre site en sera plus rapide.

## Extensions requises

* `json` pour l'enregistrement des données et des configurations,
* `session` pour garder en mémoire vos données (coté serveur) d'une page à l'autre,
* `mbstring` pour vos emails,
* `hash` pour crypter votre mot de passe.

## Mémoire requise

Soosyze (hors modules contributeurs) nécessite 16MB de mémoire.

## Navigateurs supportés

Le thème de base et d'administration sont réalisés avec le framework Bootstrap 3 :
* [Navigateurs supportés](https://getbootstrap.com/docs/3.3/getting-started/#desktop-browsers)
* [Navigateurs mobiles supportés](https://getbootstrap.com/docs/3.3/getting-started/#mobile-devices)

## Connexion à internet

Le thème de base et d'administration se décharge d'une partie des bibliothèques d'affichages (front-end) en fesant appel à des CND (Content delivery network) :
* Bootstrap 3.3.7
* JQuery 3.2.1,
* JQuery UI 1.12.0
Pour l'affichage complet des thèmes de bases vous devez donc avoir une connexion réseau pour que ces bibliothèques soient utilisées.

# Installation

## Téléchargement rapide

Télécharger et décompresser l'archive de la [dernière version du CMS](https://github.com/soosyze/soosyze/releases/download/1.0.0-alpha4/soosyze.zip ) dans le répertoire qui hébergera votre site.

## Téléchargement via Git & Composer

Remplacer le terme `[my-directory]` par le répertoire qui hébergera votre site.

Cloner le repo avec Git sur votre serveur,
```sh
git clone https://github.com/soosyze/soosyze.git [my-directory]
```

Placer vous dans le répértoire de votre projet,
```sh
cd [my-directory]
```

Installer les dépendances avec [Composer](https://getcomposer.org/) (assurez-vous que l'exécutable php.exe est dans votre PATH),
```sh
composer install --no-dev
```

Ou, si vous utilisez le fichier PHAR,
```sh
php composer.phar install --no-dev
```

## Installation du CMS

Ouvrer votre navigateur web et rendez-vous sur l'URL de votre site,
Renseigniez votre email, nom, prénom et mot de passe dans le formulaire d'installation et cliquez sur **Installer**,
Ne reste plus qu'à vous connecter et gérer votre site.

# License

Soosyze CMS est sous licence MIT. Voir le [fichier de licence](https://github.com/soosyze/soosyze/blob/master/LICENSE "LICENSE") pour plus d'informations.