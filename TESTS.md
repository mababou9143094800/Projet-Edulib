# Guide de test - Nouvelle architecture

## 🧪 Tests manuels

Après le déploiement, tester les chemins suivants pour valider la migration d'architecture.

### 1. Navigation générale

- [ ] `/` - Page d'accueil (index.php racine)
  - Vérifier que les boutons CTA pointent vers `/frontend/...`
  - Admin devrait voir lien `/admin/` s'il est connecté

### 2. Authentification (Frontend)

- [ ] `/frontend/login.php` - Page de connexion
  - Test login valide → redirection `/frontend/dashboard.php`
  - Test identifiants invalides → message d'erreur

- [ ] `/frontend/register.php` - Inscription
  - Test inscription valide → redirection `/frontend/dashboard.php`
  - Test email doublé → message d'erreur
  - Test password weak → message d'erreur

- [ ] `/frontend/dashboard.php` - Tableau de bord
  - Accès sans login → redirection `/frontend/login.php`
  - Onglet "Ressources" → liste ressources de l'utilisateur
  - Onglet "Favoris" → liste ressources favoritées

- [ ] `/frontend/profile.php` - Profil utilisateur
  - Changer nom/prénom
  - Changer email
  - Changer password
  - Supprimer compte (avec confirmation)

- [ ] `/backend/logout.php` - Déconnexion
  - Vérifier que session est supprimée
  - Redirection vers `/frontend/login.php`

### 3. Ressources (Frontend)

- [ ] `/frontend/resources.php` - Listing ressources
  - Filtres par catégorie
  - Recherche par titre/description
  - Pagination (12 par page)
  - Lien vers `/frontend/resource-view.php?id=X`

- [ ] `/frontend/resource-view.php?id=1` - Détail ressource
  - Affichage titre, description, auteur, date
  - Vote (POST → `/backend/vote.php`)
  - Favoris (POST → `/backend/favorite.php`)
  - Commentaires (POST → `/backend/comment.php`)

### 4. Actions Ressources (Backend)

- [ ] `/backend/add-resource.php` - Créer ressource
  - POST avec titre, description, catégorie, image
  - Redirection → `/frontend/resource-view.php?id=X` (nouvelle ressource)

- [ ] `/backend/edit-resource.php?id=X` - Éditer ressource
  - GET → formulaire préchargé
  - POST → mise à jour ressource
  - Redirection → `/frontend/resource-view.php?id=X`

- [ ] `/backend/delete-resource.php` - Supprimer ressource
  - Vérif ownership ou admin
  - POST → suppression, redirection `/frontend/dashboard.php`

- [ ] `/backend/delete-image.php` - Supprimer image
  - POST avec image_id
  - Suppression fichier + DB
  - Redirection vers referer

- [ ] `/backend/vote.php` - Voter
  - POST → toggle upvote
  - INSERT/DELETE en BD votes table
  - Redirection vers referer

- [ ] `/backend/favorite.php` - Favoris
  - POST → toggle favoris
  - INSERT/DELETE en BD favorites table
  - Redirection vers referer

- [ ] `/backend/comment.php` - Commentaires
  - POST avec texte + parent_id (optionnel)
  - Max 1 level de nesting
  - Email notification à auteur ressource
  - Redirection vers referer

### 5. Admin (Admin)

**Permissions:**
- [ ] Accès sans login → redirection `/frontend/login.php`
- [ ] Accès user simple → erreur 403 Forbidden
- [ ] Accès admin → OK

**Pages:**
- [ ] `/admin/index.php` - Tableau de bord admin
  - Statistiques: users, admins, resources, categories
  - Derniers users ajoutés
  - Dernières ressources ajoutées
  - Lien nav vers `/admin/users.php` et `/admin/resources.php`

- [ ] `/admin/users.php` - Gestion users
  - Recherche par nom/email
  - Tableau avec checkbox (select all)
  - Actions individuelles: Modifier, Supprimer
  - Bulk actions: Supprimer, Promouvoir, Rétrograder
  - Pagination

