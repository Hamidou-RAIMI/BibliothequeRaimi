# Compréhension du Projet Bibliothèque Numérique

## 1. Contexte Général
C'est une **application web de bibliothèque numérique responsive** développée pour un établissement (bibliothèque publique, universitaire, etc.), inspirée des bonnes pratiques de la Bibliothèque Numérique Nationale du Bénin.

## 2. Objectif Principal
Fournir un accès centralisé à un catalogue de références documentaires numériques, avec un système de dépôt collaboratif et de validation.

---

## 3. Fonctionnalités Clés

### 3.1 Pour tous les utilisateurs (visiteurs inclus)
- Consultation du catalogue de références
- Recherche simple et avancée
- Visualisation des fiches descriptives des références accessibles au public

### 3.2 Pour les utilisateurs inscrits
- Lecture en ligne des documents autorisés
- Téléchargement des ressources disponibles
- Soumission de demandes de dépôt de nouvelles références
- Suivi de l'état de ses demandes (en attente, validée, refusée, publiée)
- Consultation des justifications liées aux décisions
- Mise à jour de ses informations de profil

### 3.3 Pour les Responsables RH
- Consultation de la liste des utilisateurs inscrits
- Création, modification, activation, désactivation ou suppression de comptes
- Gestion des profils et des informations utilisateurs
- Attribution ou retrait de certains rôles/autorisations
- Réinitialisation des accès et assistance aux utilisateurs
- Consultation de l'historique des actions de gestion des comptes

### 3.4 Pour les Responsables Chargés de la Gestion des Demandes
- Accès aux demandes qui lui sont attribuées
- Consultation des informations et documents associés
- Vérification de la conformité, qualité et pertinence des références proposées
- Validation des demandes et transmission à l'administrateur pour publication
- Refus des demandes avec justification détaillée obligatoire
- Participation à une seconde évaluation si demandé par l'administrateur

### 3.5 Pour les Administrateurs
- Gestion complète des comptes utilisateurs et de leurs rôles
- Gestion des responsables de validation et des affectations des demandes
- Consultation de l'ensemble des demandes de dépôt et de leur historique
- Publication des références validées
- Confirmation ou invalidation d'un refus émis par un responsable
- Justification obligatoire en cas de passage outre d'un refus
- Demande d'un second avis en réaffectant une demande à un autre responsable
- Rejet définitif ou approbation d'une demande après examen des avis
- Modification ou suppression des références publiées
- Administration des catégories, auteurs, mots-clés et autres données de référence
- Consultation des tableaux de bord, statistiques et journaux d'activité

---

## 4. Circuit de Validation des Demandes de Dépôt

1. **Soumission** : Un utilisateur inscrit soumet une demande de dépôt
2. **Première vérification** : La demande est affectée à un Responsable Chargé de la Gestion des Demandes
3. **Décision du responsable** :
   - Si conforme → Valide et transmet à l'administrateur
   - Si non conforme → Refuse avec justification → Transmet à l'administrateur
4. **Examen par l'administrateur** :
   - Si refus confirmé → Rejette définitivement la demande
   - Si refus invalidé → Approuve et publie la demande (avec justification)
   - Si besoin → Demande un second avis à un autre responsable
5. **Traitement du second avis** :
   - Si second refus confirmé → Administrateur rejette définitivement
   - Si second avis favorable → Administrateur valide et publie

---

## 5. Structure Technique

### 5.1 Stack Technique
- **Backend** : Laravel 12
- **Frontend** : Vue.js + Taro (pour mini-programmes cross-platform)
- **Base de données** : SQLite (par défaut)

### 5.2 Modèles Principaux
1. `User` : Utilisateurs (tous profils)
2. `Categorie` : Catégories de références
3. `Author` : Auteurs des références
4. `Publisher` : Éditeurs des références
5. `Reference` : Références documentaires (livres, mémoires, thèses, articles, etc.)

### 5.3 Tables de Relation & Fonctionnalités
- `reference_author` : Relation many-to-many entre références et auteurs
- `reference_keywords` : Mots-clés associés aux références
- `deposite_requests` : Demandes de dépôt de références
- `deposite_request_reviews` : Avis/justifications sur les demandes de dépôt
- `notifications` : Notifications aux utilisateurs
- `activity_logs` : Journal des activités système
- `downloads` : Historique des téléchargements
- `views` : Historique des consultations
