<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCategorieRequest;
use App\Http\Requests\UpdateDepositeRequest;
use App\Models\DepositeRequest;
use App\Models\DepositeRequestReview;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DepositeRequestController extends Controller
{
    /**
     * =========================================================================
     * MÉTHODE INDEX : AFFICHER LA LISTE DE TOUTES LES DEMANDES DE DÉPÔT (ADMIN)
     * =========================================================================
     */
    public function index()
    {
        $demandes = DepositeRequest::with(['applicant', 'assignedManager', 'reviews.reviewer'])->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Liste des demandes de dépôt récupérée avec succès',
            'data' => $demandes
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE MYREQUESTS : AFFICHER LA LISTE DES DEMANDES DE L'UTILISATEUR CONNECTÉ
     * =========================================================================
     */
    public function myRequests(Request $request)
    {
        $demandes = DepositeRequest::where('applicant_id', $request->user()->id)
            ->with(['assignedManager', 'reviews.reviewer', 'category', 'publisher'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Liste de vos demandes récupérée avec succès',
            'data' => $demandes
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE SHOW : AFFICHER UNE DEMANDE DE DÉPÔT SPECIFIQUE
     * =========================================================================
     */
    public function show($id)
    {
        $demande = DepositeRequest::with(['applicant', 'assignedManager', 'reviews.reviewer', 'category', 'publisher'])->find($id);

        if (!$demande) {
            return response()->json([
                'success' => false,
                'message' => 'Demande de dépôt non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Demande de dépôt récupérée avec succès',
            'data' => $demande
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE STORE : CRÉER UNE NOUVELLE DEMANDE DE DÉPÔT
     * =========================================================================
     */
    public function store(Request $request)
    {
        // Valider les données de la requête
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'subtitle' => 'nullable|string|max:255',
            'abstract' => 'nullable|string',
            'isbn' => 'nullable|string|max:50',
            'publication_year' => 'nullable|integer',
            'language' => 'required|string',
            'document_type' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'publisher_id' => 'nullable|exists:publishers,id',
            'pages' => 'nullable|integer',
            'pdf_file' => 'required|file|mimes:pdf|max:20480', // PDF obligatoire, max 20 MB
            'cover_image' => 'nullable|file|mimes:jpeg,jpg,png|max:2048' // Photo de couverture, max 2 MB
        ]);

        // Si la validation échoue, renvoyer les erreurs
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Initialiser les données à sauvegarder
        $data = $validator->validated();
        $data['applicant_id'] = $request->user()->id;
        
        // Gestion de l'upload du fichier PDF
        if ($request->hasFile('pdf_file')) {
            // Enregistrer le fichier dans le disque "public" dans le dossier "pdfs"
            $pdfPath = $request->file('pdf_file')->store('pdfs', 'public');
            // Stocker le chemin dans la colonne "proposed_file"
            $data['proposed_file'] = $pdfPath;
        }

        // Gestion de l'upload de la photo de couverture
        if ($request->hasFile('cover_image')) {
            // Enregistrer la photo dans le disque "public" dans le dossier "covers"
            $coverPath = $request->file('cover_image')->store('covers', 'public');
            // Stocker le chemin dans la colonne "cover_image"
            $data['cover_image'] = $coverPath;
        }

        // Créer la demande de dépôt
        $demande = DepositeRequest::create($data);
        // Charger les relations pour la réponse
        $demande->load(['applicant', 'assignedManager']);

        return response()->json([
            'success' => true,
            'message' => 'Demande de dépôt créée avec succès !',
            'data' => $demande
        ], 201);
    }

    /**
     * =========================================================================
     * MÉTHODE DESTROY : SUPPRIMER UNE DEMANDE DE DÉPÔT
     * =========================================================================
     */
    public function destroy(Request $request, $id)
    {
        // Récupérer la demande de dépôt
        $demande = DepositeRequest::find($id);

        // Vérifier si la demande existe
        if (!$demande) {
            return response()->json([
                'success' => false,
                'message' => 'Demande de dépôt non trouvée'
            ], 404);
        }

        // Vérifier si l'utilisateur est le propriétaire de la demande
        if ($demande->applicant_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé à supprimer cette demande'
            ], 403);
        }

        // Supprimer les fichiers associés si ils existent
        if ($demande->proposed_file && Storage::disk('public')->exists($demande->proposed_file)) {
            Storage::disk('public')->delete($demande->proposed_file);
        }
        if ($demande->cover_image && Storage::disk('public')->exists($demande->cover_image)) {
            Storage::disk('public')->delete($demande->cover_image);
        }

        // Supprimer la demande
        $demande->delete();

        return response()->json([
            'success' => true,
            'message' => 'Demande de dépôt supprimée avec succès !'
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE UPDATE : MODIFIER UNE DEMANDE DE DÉPÔT EXISTANTE
     * =========================================================================
     */
    public function update(UpdateDepositeRequest $request, $id)
    {
        $demande = DepositeRequest::find($id);

        if (!$demande) {
            return response()->json([
                'success' => false,
                'message' => 'Demande de dépôt non trouvée'
            ], 404);
        }

       

        

        $demande->update($request->validated());
        $demande->load(['applicant', 'assignedManager', 'reviews.reviewer']);

        return response()->json([
            'success' => true,
            'message' => 'Demande de dépôt mise à jour avec succès !',
            'data' => $demande
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE ASSIGN : AFFECTER UNE DEMANDE À UN RESPONSABLE
     * =========================================================================
     */
    public function assign(Request $request, $id)
    {
        $demande = DepositeRequest::find($id);
        if (!$demande) {
            return response()->json([
                'success' => false,
                'message' => 'Demande de dépôt non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'manager_id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $demande->assigned_manager_id = $request->manager_id;
        $demande->status = 'assigned';
        $demande->save();
        $demande->load(['applicant', 'assignedManager', 'reviews.reviewer']);

        return response()->json([
            'success' => true,
            'message' => 'Demande affectée avec succès !',
            'data' => $demande
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE REASSIGN : RÉAFFECTER UNE DEMANDE À UN AUTRE RESPONSABLE
     * =========================================================================
     */
    public function reassign(Request $request, $id)
    {
        $demande = DepositeRequest::find($id);
        if (!$demande) {
            return response()->json([
                'success' => false,
                'message' => 'Demande de dépôt non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'manager_id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $demande->assigned_manager_id = $request->manager_id;
        $demande->status = 'reassigned';
        $demande->save();
        $demande->load(['applicant', 'assignedManager', 'reviews.reviewer']);

        return response()->json([
            'success' => true,
            'message' => 'Demande réaffectée avec succès !',
            'data' => $demande
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE SUBMITREVIEW : LE RESPONSABLE SOUMET SON AVIS
     * =========================================================================
     */
    public function submitReview(Request $request, $id)
    {
        $demande = DepositeRequest::find($id);
        if (!$demande) {
            return response()->json([
                'success' => false,
                'message' => 'Demande de dépôt non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'decision' => 'required|in:approved,rejected',
            'justification' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $review = DepositeRequestReview::create([
            'deposite_requests_id' => $id,
            'reviewer_id' => $request->user()->id,
            'reviewer_role' => $request->user()->role,
            'decision' => $request->decision,
            'justification' => $request->justification,
        ]);

        $demande->status = $request->decision === 'approved' ? 'approved_by_manager' : 'rejected_by_manager';
        $demande->save();
        $demande->load(['applicant', 'assignedManager', 'reviews.reviewer']);

        return response()->json([
            'success' => true,
            'message' => 'Avis soumis avec succès !',
            'data' => $demande
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE PUBLISH : L'ADMIN PUBLIE LA DEMANDE ET CRÉE UNE RÉFÉRENCE
     * =========================================================================
     */
    public function publish($id)
    {
        $demande = DepositeRequest::find($id);
        if (!$demande) {
            return response()->json([
                'success' => false,
                'message' => 'Demande de dépôt non trouvée'
            ], 404);
        }

        $demande->status = 'published';
        $demande->save();

        // Créer la référence
        $reference = \App\Models\Reference::create([
            'title' => $demande->title,
            'subtitle' => $demande->subtitle,
            'abstract' => $demande->abstract,
            'isbn' => $demande->isbn,
            'publication_year' => $demande->publication_year,
            'language' => $demande->language,
            'document_type' => $demande->document_type,
            'category_id' => $demande->category_id,
            'publisher_id' => $demande->publisher_id,
            'pages' => $demande->pages,
            'uploaded_by' => $demande->applicant_id,
            'cover_image' => $demande->cover_image,
            'file_path' => $demande->proposed_file,
            'status' => 'published',
            'is_new' => true
        ]);

        $demande->load(['applicant', 'assignedManager', 'reviews.reviewer']);

        return response()->json([
            'success' => true,
            'message' => 'Demande publiée avec succès !',
            'data' => $demande
        ], 200);
    }

    /**
     * =========================================================================
     * MÉTHODE REJECT : L'ADMIN REJETTE LA DEMANDE AVEC UN MOTIF
     * =========================================================================
     */
    public function reject(Request $request, $id)
    {
        $demande = DepositeRequest::find($id);
        if (!$demande) {
            return response()->json([
                'success' => false,
                'message' => 'Demande de dépôt non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'justification' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $review = DepositeRequestReview::create([
            'deposite_requests_id' => $id,
            'reviewer_id' => $request->user()->id,
            'reviewer_role' => $request->user()->role,
            'decision' => 'rejected',
            'justification' => $request->justification,
        ]);

        $demande->status = 'rejected';
        $demande->save();
        $demande->load(['applicant', 'assignedManager', 'reviews.reviewer']);

        return response()->json([
            'success' => true,
            'message' => 'Demande rejetée avec succès !',
            'data' => $demande
        ], 200);
    }
}
