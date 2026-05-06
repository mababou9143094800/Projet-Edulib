# Projet-Edulib - Architecture Réorganisée

Ce document décrit la nouvelle structure du projet Projet-Edulib avec une séparation claire entre le frontend, le backend et l'administration.

## 📁 Structure du Projet

```
Projet-Edulib/
├── frontend/                # Interface utilisateur
│   ├── index.php           # (Non - à la racine)
│   ├── login.php           # Connexion
│   ├── register.php        # Inscription
│   ├── dashboard.php       # Tableau de bord utilisateur
│   ├── profile.php         # Gestion du profil
│   ├── resources.php       # Liste des ressources
│   ├── resource-view.php   # Détail d'une ressource
│   ├── css/
│   │   └── style.css
│   └── includes/
│       ├── header.php
│       └── footer.php
│
├── backend/                 # Logique serveur
│   ├── add-resource.php    # Ajouter une ressource
│   ├── edit-resource.php   # Modifier une ressource
│   ├── delete-resource.php # Supprimer une ressource
│   ├── vote.php            # Voter pour une ressource
│   ├── favorite.php        # Ajouter aux favoris
│   ├── comment.php         # Gérer les commentaires
│   ├── delete-image.php    # Supprimer une image
│   ├── logout.php          # Déconnexion
│   ├── includes/
│   │   ├── config.php      # Configuration
│   │   ├── db.php          # Connexion base de données
│   │   ├── auth.php        # Authentification
│   │   ├── functions.php   # Fonctions utilitaires
│   │   ├── header.php      # Entête (avec nav)
│   │   └── footer.php      # Pied de page
│   ├── admin/              # Ancien - À supprimer
│   └── tests/              # Tests unitaires
│       ├── AuthTest.php
│       └── FunctionsTest.php
│
├── admin/                   # Interface d'administration
│   ├── index.php           # Tableau de bord admin
│   ├── users.php           # Gestion des utilisateurs
│   ├── resources.php       # Gestion des ressources
│   ├── edit-user.php       # Éditer un utilisateur
│   ├── delete-user.php     # Supprimer un utilisateur
│   ├── bulk-users.php      # Actions groupées sur utilisateurs
│   ├── bulk-resources.php  # Actions groupées sur ressources
│   └── includes/
│       └── admin-nav.php   # Navigation admin
│
├── database/               # Base de données
│   ├── schema.sql          # Schéma initial
│   └── migrate.sql         # Migrations
│
├── tests/                  # Tests
│   ├── Backend/
│   ├── Frontend/
│   └── Unit/
│
├── var/                    # Fichiers dynamiques
│   ├── cache/
│   └── logs/
│
├── uploads/                # Fichiers uploadés
│   └── resources/          # Images des ressources
│
├── docs/                   # Documentation
│   ├── UML.png
│   ├── wireframes.png
│   ├── ecoindex.png
│   └── rapport.pdf
│
├── index.php               # Page d'accueil (RACINE)
├── .env.example            # Variables d'environnement
├── .env                    # Variables d'environnement (local)
├── composer.json           # Dépendances PHP
├── phpunit.xml             # Configuration PHPUnit
├── robots.txt              # Directives pour les robots
└── README.md               # Ce fichier
```

## 🔄 Routage des URLs

| URL | Fichier | Type |
|-----|---------|------|
| `/` | `index.php` | Frontend - Accueil |
| `/frontend/login.php` | `frontend/login.php` | Frontend - Auth |
| `/frontend/register.php` | `frontend/register.php` | Frontend - Auth |
| `/frontend/dashboard.php` | `frontend/dashboard.php` | Frontend - Utilisateur |
| `/frontend/profile.php` | `frontend/profile.php` | Frontend - Utilisateur |
| `/frontend/resources.php` | `frontend/resources.php` | Frontend - Ressources |
| `/frontend/resource-view.php` | `frontend/resource-view.php` | Frontend - Ressource |
| `/backend/add-resource.php` | `backend/add-resource.php` | Backend - API |
| `/backend/edit-resource.php` | `backend/edit-resource.php` | Backend - API |
| `/backend/delete-resource.php` | `backend/delete-resource.php` | Backend - API |
| `/backend/vote.php` | `backend/vote.php` | Backend - API |
| `/backend/favorite.php` | `backend/favorite.php` | Backend - API |
| `/backend/comment.php` | `backend/comment.php` | Backend - API |
| `/backend/delete-image.php` | `backend/delete-image.php` | Backend - API |
| `/backend/logout.php` | `backend/logout.php` | Backend - Auth |
| `/admin/index.php` | `admin/index.php` | Admin - Dashboard |
| `/admin/users.php` | `admin/users.php` | Admin - Gestion |
| `/admin/resources.php` | `admin/resources.php` | Admin - Gestion |

## 🚀 Configuration et Lancement

### 1. Installer les dépendances
```bash
composer install
```

### 2. Configurer l'environnement
```bash
cp .env.example .env
# Éditer .env avec vos paramètres
```

### 3. Initialiser la base de données
```bash
mysql -u root -p edulib < database/schema.sql
```

### 4. Lancer le serveur
```bash
php -S localhost:8000
```

### 5. Accéder à l'application
- **Accueil** : http://localhost:8000/
- **Ressources** : http://localhost:8000/frontend/resources.php
- **Connexion** : http://localhost:8000/frontend/login.php
- **Admin** : http://localhost:8000/admin/index.php

## 📝 Structure des includes

### `backend/includes/config.php`
- Charge les variables d'environnement depuis `.env`
- Définit les constantes de base de données et configuration
- Initialise les paramètres PHP de sécurité

### `backend/includes/db.php`
- Fournit la fonction `db()` pour accéder à la base de données
- Connexion PDO avec charset UTF-8

### `backend/includes/auth.php`
- Gestion de l'authentification et des sessions
- Fonctions : `require_login()`, `require_admin()`, `is_logged_in()`, `is_admin()`, `current_user()`, `login_user()`, `logout_user()`
- Protection contre le rate-limiting

### `backend/includes/functions.php`
- Fonctions utilitaires générales
- Validation, échappement HTML, formatage de dates
- Gestion des fichiers et images
- Fonctions de notification

### `backend/includes/header.php` et `footer.php`
- Templates HTML réutilisables
- Navigation et branding
- Affichage des messages flash

## 🔐 Sécurité

- **CSRF Protection** : Tokens CSRF sur tous les formulaires POST
- **SQL Injection** : Requêtes préparées PDO
- **XSS** : Échappement HTML avec `e()`
- **Password** : Hachage bcrypt
- **Session** : HttpOnly, SameSite=Strict
- **Rate Limiting** : Protection contre le brute-force

## 📦 Commandes disponibles

```bash
# Exécuter les tests
composer test

# Générer la documentation PHPDoc
composer docs
```

## 🌱 Engagement écologique

- HTML/CSS pur - Zéro framework JavaScript
- < 200 Ko par page
- Zéro tracker - Zéro cookie tiers
- Police système - Pas de Google Fonts
- Données minimales en base de données
