<?php

namespace App\Http\Controllers;

use App\Models\Reference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReferenceController extends Controller
{
    /**
     * =========================================================================
     * MÉTHODE INDEX : AFFICHER LA LISTE DE TOUTES LES RÉFÉRENCES
     * =========================================================================
     */
    public function index()
    {
        $references = Reference::with(['category', 'publisher', 'uploadedBy', 'authors'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Liste des références récupérée avec succès',
            'data' => $references
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE SHOW : AFFICHER UNE SEULE RÉFÉRENCE
     * =========================================================================
     */
    public function show($id)
    {
        $reference = Reference::with(['category', 'publisher', 'uploadedBy', 'authors'])->find($id);

        if (!$reference) {
            return response()->json([
                'success' => false,
                'message' => 'Référence non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Référence récupérée avec succès',
            'data' => $reference
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE STORE : CRÉER UNE NOUVELLE RÉFÉRENCE
     * =========================================================================
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'abstract' => 'nullable|string',
            'isbn' => 'nullable|string|max:50',
            'publication_year' => 'nullable|integer',
            'language' => 'required|in:fr,en,autre',
            'document_type' => 'required|in:livre,memoire,these,article,revue,rapport,guide,autre',
            'category_id' => 'nullable|exists:categories,id',
            'publisher_id' => 'nullable|exists:publishers,id',
            'uploaded_by' => 'nullable|exists:users,id',
            'cover_image' => 'nullable|string|max:255',
            'file_path' => 'nullable|string|max:255',
            'pages' => 'nullable|integer',
            'status' => 'required|in:draft,published,archived'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation des données',
                'errors' => $validator->errors()
            ], 422);
        }

        $reference = Reference::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Référence créée avec succès !',
            'data' => $reference
        ], 201);
    }

    /**
     * =========================================================================
     * MÉTHODE UPDATE : MODIFIER UNE RÉFÉRENCE EXISTANTE
     * =========================================================================
     */
    public function update(Request $request, $id)
    {
        $reference = Reference::find($id);

        if (!$reference) {
            return response()->json([
                'success' => false,
                'message' => 'Référence non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'abstract' => 'nullable|string',
            'isbn' => 'nullable|string|max:50',
            'publication_year' => 'nullable|integer',
            'language' => 'sometimes|in:fr,en,autre',
            'document_type' => 'sometimes|in:livre,memoire,these,article,revue,rapport,guide,autre',
            'category_id' => 'nullable|exists:categories,id',
            'publisher_id' => 'nullable|exists:publishers,id',
            'uploaded_by' => 'nullable|exists:users,id',
            'cover_image' => 'nullable|string|max:255',
            'file_path' => 'nullable|string|max:255',
            'pages' => 'nullable|integer',
            'status' => 'sometimes|in:draft,published,archived'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $reference->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Référence mise à jour avec succès !',
            'data' => $reference
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE DESTROY : SUPPRIMER UNE RÉFÉRENCE
     * =========================================================================
     */
    public function destroy($id)
    {
        $reference = Reference::find($id);

        if (!$reference) {
            return response()->json([
                'success' => false,
                'message' => 'Référence non trouvée'
            ], 404);
        }

        $reference->delete();

        return response()->json([
            'success' => true,
            'message' => 'Référence supprimée avec succès !'
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE TOGGLESTATUS : CHANGER LE STATUT D'UNE RÉFÉRENCE
     * =========================================================================
     */
    public function toggleStatus($id)
    {
        $reference = Reference::find($id);

        if (!$reference) {
            return response()->json([
                'success' => false,
                'message' => 'Référence non trouvée'
            ], 404);
        }

        // Basculer le statut : draft ↔ published
        $reference->status = $reference->status === 'published' ? 'draft' : 'published';
        $reference->save();

        return response()->json([
            'success' => true,
            'message' => 'Statut de la référence mis à jour avec succès',
            'data' => $reference
        ], 200);
    }
}
