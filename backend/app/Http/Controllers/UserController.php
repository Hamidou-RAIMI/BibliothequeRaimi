<?php

namespace App\Http\Controllers;

// On importe les classes nécessaires au fonctionnement du contrôleur

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Routing\Controller;
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
        $search = $request->input('search');
        $role = $request->input('role');
        $status = $request->input('status');

        $query = User::query();

        // Recherche par nom/prénom/email
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Filtre par rôle
        if ($role) {
            $query->where('role', $role);
        }

        // Filtre par statut
        if ($status) {
            $query->where('status', $status);
        }

        // Pagination
        $users = $query->paginate($perPage);

        return response()->json([
            'users' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ]
        ]);
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
    public function store(StoreUserRequest $requestStore)
    {
        $data = $requestStore->validated();

        if (!isset($data['statut'])) {
            $data['statut'] = 'active'; // Valeur par défaut
           
        }
        $user = User::create($data);

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
    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        $updateData = $request->validated();

        
        if (isset($updateData['password'])) {
        $updateData['password'] = Hash::make($updateData['password']);
    }

        // $updateData = $request->all();
        // if (isset($updateData['password'])) {
        //     $updateData['password'] = Hash::make($updateData['password']);
        // }

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
