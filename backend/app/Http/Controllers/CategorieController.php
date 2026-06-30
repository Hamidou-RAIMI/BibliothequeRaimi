<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategorieRequest;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

// ==============================================
// CONTRÔLEUR POUR LA GESTION DES CATÉGORIES
// ==============================================
// Ce contrôleur gère toutes les opérations CRUD (Créer, Lire, Mettre à jour, Supprimer)
// liées aux catégories via l'API
class CategorieController extends Controller
{
    /**
     * Récupère et renvoie la liste de toutes les catégories
     * Les catégories sont triées par nom
     */
    public function index()
    {
        try {
            $categories = Categorie::orderBy('name')->get();
            return response()->json([
                'success' => true,
                'message' => 'Catégories récupérées avec succès',
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des catégories',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Récupère et renvoie une catégorie spécifique par son ID
     * @param int $id - Identifiant unique de la catégorie
     */
    public function show($id)
    {
        $categorie = Categorie::find($id);
        if (!$categorie) {
            return response()->json([
                'success' => false,
                'message' => 'Catégorie non trouvée'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Catégorie récupérée avec succès',
            'data' => $categorie
        ]);
    }

    /**
     * Crée une nouvelle catégorie dans la base de données
     * @param Request $request - Requête contenant les données de la catégorie
     */
    public function store(StoreCategorieRequest $request)
    {
        // Récupère uniquement les données validées par la Form Request
         $data = $request->validated();

        // Génère automatiquement le slug à partir du nom validé
         $data['slug'] = Str::slug($data['name']);

    

        // Crée la catégorie avec les données validées
        $categorie = Categorie::create($data);

        // Renvoie une réponse de succès avec la catégorie créée
        return response()->json([
            'success' => true,
            'message' => 'Catégorie créée avec succès',
            'data' => $categorie
        ], 201);
    }

    /**
     * Met à jour les informations d'une catégorie existante
     * @param Request $request - Requête contenant les nouvelles données
     * @param int $id - Identifiant de la catégorie à modifier
     */
    public function update(Request $request, $id)
    {
        $categorie = Categorie::find($id);
        if (!$categorie) {
            return response()->json([
                'success' => false,
                'message' => 'Catégorie non trouvée'
            ], 404);
        }

       

        

        // Prépare les données, met à jour le slug si le nom change
        //recuperer uniquement els donnée validerr
        $data = $request->validated();

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Met à jour la catégorie
        $categorie->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Catégorie mise à jour avec succès',
            'data' => $categorie
        ]);
    }

    /**
     * Supprime une catégorie de la base de données
     * @param int $id - Identifiant de la catégorie à supprimer
     */
    public function destroy($id)
    {
        $categorie = Categorie::find($id);
        if (!$categorie) {
            return response()->json([
                'success' => false,
                'message' => 'Catégorie non trouvée'
            ], 404);
        }

        $categorie->delete();

        return response()->json([
            'success' => true,
            'message' => 'Catégorie supprimée avec succès'
        ]);
    }
}