- [ ] `/admin/resources.php` - Gestion resources (placeholder)
  - Message "À compléter"

- [ ] Navigation admin (`admin/includes/admin-nav.php`)
  - Affichée dans la sidebar de tous les admin pages
  - Lien actif sur la page courante
  - Lien "Mon espace" → `/frontend/dashboard.php`
  - Lien "Déconnexion" → `/backend/logout.php`

### 6. Vérifications de sécurité

- [ ] CSRF tokens présents sur tous les formulaires POST
- [ ] Requêtes préparées PDO (pas de SQL injection possible)
- [ ] Passwords hashés en bcrypt (pas de plaintext)
- [ ] Ownership vérifiée (utilisateur ne peut edit/delete que ses ressources)
- [ ] Admin check sur pages admin
- [ ] XSS protection avec `e()` sur tous les outputs
- [ ] Headers de sécurité présents (X-Frame-Options, CSP, etc.)

### 7. Vérifications de fichiers

- [ ] Tous les fichiers dans les bons répertoires
  ```
  frontend/ → 6 fichiers + includes/
  backend/ → 8 fichiers + includes/
  admin/ → 7 fichiers + includes/
  ```

- [ ] Chemins include relatifs corrects
  ```php
  // Frontend pages
  require_once __DIR__ . '/../backend/includes/...';
  
  // Admin pages
  require_once __DIR__ . '/../backend/includes/...';
  
  // Backend includes
  require_once __DIR__ . '/includes/...';
  ```

- [ ] URLs de redirection adaptées
  - Pas de redirection vers anciennes URLs (root)
  - Toutes les redirects utilisent `/frontend/...`, `/backend/...`, `/admin/...`

- [ ] CSS et assets accessibles
  - `/css/style.css` disponible et chargé
  - Images uploadées accessibles dans `/uploads/resources/`

### 8. Vérifications de base de données

- [ ] Schéma appliqué avec `database/schema.sql`
- [ ] Tables existantes: users, resources, categories, votes, favorites, comments
- [ ] Colonnes correctes et types adaptés
- [ ] Indexes créés sur colonnes fréquemment requêtées

### 9. Performance

- [ ] Page d'accueil < 200 Ko
- [ ] Pas de n+1 queries (utiliser JOINs)
- [ ] Images en WebP optimisées
- [ ] Cache des requêtes côté client

### 10. Cas d'erreur

- [ ] 404 sur pages inexistantes
- [ ] 403 sur pages sans permission
- [ ] 500 avec message approprié (log en var/logs/)
- [ ] Messages flash affichés correctement

## 📊 Checklist finale

| Domaine | Test | Status |
|---------|------|--------|
| **Frontend** | Login/Register | [ ] |
| | Dashboard | [ ] |
| | Resources listing | [ ] |
| | Resource view | [ ] |
| **Backend** | Add/Edit/Delete Resource | [ ] |
| | Vote/Favorite | [ ] |
| | Comments | [ ] |
| | Delete Image | [ ] |
| **Admin** | Dashboard | [ ] |
| | Users management | [ ] |
| | Permissions | [ ] |
| **Security** | CSRF tokens | [ ] |
| | SQL injection | [ ] |
| | XSS protection | [ ] |
| | Authentication | [ ] |
| **Structure** | Chemins includes | [ ] |
| | URLs redirects | [ ] |
| | Fichiers dans bons répertoires | [ ] |
| **Database** | Schema appliqué | [ ] |
| | Données test OK | [ ] |

## 🐛 Troubleshooting

### Page blanche
- Vérifier les logs dans `var/logs/`
- Vérifier que les chemins include sont corrects
- Vérifier la connexion base de données dans `.env`

### 404 sur ressources
- Vérifier que CSS/images sont accessibles
- Vérifier les permissions sur `/uploads/`

### Erreur authentification
- Vérifier que `.env` est configuré
- Vérifier que la table `users` existe
- Tester avec credentials de test

### Erreur admin
- Vérifier que l'utilisateur est admin (`role = 'admin'`)
- Vérifier `require_admin()` appelé

---

**À exécuter avant déploiement en production !**
