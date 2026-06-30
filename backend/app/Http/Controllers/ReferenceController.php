<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReferernceRequest;
use App\Http\Requests\UpdateReferenceRequest;
use App\Models\Reference;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;


class ReferenceController extends Controller
{
    /**
     * =========================================================================
     * MÉTHODE INDEX : AFFICHER LA LISTE DE TOUTES LES RÉFÉRENCES
     * =========================================================================
     */
    public function index()
    {
        try {
            $references = Reference::with(['category', 'authors'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Liste des références récupérée avec succès',
                'data' => $references
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des références',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * =========================================================================
     * MÉTHODE PUBLICINDEX : AFFICHER LA LISTE DES RÉFÉRENCES PUBLIQUES (PUBLIÉES)
     * =========================================================================
     */
    public function publicIndex()
    {
        try {
            // Get all references without filtering by status for now, or maybe the status column doesn't exist
            $references = Reference::with(['category', 'authors'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Liste des références publiques récupérée avec succès',
                'data' => $references
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des références',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * =========================================================================
     * MÉTHODE SHOW : AFFICHER UNE SEULE RÉFÉRENCE
     * =========================================================================
     */
    public function show($id)
    {
        try {
            $reference = Reference::with(['category', 'authors'])->find($id);

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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la référence',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * =========================================================================
     * MÉTHODE STORE : CRÉER UNE NOUVELLE RÉFÉRENCE
     * =========================================================================
     */
    public function store(StoreReferernceRequest $request)
    {
         $data = $request->safe()->except(['cover_image', 'authors']);

        

        // Convertir is_new en booléen
        $data['is_new'] = filter_var($request->input('is_new', false), FILTER_VALIDATE_BOOLEAN);
        

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
    public function update(UpdateReferenceRequest $request, $id)
    {
        $reference = Reference::find($id);

        if (!$reference) {
            return response()->json([
                'success' => false,
                'message' => 'Référence non trouvée'
            ], 404);
        }
    $data = $request->safe()->except(['cover_image', 'authors']);

       

        // Convertir is_new en booléen
        if ($request->has('is_new')) {
            $data['is_new'] = filter_var($request->input('is_new'), FILTER_VALIDATE_BOOLEAN);
        }

        // Gestion du téléchargement de l'image de couverture pour la mise à jour
        if ($request->hasFile('cover_image')) {
            // Supprimer l'ancienne image si elle existe
            if ($reference->cover_image && Storage::disk('public')->exists($reference->cover_image)) {
            Storage::disk('public')->delete($reference->cover_image);
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

    /**
     * =========================================================================
     * MÉTHODE DOWNLOAD : TÉLÉCHARGER LE FICHIER PDF ET INCÉMENTER LE COMPTEUR
     * =========================================================================
     */
    public function download($id)
    {
        // Récupérer la référence par son ID
        $reference = Reference::find($id);

        if (!$reference) {
            return response()->json([
                'success' => false,
                'message' => 'Référence non trouvée'
            ], 404);
        }

        // Vérifier si un fichier est disponible
        if (!$reference->file_path || !Storage::disk('public')->exists($reference->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier non disponible'
            ], 404);
        }

        // Incrémenter le compteur de téléchargements
        $reference->increment('download_count');

        // Retourner le fichier pour téléchargement
        return Storage::disk('public')->download($reference->file_path);
    }
}
