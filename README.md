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
├── admin/                    # Interface d'administration
│   ├── index.php             # Tableau de bord admin
│   ├── users.php             # Gestion des utilisateurs (+ bulk)
│   ├── resources.php         # Gestion des ressources (+ bulk)
│   ├── bulk-users.php        # Handler actions groupées utilisateurs
│   ├── bulk-resources.php    # Handler actions groupées ressources
│   ├── edit-user.php
│   ├── delete-user.php
│   └── includes/
│       └── admin-nav.php
├── includes/                 # Composants partagés
│   ├── config.php            # Configuration (charge .env)
│   ├── db.php                # Connexion PDO (singleton)
│   ├── auth.php              # Auth + CSRF + rate limiting
│   ├── functions.php         # Utilitaires, logging, email
│   ├── header.php
│   └── footer.php
├── css/
│   └── style.css
├── sql/
│   ├── schema.sql            # Schéma complet (nouvelle installation)
│   └── migrate.sql           # Migration (base existante)
├── tests/                    # Tests unitaires PHPUnit
│   ├── bootstrap.php
│   ├── FunctionsTest.php
│   └── AuthTest.php
├── var/
│   └── logs/                 # Logs applicatifs (auto-créé)
├── index.php                 # Page d'accueil
├── login.php
├── register.php
├── logout.php
├── resources.php             # Liste et recherche
├── resource-view.php         # Détail + votes + favoris + commentaires
├── dashboard.php             # Espace personnel (ressources + favoris)
├── profile.php               # Éditeur de profil
├── add-resource.php          # Ajout (avec choix brouillon/publié)
├── edit-resource.php         # Modification
├── delete-resource.php
├── vote.php                  # Endpoint toggle vote
├── favorite.php              # Endpoint toggle favori
├── comment.php               # Endpoint ajout/suppression commentaire
├── sitemap.php               # Sitemap XML dynamique
├── robots.txt
├── .env.example
├── composer.json
└── phpunit.xml
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
