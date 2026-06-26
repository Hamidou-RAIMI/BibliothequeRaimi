<?php

namespace App\Http\Controllers;

// On importe les classes nécessaires au fonctionnement du contrôleur
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * =========================================================================
     * MÉTHODE INDEX : AFFICHER LA LISTE DE TOUS LES UTILISATEURS (NON ARCHIVÉS)
     * =========================================================================
     * 
     * UTILITÉ :
     * Cette méthode permet de récupérer et de renvoyer la liste complète des utilisateurs
     * NON ARCHIVÉS.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $users = User::paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste des utilisateurs récupérée avec succès',
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'has_more_pages' => $users->hasMorePages(),
            ]
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE ARCHIVED : AFFICHER LA LISTE DES UTILISATEURS ARCHIVÉS
     * =========================================================================
     */
    public function archived(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $users = User::onlyTrashed()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste des utilisateurs archivés récupérée avec succès',
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'has_more_pages' => $users->hasMorePages(),
            ]
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE SHOW : AFFICHER UN SEUL UTILISATEUR (SPÉCIFIQUE)
     * =========================================================================
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

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
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'role' => ['required', 'in:admin,responsable_rh,responsable_demande,user'],
            'status' => ['required', 'in:active,inactive,suspended']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation des données',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status,
            'email_verified_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès !',
            'data' => $user
        ], 201);
    }

    /**
     * =========================================================================
     * MÉTHODE UPDATE : MODIFIER UN UTILISATEUR EXISTANT
     * =========================================================================
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'password' => 'sometimes|string|min:6',
            'role' => ['sometimes', 'in:admin,responsable_rh,responsable_demande,user'],
            'status' => ['sometimes', 'in:active,inactive,suspended']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->all();
        if (isset($updateData['password'])) {
            $updateData['password'] = Hash::make($updateData['password']);
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur mis à jour avec succès !',
            'data' => $user
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE DESTROY : ARCHIVER UN UTILISATEUR (SOFT DELETE)
     * =========================================================================
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur archivé avec succès !'
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE RESTORE : DÉSARCHIVER UN UTILISATEUR
     * =========================================================================
     */
    public function restore($id)
    {
        $user = User::onlyTrashed()->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur archivé non trouvé'
            ], 404);
        }

        $user->restore();

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur désarchivé avec succès !',
            'data' => $user
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE GETMANAGERS : RÉCUPÉRER TOUS LES RESPONSABLES DE DEMANDE
     * =========================================================================
     */
    public function getManagers()
    {
        $managers = User::where('role', 'responsable_demande')->get();

        return response()->json([
            'success' => true,
            'message' => 'Liste des responsables de demande récupérée avec succès',
            'data' => $managers
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE TOGGLESTATUS : ACTIVER / DÉSACTIVER UN UTILISATEUR
     * =========================================================================
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

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Statut de l\'utilisateur mis à jour avec succès',
            'data' => $user
        ], 200);
    }
}
