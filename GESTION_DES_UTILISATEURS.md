#  Gestion des Utilisateurs - Documentation

##  Résumé
Implémentation complète de la gestion des utilisateurs pour la bibliothèque numérique, avec :
- Backend Laravel (contrôleur, routes, policy)
- Frontend Vue.js + Pinia + PrimeIcons
- Design moderne avec Tailwind CSS
- Autorisations par rôle (Admin, Responsable RH, Responsable Demande, Utilisateur)

---

##  Backend - Laravel

### 1. Fichiers Créés/Modifiés
| Fichier | Action |
|---------|--------|
| `backend/app/Http/Controllers/UserController.php` | **Créé** - Contrôleur complet avec CRUD + toggle statut |
| `backend/routes/api.php` | **Modifié** - Ajout des routes protégées |
| `backend/app/Policies/UserPolicy.php` | **Créé** - Politique d'autorisation |

---

### 2. Détails des fichiers

#### `UserController.php`
Méthodes implémentées avec **commentaires ultra-détaillés en français** :
- `index()` : Récupère la liste de TOUS les utilisateurs
- `show($id)` : Récupère UN utilisateur spécifique
- `store(Request $request)` : Crée UN nouvel utilisateur (validation incluse)
- `update(Request $request, $id)` : Modifie UN utilisateur existant
- `destroy($id)` : Supprime UN utilisateur (irréversible)
- `toggleStatus($id)` : Basculer le statut (Actif ↔ Inactif) en 1 clic

#### `api.php`
Routes ajoutées dans le groupe `auth:sanctum` (protégées par l'authentification) :
```php
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
```

#### `UserPolicy.php`
Règles d'autorisation :
- `viewAny()` : Seul **Admin** et **Responsable RH** peuvent voir la liste
- `view()` : Admin/RH ou l'utilisateur lui-même
- `create()` : Seul Admin et RH
- `update()` : Admin/RH ou l'utilisateur lui-même
- `delete()` : **Seul Admin**
- `toggleStatus()` : Admin et RH

---

##  Frontend - Vue.js

### 1. Fichiers Créés/Modifiés
| Fichier | Action |
|---------|--------|
| `frontend/src/stores/user.js` | **Créé** - Store Pinia pour les utilisateurs |
| `frontend/src/views/responsable_Rh/DashboardView.vue` | **Modifié** - Page complète de gestion |
| `frontend/src/views/admin/DashboardView.vue` | **Modifié** - Page complète de gestion (couleurs violettes) |

---

### 2. Détails des fichiers

#### `stores/user.js` (Pinia)
Fonctionnalités :
- `users` : État pour la liste des utilisateurs
- `currentUser` : État pour un utilisateur sélectionné
- `fetchUsers()` : Récupère la liste via l'API
- `fetchUser(id)` : Récupère un utilisateur
- `createUser(data)` : Crée un utilisateur
- `updateUser(id, data)` : Modifie un utilisateur
- `deleteUser(id)` : Supprime un utilisateur
- `toggleUserStatus(id)` : Basculer le statut

#### Pages de gestion (Admin + RH)
Fonctionnalités de la page :
-  Tableau des utilisateurs (nom, prénom, email, téléphone, rôle, statut)
-  Bouton "Ajouter un utilisateur" avec modale
-  Bouton "Modifier" avec modale pré-remplie
-  Bouton "Supprimer" avec confirmation (seul Admin)
-  Bouton "Actif/Inactif" pour basculer le statut en 1 clic
-  Indicateur de chargement
-  Gestion des erreurs
-  Badges de rôle colorés

---

##  Fonctionnalités Principales

### 1. CRUD Utilisateurs
| Action | Rôles autorisés |
|--------|-----------------|
| Voir la liste | Admin, Responsable RH |
| Voir un utilisateur | Admin, RH, ou l'utilisateur lui-même |
| Créer | Admin, RH |
| Modifier | Admin, RH, ou l'utilisateur lui-même |
| Supprimer | **Seul Admin** |
| Basculer statut | Admin, RH |

### 2. Design & UX
- Responsive (mobile, tablette, desktop)
- Couleurs cohérentes (bleu pour RH, violet pour Admin)
- Icônes PrimeIcons
- Modales avec fond blur
- Badges colorés pour les rôles/statuts
- Confirmation avant suppression

---

##  Comment Utiliser

### 1. Lancer le backend
```powershell
cd backend
php artisan serve
```

### 2. Lancer le frontend
```powershell
cd frontend
npm run dev
```

### 3. Se connecter
Utilisez les comptes créés par les seeders :
- **Admin** : `admin@bibliotheque.fr` / `password`
- **Responsable RH** : `rh@bibliotheque.fr` / `password`
- **Responsable Demande** : `demande@bibliotheque.fr` / `password`
- **Utilisateur** : `user@bibliotheque.fr` / `password`

---

##  Notes Importantes
- Les mots de passe sont hachés avant d'être stockés
- Les routes API sont protégées par Sanctum
- Les politiques d'autorisation (Policy) vérifient les droits avant chaque action
- Le bouton "Supprimer" n'est visible que par l'Admin
- Tous les fichiers ont des commentaires en français pour une meilleure compréhension
