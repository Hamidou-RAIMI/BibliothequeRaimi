<?php

namespace App\Http\Controllers;

use App\Models\DepositeRequest;
use App\Models\DepositeRequestReview;
use Illuminate\Http\Request;
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
            ->with(['assignedManager', 'reviews.reviewer'])
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
        $demande = DepositeRequest::with(['applicant', 'assignedManager', 'reviews.reviewer'])->find($id);

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
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'proposed_file' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'abstract' => 'nullable|string',
            'isbn' => 'nullable|string|max:50',
            'publication_year' => 'nullable|integer',
            'language' => 'nullable|in:fr,en,autre',
            'document_type' => 'nullable|in:livre,memoire,these,article,revue,rapport,guide,autre',
            'category_id' => 'nullable|exists:categories,id',
            'publisher_id' => 'nullable|exists:publishers,id',
            'pages' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation des données',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['applicant_id'] = $request->user()->id;
        $demande = DepositeRequest::create($data);
        $demande->load(['applicant', 'assignedManager']);

        return response()->json([
            'success' => true,
            'message' => 'Demande de dépôt créée avec succès !',
            'data' => $demande
        ], 201);
    }

    /**
     * =========================================================================
     * MÉTHODE UPDATE : MODIFIER UNE DEMANDE DE DÉPÔT EXISTANTE
     * =========================================================================
     */
    public function update(Request $request, $id)
    {
        $demande = DepositeRequest::find($id);

        if (!$demande) {
            return response()->json([
                'success' => false,
                'message' => 'Demande de dépôt non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'proposed_file' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:pending,assigned,reassigned,approved_by_manager,rejected_by_manager,second_review,approved,rejected,published',
            'assigned_manager_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $demande->update($request->all());
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
