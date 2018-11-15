# Soosyze CMS

![GitHub](https://img.shields.io/github/license/soosyze/soosyze.svg)
![GitHub tag](https://img.shields.io/github/tag/soosyze/soosyze.svg)
![PHP from Packagist](https://img.shields.io/badge/php-%3E%3D5.4-blue.svg)
![GitHub code size in bytes](https://img.shields.io/github/languages/code-size/soosyze/soosyze.svg)

Soosyze CMS est un micro système de gestion de contenu sans base de données. Il permet de créer et gérer votre site web facilement avec peu ou aucune connaissance technique. Basé sur un micro-framework PHP et une bibliothèque noSQL pour assurer sa stabilité et son évolution.

# Sommaire

* [Requirements](/README.md#requirements)
* [Installation](/README.md#installation)
* [License](/README.md#license)

# Requirements

* PHP =>5.4, support PHP 5.6, 7.0, 7.1
* La permission d'écrire et lire les fichiers,
* L'extension `json` activé.

# Installation

## Git & Composer

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

Ouvrer votre navigateur et rendez-vous sur l'URL de votre site,
Renseigniez votre email, nom, prénom et mot de passe dans le formulaire d'installation et cliquez sur 'Installer',
Ne reste plus qu'à vous connecter et gérer votre site.

# License

Soosyze CMS est sous licence MIT. Voir le fichier de licence pour plus d'informations.