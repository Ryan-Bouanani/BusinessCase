# La Nîmes'Alerie BusinessCase
## Présentation du projet

Création d'un site e-commerce pour une animalerie en php Symfony. Projet présenté au jury lors de mon exament qui ma permis de valider mon diplôme de Développeur Web et Web Mobile.

Contient une partie backoffice disponible unniquement pour l'administrateur du site. Elle permet de gérer l'administration du site (produits, catégories et sous catégories, marques, promotions, commandes, clients), Mis en place d'une api avec API Platform, gestion de rôles utilisateurs, barre de recherche, gestion du compte, commandes fictif ...
La plateforme est divisée en trois parties :  
* Le front office du e-commerce
* Le back office du e-commerce
* Le dashboard d’analyse de données

Comme tout e-commerce, il est possible pour l’utilisateur de se connecter, de  
s’inscrire mais aussi de faire une demande de renvoi de mot de passe.  
L’authentification est sécurisée avec token et l’intégralité des mots de passes sont hashés.  

La partie frontend (front office et back office) du projet est réalisée avec  
[Symfony](https://symfony.com/) et [API Platform](https://api-platform.com/).  

## Technologies utilisées

* Symfony (6.0.18)
* PHP (8.1.13)
* MySQL (8.0.31)

## Installation

Cloner le projet sur votre machine :  

```git clone https://github.com/LaurentCNS/La-nimes-alerie-Business-case```

Creer un fichier `.env.local` à la racine et configurez vos propres informations de connexion de votre base de données.  
Vous pouvez consulter le fichier `.env` pour voir les informations par défaut.  

La ligne à copier et à modifier sur votre fichier `.env.local` est :  

```DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7&charset=utf8mb4"```

Verifier la version de php installée sur votre machine (8.1.13) :

```php -v```

Installer les dépandances symfony  :  

```symfony composer install```

```yarn install```


Creer la base de données :  

```symfony console doctrine:database:create```

Faire les migrations sur la bdd :  

```symfony console doctrine:migrations:migrate```

Génerer les fixtures :  

```symfony console hautelook:fixtures:load --purge-with-truncate```  

Vous aurez besoin d'une clé pour l'authentification JWT pour l'API REST.


## Lancer le projet

Lancer le serveur symfony :  

```symfony serve```

```yarn watch```


Rendez-vous sur l'url indiqué par le serveur pour vous rendre sur la page d'accueil.  

Pour vous rendre sur api-platform, ajouter ceci après l'url de la page d'accueil:  

```/api/docs```
