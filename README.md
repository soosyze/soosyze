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

## Version PHP

| Version PHP                | Soosyze 1.x    |
|----------------------------|----------------|
| < 5.3                      | ✗ Non supporté |
| 5.4 / 5.5 / 5.6            | ✓ Supporté     |
| 7.0 / 7.1 / 7.2 / 7.3.0RC3 | ✓ Supporté     |

En choisissant les versions PHP 7.x vous aurez un gain de performance sur la mémoire et le temps d'exécution de 30% à 45%. Votre site en sera plus rapide.

## Extensions requis

* `json` pour l'enregistrement des données et des configurations,
* `session` pour garder en mémoire vos données (coté serveur) d'une page à l'autre,
* `hash` pour crypter votre mot de passe.

## Mémoire requise

Soosyze (hors modules contributeurs) nécessite 16MB de mémoire.

## Navigateur supporté

Le thème de base et d'administration sont réalisés avec le framework Bootstrap 3 :
* [Navigateurs supportés](https://getbootstrap.com/docs/3.3/getting-started/#desktop-browsers)
* [Navigateurs mobiles supportés](https://getbootstrap.com/docs/3.3/getting-started/#mobile-devices)


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