# EduLib

Plateforme de partage de ressources pédagogiques pour les étudiants d'EFREI, conçue dans une démarche de numérique durable.

## Présentation

EduLib est une bibliothèque centralisée où les étudiants peuvent déposer, partager et consulter des notes de cours, résumés, exercices et autres supports pédagogiques organisés par matière. L'application privilégie la sobriété numérique : moins de 200 Ko par page, pas de framework JavaScript, pas de traceurs tiers.

## Fonctionnalités

**Ressources**
- Dépôt avec choix de visibilité (publié / brouillon)
- Recherche plein texte et filtrage par catégorie avec pagination
- Vote (upvote) et système de favoris par ressource
- Commentaires avec réponses imbriquées (1 niveau)

**Utilisateurs**
- Authentification sécurisée avec contrôle d'accès par rôle (utilisateur / administrateur)
- Tableau de bord avec onglets Mes ressources / Mes favoris
- Gestion de profil (nom, email, mot de passe)
- Protection brute-force : blocage IP après 5 tentatives échouées (fenêtre 15 min)

**Administration**
- Tableau de bord avec statistiques globales
- Gestion des utilisateurs : modifier, supprimer, promouvoir/rétrograder en masse
- Gestion des ressources : filtres, publication/dépublication et suppression en masse

**Infrastructure**
- Variables d'environnement via fichier `.env`
- Logging applicatif dans `var/logs/app.log`
- Notifications email (via `mail()`, configurable)
- Headers HTTP de cache : `ETag` + `Cache-Control` sur les pages de ressources
- `robots.txt` et `sitemap.xml` dynamique
- Suite de tests unitaires PHPUnit

**Sécurité**
- Tokens CSRF sur tous les formulaires POST
- Requêtes préparées PDO (protection injection SQL)
- Échappement HTML systématique (XSS)
- Mots de passe bcrypt
- Cookies de session `HttpOnly` + `SameSite=Strict`
- Rate limiting par IP sur le formulaire de connexion

## Stack technique

| Couche | Technologie |
|---|---|
| Backend | PHP 8.0+ (vanilla, sans framework) |
| Base de données | MySQL 8.0+ avec PDO |
| Frontend | HTML5 / CSS3 (sans framework JS) |
| Authentification | Sessions PHP + bcrypt |
| Tests | PHPUnit 11 |

## Structure du projet

```
Projet-Edulib/
├── frontend/                # Interface utilisateur
│   ├── index.php
│   ├── login.php
│   ├── register.php
│   ├── resources.php
│   ├── resource-view.php
│   ├── dashboard.php
│   ├── profile.php
│   ├── css/
│   │   └── style.css
│   └── includes/
│       ├── header.php
│       └── footer.php
│
├── backend/                 # Logique serveur (API PHP)
│   ├── auth.php
│   ├── vote.php
│   ├── favorite.php
│   ├── comment.php
│   ├── add-resource.php
│   ├── edit-resource.php
│   ├── delete-resource.php
│   └── includes/
│       ├── db.php
│       ├── config.php
│       └── functions.php
│
├── admin/                   # Interface admin
│   ├── dashboard.php
│   ├── users.php
│   ├── resources.php
│   └── reports.php
│
├── database/                # Base de données
│   ├── schema.sql
│   └── migrate.sql
│
├── docs/                    # Documentation projet
│   ├── UML.png
│   ├── wireframes.png
│   ├── ecoindex.png
│   ├── carbon-calculator.png
|   ├── Edulib_presentation.pdf
│   └── rapport.pdf
│
├── tests/                   # Tests PHPUnit
│   ├── Backend/
│   ├── Frontend/
│   └── Unit/
│
├── var/                     # Logs / cache
│   ├── cache/
│   └── logs/
│
├── .env.example
├── composer.json
├── phpunit.xml
└── README.md
```

## Installation

### Prérequis

- PHP 8.0+
- MySQL 8.0+
- Serveur web (Apache, Nginx ou PHP built-in server)
- Composer (pour les tests)

### Étapes

1. Cloner le dépôt :
   ```bash
   git clone https://github.com/mababou9143094800/Projet-Edulib.git
   cd Projet-Edulib
   ```

2. Créer le fichier de configuration :
   ```bash
   cp .env.example .env
   ```
   Éditer `.env` avec vos identifiants de base de données.

3. Créer la base de données et importer le schéma :
   ```sql
   CREATE DATABASE edulib CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
   ```bash
   mysql -u root -p edulib < sql/schema.sql
   ```

   Sur une **base existante**, utiliser la migration à la place :
   ```bash
   mysql -u root -p edulib < sql/migrate.sql
   ```

4. Lancer le serveur de développement :
   ```bash
   php -S localhost:8000
   ```

5. Ouvrir [http://localhost:8000](http://localhost:8000) dans un navigateur.

### Tests

```bash
composer install
composer test
```

## Base de données

| Table | Description |
|---|---|
| `users` | Comptes utilisateurs (bcrypt, rôle user/admin) |
| `categories` | 8 matières prédéfinies |
| `resources` | Ressources pédagogiques avec statut draft/published |
| `votes` | Upvotes par ressource et par utilisateur |
| `favorites` | Favoris par utilisateur |
| `comments` | Commentaires avec réponses imbriquées |
| `login_attempts` | Suivi des tentatives de connexion (rate limiting) |

## Variables d'environnement

Copier `.env.example` en `.env` et ajuster :

```env
DB_HOST=localhost
DB_NAME=edulib
DB_USER=root
DB_PASS=votre_mot_de_passe

SITE_NAME=EduLib

# Laisser vide pour désactiver les notifications email
MAIL_FROM=noreply@votre-domaine.fr
```

## Catégories disponibles

Informatique · Mathématiques · Physique · Électronique · Gestion de projet · Langues · Économie · Autre

## Auteur

Mathieu Dilhan — EFREI ING1  
Projet Numérique Durable

## Lien Canva

https://canva.link/9fin3brrks579hb
