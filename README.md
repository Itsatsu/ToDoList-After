# TODO & Co
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/d2e38e48c599428f9a3cab2d433462bc)](https://app.codacy.com/gh/Itsatsu/ToDoList-After/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade) [![Maintainability](https://api.codeclimate.com/v1/badges/7e77d5a17342b9bd5770/maintainability)](https://codeclimate.com/github/Itsatsu/ToDoList-After/maintainability) [![Coverage](https://sonarcloud.io/api/project_badges/measure?project=Itsatsu_ToDoList-After&metric=coverage)](https://sonarcloud.io/summary/new_code?id=Itsatsu_ToDoList-After) [![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=Itsatsu_ToDoList-After&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=Itsatsu_ToDoList-After)

Ceci est un projet pour le parcours de formation [Développeur d'application PHP/Symfony sur Openclassroom](https://openclassrooms.com/fr/paths/59-developpeur-dapplication-php-symfony).
Le but de ce projet est de reprendre un projet existant et de le corriger, l'améliorer et le documenter.

### Contexte du projet

Todo


## Fichier présent dans le projet
- schema de base de donnée
- Diagrammes de séquence
- Diagrammes de cas d'utilisation
- Fichier nécessaires pour le bon fonctionnement du projet


## Prérequis / technologies utilisées ⚙️

Avant de commencer, assurez-vous d'avoir les éléments suivants installés sur votre système :
- Symfony 7.1
- [PHP](https://www.php.net/) 8.3.8 ou supérieur
- [Composer](https://getcomposer.org/) 2.7.1 ou supérieur (pour l'installation des dépendances)
- [MySQL](https://www.mysql.com/) 8.4.0 ou supérieur (ou tout autre système de gestion de base de données compatible)
- [Symfony CLI](https://symfony.com/download) (pour lancer le projet)
- Fichier zip ou clone du projet
- Yarn 1.22.22 ou supérieur

## Installation et lancement du projet 🚀

1. Clonez ou téléchargez le repository GitHub dans le dossier voulu
2. Installez les dépendances du projet avec la commande suivante :
   ```composer install```
3. Configurez vos variables d'environnement dans le fichier .env) à la racine du projet:
- Modifier la variable APP_ENV en dev ou prod selon votre environnement
- Ajouter une clé dans APP_SECRET
- Modifier la variable MAILER_DSN avec vos informations de connexion à votre serveur SMTP
- Modifier la variable DATABASE_URL avec vos informations de connexion à la base de donnée

4. Créez la base de données avec la commande suivante (assurez-vous que votre serveur MySQL local soit en cours d'exécution et de ne pas avoir de base de données nommé snowtricks)
   ```php bin/console doctrine:database:create```
5. Créez la structure de la base de données avec la commande suivante :
   ```php bin/console doctrine:schema:create```
6. Installez les fixtures avec la commande suivante :
   ```php bin/console doctrine:fixtures:load```
7. Installez les dépendances front-end avec la commande suivante :
   ```yarn install```
8. Compilez les assets avec la commande suivante :
   ```yarn dev```
9. Lancez le serveur avec la commande suivante :
   ```symfony serve```

10. Vous pouvez accéder à l'index du projet via l'URL suivante :
   ```http://IpDuServeur:8000/```


## Connexion
- Pour vous connecter en tant qu'administrateur, vous pouvez utiliser les identifiants suivants :
  - Email : admin@email.fr
  - Mot de passe : passpass
- Pour vous connecter en tant qu'utilisateur, vous pouvez utiliser les identifiants suivants :
  - Email : user@email.fr
  - Mot de passe : passpass

## Pour Lancez les tests
- Pour lancer les tests et avoir un fichier xml, vous pouvez utiliser la commande suivante :
  ```php bin/phpunit --coverage-clover coverage.xml ```
- Pour lancer les tests et avoir un fichier html, vous pouvez utiliser la commande suivante :
  ```php bin/phpunit --coverage-html tests/result/ ```