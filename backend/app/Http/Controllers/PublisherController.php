<?php

namespace App\Http\Controllers;

use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

// ==============================================
// CONTRÔLEUR POUR LA GESTION DES ÉDITEURS
// ==============================================
// Ce contrôleur gère toutes les opérations CRUD (Créer, Lire, Mettre à jour, Supprimer)
// liées aux éditeurs via l'API
class PublisherController extends Controller
{
    /**
     * Récupère et renvoie la liste de tous les éditeurs
     * Les éditeurs sont triés par nom
     */
    public function index()
    {
        $publishers = Publisher::orderBy('name')->get();
        return response()->json([
            'success' => true,
            'message' => 'Éditeurs récupérés avec succès',
            'data' => $publishers
        ]);
    }

    /**
     * Récupère et renvoie un éditeur spécifique par son ID
     * @param int $id - Identifiant unique de l'éditeur
     */
    public function show($id)
    {
        $publisher = Publisher::find($id);
        if (!$publisher) {
            return response()->json([
                'success' => false,
                'message' => 'Éditeur non trouvé'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Éditeur récupéré avec succès',
            'data' => $publisher
        ]);
    }

    /**
     * Crée un nouvel éditeur dans la base de données
     * @param Request $request - Requête contenant les données de l'éditeur
     */
    public function store(Request $request)
    {
        // Valide les données envoyées
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'country' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        // Si la validation échoue, renvoie les erreurs
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Crée l'éditeur avec les données validées
        $publisher = Publisher::create($request->all());

        // Renvoie une réponse de succès avec l'éditeur créé
        return response()->json([
            'success' => true,
            'message' => 'Éditeur créé avec succès',
            'data' => $publisher
        ], 201);
    }

    /**
     * Met à jour les informations d'un éditeur existant
     * @param Request $request - Requête contenant les nouvelles données
     * @param int $id - Identifiant de l'éditeur à modifier
     */
    public function update(Request $request, $id)
    {
        $publisher = Publisher::find($id);
        if (!$publisher) {
            return response()->json([
                'success' => false,
                'message' => 'Éditeur non trouvé'
            ], 404);
        }

        // Valide les données envoyées
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'country' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        // Si la validation échoue, renvoie les erreurs
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Met à jour l'éditeur
        $publisher->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Éditeur mis à jour avec succès',
            'data' => $publisher
        ]);
    }

    /**
     * Supprime un éditeur de la base de données
     * @param int $id - Identifiant de l'éditeur à supprimer
     */
    public function destroy($id)
    {
        $publisher = Publisher::find($id);
        if (!$publisher) {
            return response()->json([
                'success' => false,
                'message' => 'Éditeur non trouvé'
            ], 404);
        }

        $publisher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Éditeur supprimé avec succès'
        ]);
    }
}
