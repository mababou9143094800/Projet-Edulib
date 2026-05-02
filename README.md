# EduLib

Plateforme de partage de ressources pédagogiques pour les étudiants d'EFREI, conçue dans une démarche de numérique durable.

## Présentation

EduLib est une bibliothèque centralisée où les étudiants peuvent déposer, partager et consulter des notes de cours, résumés, exercices et autres supports pédagogiques organisés par matière. L'application privilégie la sobriété numérique : moins de 200 Ko par page, pas de framework JavaScript, pas de traceurs tiers.

## Fonctionnalités

- Dépôt et consultation de ressources pédagogiques
- Recherche plein texte et filtrage par catégorie
- Authentification avec contrôle d'accès par rôle (utilisateur / administrateur)
- Tableau de bord personnel et gestion de profil
- Interface d'administration (statistiques, gestion des utilisateurs)
- Protection CSRF et prévention des injections SQL

## Stack technique

| Couche | Technologie |
|---|---|
| Backend | PHP (vanilla, sans framework) |
| Base de données | MySQL 8.0+ avec PDO |
| Frontend | HTML5 / CSS3 (sans framework JS) |
| Authentification | Sessions PHP + bcrypt |

## Structure du projet

```
Projet-Edulib/
├── admin/              # Interface d'administration
│   ├── index.php       # Tableau de bord admin
│   ├── users.php       # Gestion des utilisateurs
│   ├── edit-user.php
│   └── delete-user.php
├── includes/           # Composants partagés
│   ├── config.php      # Configuration (BDD, constantes)
│   ├── db.php          # Connexion PDO (singleton)
│   ├── auth.php        # Fonctions d'authentification
│   ├── functions.php   # Fonctions utilitaires
│   ├── header.php
│   └── footer.php
├── css/
│   └── style.css
├── sql/
│   └── schema.sql      # Schéma BDD + données initiales
├── index.php           # Page d'accueil
├── login.php
├── register.php
├── logout.php
├── resources.php       # Liste et recherche de ressources
├── resource-view.php   # Détail d'une ressource
├── dashboard.php       # Espace personnel
├── profile.php         # Éditeur de profil
├── add-resource.php    # Ajout de ressource
├── edit-resource.php   # Modification de ressource
└── delete-resource.php
```

## Installation

### Prérequis

- PHP 8.0+
- MySQL 8.0+
- Serveur web (Apache, Nginx ou PHP built-in server)

### Étapes

1. Cloner le dépôt dans le répertoire web de votre serveur :
   ```bash
   git clone https://github.com/mababou9143094800/Projet-Edulib.git
   cd Projet-Edulib
   ```

2. Créer la base de données et importer le schéma :
   ```sql
   CREATE DATABASE edulib CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
   ```bash
   mysql -u root -p edulib < sql/schema.sql
   ```

3. Configurer la connexion à la base de données dans [includes/config.php](includes/config.php) :
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'edulib');
   define('DB_USER', 'root');
   define('DB_PASS', 'votre_mot_de_passe');
   ```

4. Lancer le serveur (développement) :
   ```bash
   php -S localhost:8000
   ```

5. Ouvrir [http://localhost:8000](http://localhost:8000) dans un navigateur.

## Base de données

Le schéma comprend trois tables :

- **users** — id, nom, prenom, email (unique), password (bcrypt), role (user/admin), created_at
- **categories** — id, nom, slug — pré-remplie avec 8 matières (Informatique, Mathématiques, Physique, Électronique, Gestion de projet, Langues, Économie, Autre)
- **resources** — id, titre, description, contenu, categorie\_id (FK), auteur\_id (FK), created\_at, updated\_at

## Catégories disponibles

Informatique · Mathématiques · Physique · Électronique · Gestion de projet · Langues · Économie · Autre

## Sécurité

- Mots de passe hachés avec `password_hash` (bcrypt)
- Requêtes préparées PDO (protection injection SQL)
- Tokens CSRF sur tous les formulaires POST
- Échappement HTML systématique (XSS)
- Cookies de session en mode `HttpOnly` + `SameSite=Strict`
- Validation stricte des paramètres d'URL

## Auteur

Mathieu Dilhan — EFREI ING1  
Projet Numérique Durable
