<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

// ==============================================
// CONTRÔLEUR POUR LA GESTION DES AUTEURS
// ==============================================
// Ce contrôleur gère toutes les opérations CRUD (Créer, Lire, Mettre à jour, Supprimer)
// liées aux auteurs via l'API
class AuthorController extends Controller
{
    /**
     * Récupère et renvoie la liste de tous les auteurs
     * Les auteurs sont triés par nom de famille puis par prénom
     */
    public function index()
    {
        $authors = Author::orderBy('last_name')->orderBy('first_name')->get();
        return response()->json([
            'success' => true,
            'message' => 'Auteurs récupérés avec succès',
            'data' => $authors
        ]);
    }

    /**
     * Récupère et renvoie un auteur spécifique par son ID
     * @param int $id - Identifiant unique de l'auteur
     */
    public function show($id)
    {
        $author = Author::find($id);
        if (!$author) {
            return response()->json([
                'success' => false,
                'message' => 'Auteur non trouvé'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Auteur récupéré avec succès',
            'data' => $author
        ]);
    }

    /**
     * Crée un nouvel auteur dans la base de données
     * @param Request $request - Requête contenant les données de l'auteur
     */
    public function store(Request $request)
    {
        // Valide les données envoyées
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'biography' => 'nullable|string',
            'nationality' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'death_date' => 'nullable|date|after_or_equal:birth_date',
        ]);

        // Si la validation échoue, renvoie les erreurs
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Crée l'auteur avec les données validées
        $author = Author::create($request->all());

        // Renvoie une réponse de succès avec l'auteur créé
        return response()->json([
            'success' => true,
            'message' => 'Auteur créé avec succès',
            'data' => $author
        ], 201);
    }

    /**
     * Met à jour les informations d'un auteur existant
     * @param Request $request - Requête contenant les nouvelles données
     * @param int $id - Identifiant de l'auteur à modifier
     */
    public function update(Request $request, $id)
    {
        $author = Author::find($id);
        if (!$author) {
            return response()->json([
                'success' => false,
                'message' => 'Auteur non trouvé'
            ], 404);
        }

        // Valide les données envoyées
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'biography' => 'nullable|string',
            'nationality' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'death_date' => 'nullable|date|after_or_equal:birth_date',
        ]);

        // Si la validation échoue, renvoie les erreurs
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Met à jour l'auteur
        $author->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Auteur mis à jour avec succès',
            'data' => $author
        ]);
    }

    /**
     * Supprime un auteur de la base de données
     * @param int $id - Identifiant de l'auteur à supprimer
     */
    public function destroy($id)
    {
        $author = Author::find($id);
        if (!$author) {
            return response()->json([
                'success' => false,
                'message' => 'Auteur non trouvé'
            ], 404);
        }

        $author->delete();

        return response()->json([
            'success' => true,
            'message' => 'Auteur supprimé avec succès'
        ]);
    }
}
