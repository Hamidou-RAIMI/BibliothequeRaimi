<?php

namespace App\Http\Controllers;

// On importe les classes nécessaires au fonctionnement du contrôleur
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * =========================================================================
     * MÉTHODE INDEX : AFFICHER LA LISTE DE TOUS LES UTILISATEURS
     * =========================================================================
     * 
     * UTILITÉ :
     * Cette méthode permet de récupérer et de renvoyer la liste complète des utilisateurs
     * inscrits dans la base de données. C'est la méthode principale pour l'affichage
     * de la page de gestion des utilisateurs.
     * 
     * PARAMÈTRES : Aucun (on récupère tout le monde !)
     * 
     * RETOUR : Une réponse JSON avec la liste des utilisateurs
     */
    public function index()
    {
        // VÉRIFICATION DES DROITS : seul Admin/RH peuvent accéder à la liste
        $this->authorize('viewAny', User::class);
        
        // Récupère TOUS les utilisateurs depuis la base de données
        // Si tu veux ajouter une pagination ou des filtres plus tard, tu peux modifier ça !
        $users = User::all();

        // On renvoie la liste des utilisateurs en format JSON
        // C'est ce que le frontend (Vue.js) va recevoir et afficher
        return response()->json([
            'success' => true,
            'message' => 'Liste des utilisateurs récupérée avec succès',
            'data' => $users
        ], 200); // 200 = Code HTTP "OK", tout s'est bien passé
    }

    /**
     * =========================================================================
     * MÉTHODE SHOW : AFFICHER UN SEUL UTILISATEUR (SPÉCIFIQUE)
     * =========================================================================
     * 
     * UTILITÉ :
     * Cette méthode permet de récupérer les informations d'UN SEUL utilisateur,
     * grâce à son ID unique. Utile pour afficher un profil utilisateur ou préremplir un formulaire de modification.
     * 
     * PARAMÈTRES : $id (l'identifiant unique de l'utilisateur que l'on veut récupérer)
     * 
     * RETOUR : Une réponse JSON avec les infos de l'utilisateur OU une erreur si non trouvé
     */
    public function show($id)
    {
        // On essaie de trouver l'utilisateur avec l'ID donné
        $user = User::find($id);

        // Si l'utilisateur n'existe pas, on renvoie une erreur 404 (Not Found)
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        // VÉRIFICATION DES DROITS : on autorise ou non l'accès avec la Policy
        $this->authorize('view', $user);

        // Si on a trouvé l'utilisateur, on renvoie ses informations
        return response()->json([
            'success' => true,
            'message' => 'Utilisateur récupéré avec succès',
            'data' => $user
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE STORE : CRÉER UN NOUVEL UTILISATEUR
     * =========================================================================
     * 
     * UTILITÉ :
     * Cette méthode permet d'ajouter un NOUVEL utilisateur dans la base de données.
     * Elle valide d'abord les données envoyées depuis le frontend, puis crée l'utilisateur.
     * 
     * PARAMÈTRES : $request (objet Request qui contient toutes les données du formulaire envoyé par le frontend)
     * 
     * RETOUR : Une réponse JSON avec l'utilisateur créé OU une erreur de validation
     */
    public function store(Request $request)
    {
        // VÉRIFICATION DES DROITS : a-t-on le droit de créer un utilisateur ?
        $this->authorize('create', User::class);

        // =====================================================================
        // 1. VALIDATION DES DONNÉES (TRÈS IMPORTANT !)
        // =====================================================================
        // On vérifie que les données envoyées sont correctes avant de les enregistrer
        // C'est une sécurité essentielle pour éviter les données invalides ou malveillantes
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255', // Prénom obligatoire, texte, max 255 caractères
            'last_name' => 'required|string|max:255',  // Nom obligatoire, texte, max 255 caractères
            'email' => 'required|email|unique:users,email', // Email obligatoire, format valide, unique dans la table users
            'phone' => 'nullable|string|max:20', // Téléphone facultatif, texte, max 20 caractères
            'password' => 'required|string|min:6', // Mot de passe obligatoire, min 6 caractères
            'role' => ['required', new Enum(['admin', 'responsable_rh', 'responsable_d', 'user'])], // Rôle doit être l'un des 4 valeurs autorisées
            'status' => ['required', new Enum(['active', 'inactive', 'suspended'])] // Statut doit être l'un des 3 valeurs autorisées
        ]);

        // Si la validation échoue, on renvoie les erreurs au frontend
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation des données',
                'errors' => $validator->errors() // On renvoie la liste détaillée des erreurs
            ], 422); // 422 = Code HTTP "Unprocessable Entity" (données invalides)
        }

        // =====================================================================
        // 2. CRÉATION DE L'UTILISATEUR DANS LA BASE DE DONNÉES
        // =====================================================================
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            // IMPORTANT : On hache (crypte) le mot de passe avant de l'enregistrer !
            // Jamais de mot de passe en clair dans la base de données !
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status,
            'email_verified_at' => now() // On marque l'email comme vérifié d'office (car c'est un admin/RH qui crée le compte)
        ]);

        // =====================================================================
        // 3. RÉPONSE AU FRONTEND
        // =====================================================================
        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès !',
            'data' => $user // On renvoie l'utilisateur qui vient d'être créé
        ], 201); // 201 = Code HTTP "Created", tout a été créé avec succès
    }

    /**
     * =========================================================================
     * MÉTHODE UPDATE : MODIFIER UN UTILISATEUR EXISTANT
     * =========================================================================
     * 
     * UTILITÉ :
     * Cette méthode permet de mettre à jour les informations d'un utilisateur déjà existant.
     * 
     * PARAMÈTRES : 
     * - $request : les nouvelles données envoyées par le formulaire frontend
     * - $id : l'identifiant de l'utilisateur à modifier
     * 
     * RETOUR : Une réponse JSON avec l'utilisateur mis à jour OU une erreur
     */
    public function update(Request $request, $id)
    {
        // =====================================================================
        // 1. TROUVER L'UTILISATEUR À MODIFIER
        // =====================================================================
        $user = User::find($id);

        // Si l'utilisateur n'existe pas, erreur 404
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        // VÉRIFICATION DES DROITS : a-t-on le droit de modifier cet utilisateur ?
        $this->authorize('update', $user);

        // =====================================================================
        // 2. VALIDATION DES DONNÉES
        // =====================================================================
        // Note : pour l'email, on ajoute "ignore:$id" pour autoriser l'utilisateur à garder son email actuel
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            // Mot de passe est "sometimes", on ne le change que si on envoie un nouveau
            'password' => 'sometimes|string|min:6',
            'role' => ['sometimes', new Enum(['admin', 'responsable_rh', 'responsable_d', 'user'])],
            'status' => ['sometimes', new Enum(['active', 'inactive', 'suspended'])]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // =====================================================================
        // 3. PRÉPARER LES DONNÉES À METTRE À JOUR
        // =====================================================================
        $updateData = $request->all();

        // Si on a envoyé un nouveau mot de passe, on le hache !
        if (isset($updateData['password'])) {
            $updateData['password'] = Hash::make($updateData['password']);
        }

        // =====================================================================
        // 4. METTRE À JOUR L'UTILISATEUR
        // =====================================================================
        $user->update($updateData);

        // =====================================================================
        // 5. RÉPONSE AU FRONTEND
        // =====================================================================
        return response()->json([
            'success' => true,
            'message' => 'Utilisateur mis à jour avec succès !',
            'data' => $user
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE DESTROY : SUPPRIMER UN UTILISATEUR
     * =========================================================================
     * 
     * UTILITÉ :
     * Supprime définitivement un utilisateur de la base de données.
     * Attention : cette action est IRRÉVERSIBLE !
     * 
     * PARAMÈTRES : $id (l'identifiant de l'utilisateur à supprimer)
     * 
     * RETOUR : Une réponse JSON confirmant la suppression
     */
    public function destroy($id)
    {
        // On trouve l'utilisateur
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        // VÉRIFICATION DES DROITS : seul l'admin peut supprimer !
        $this->authorize('delete', $user);

        // On le supprime !
        $user->delete();

        // Réponse de confirmation
        return response()->json([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès !'
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE TOGGLESTATUS : ACTIVER / DÉSACTIVER UN UTILISATEUR
     * =========================================================================
     * 
     * UTILITÉ :
     * Permet de changer rapidement le statut d'un utilisateur (actif ↔ inactif)
     * sans avoir à remplir tout le formulaire de modification.
     * 
     * PARAMÈTRES : $id (l'identifiant de l'utilisateur)
     * 
     * RETOUR : Une réponse JSON avec le nouveau statut
     */
    public function toggleStatus($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        // VÉRIFICATION DES DROITS : Admin/RH peuvent changer le statut
        $this->authorize('toggleStatus', $user);

        // On bascule le statut : si c'est active, on met inactive, et vice versa !
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Statut de l\'utilisateur mis à jour avec succès',
            'data' => $user
        ], 200);
    }
}

