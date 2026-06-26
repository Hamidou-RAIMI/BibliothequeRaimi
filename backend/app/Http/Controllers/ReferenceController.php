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
     * MÉTHODE PUBLICINDEX : AFFICHER LA LISTE DES RÉFÉRENCES PUBLIQUES (PUBLIÉES)
     * =========================================================================
     */
    public function publicIndex()
    {
        $references = Reference::with(['category', 'publisher', 'authors'])->where('status', 'published')->get();

        return response()->json([
            'success' => true,
            'message' => 'Liste des références publiques récupérée avec succès',
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
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'file_path' => 'nullable|string|max:255',
            'pages' => 'nullable|integer',
            'status' => 'required|in:draft,published,archived',
            'authors' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation des données',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except('cover_image', 'authors');

        // Gestion du téléchargement de l'image de couverture
        if ($request->hasFile('cover_image')) {
            $imagePath = $request->file('cover_image')->store('covers', 'public');
            $data['cover_image'] = $imagePath;
        }

        $reference = Reference::create($data);

        // Gestion des auteurs associés
        if ($request->has('authors')) {
            $authors = json_decode($request->authors, true);
            if (is_array($authors) && !empty($authors)) {
                $authorIds = array_column($authors, 'id');
                $reference->authors()->sync($authorIds);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Référence créée avec succès !',
            'data' => $reference->load(['category', 'publisher', 'uploadedBy', 'authors'])
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
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'file_path' => 'nullable|string|max:255',
            'pages' => 'nullable|integer',
            'status' => 'sometimes|in:draft,published,archived',
            'authors' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except('cover_image', 'authors');

        // Gestion du téléchargement de l'image de couverture pour la mise à jour
        if ($request->hasFile('cover_image')) {
            // Supprimer l'ancienne image si elle existe
            if ($reference->cover_image && \Storage::disk('public')->exists($reference->cover_image)) {
                \Storage::disk('public')->delete($reference->cover_image);
            }
            $imagePath = $request->file('cover_image')->store('covers', 'public');
            $data['cover_image'] = $imagePath;
        }

        $reference->update($data);

        // Gestion des auteurs associés
        if ($request->has('authors')) {
            $authors = json_decode($request->authors, true);
            if (is_array($authors) && !empty($authors)) {
                $authorIds = array_column($authors, 'id');
                $reference->authors()->sync($authorIds);
            } else {
                $reference->authors()->sync([]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Référence mise à jour avec succès !',
            'data' => $reference->load(['category', 'publisher', 'uploadedBy', 'authors'])
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE ARCHIVE : ARCHIVER UNE RÉFÉRENCE
     * =========================================================================
     */
    public function archive($id)
    {
        $reference = Reference::find($id);

        if (!$reference) {
            return response()->json([
                'success' => false,
                'message' => 'Référence non trouvée'
            ], 404);
        }

        $reference->status = 'archived';
        $reference->save();

        return response()->json([
            'success' => true,
            'message' => 'Référence archivée avec succès !'
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE RESTORE : RÉSTAUER UNE RÉFÉRENCE ARCHIVÉE
     * =========================================================================
     */
    public function restore($id)
    {
        $reference = Reference::find($id);

        if (!$reference) {
            return response()->json([
                'success' => false,
                'message' => 'Référence non trouvée'
            ], 404);
        }

        $reference->status = 'draft';
        $reference->save();

        return response()->json([
            'success' => true,
            'message' => 'Référence restaurée avec succès !'
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE ARCHIVED : AFFICHER LA LISTE DES RÉFÉRENCES ARCHIVÉES
     * =========================================================================
     */
    public function archived()
    {
        $references = Reference::with(['category', 'publisher', 'uploadedBy', 'authors'])->where('status', 'archived')->get();

        return response()->json([
            'success' => true,
            'message' => 'Liste des références archivées récupérée avec succès',
            'data' => $references
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
