<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DepositeRequest;
use App\Models\User;
use Illuminate\Support\Str;

class DepositeRequestSeeder extends Seeder
{
    /**
     * Exécute les seeders pour la table des demandes de dépôt
     * 
     * UTILITÉ : Crée des demandes de dépôt de références documentaires
     * Cela permet de tester le circuit de validation (demande → vérification → décision → publication).
     */
    public function run(): void
    {
        // Récupère les utilisateurs
        $users = User::all();
        $responsablesDemande = User::where('role', 'responsable_demande')->get();
        $admins = User::where('role', 'admin')->get();

        // Si pas assez d'utilisateurs, on en crée
        if ($users->isEmpty()) {
            UserSeeder::run();
            $users = User::all();
            $responsablesDemande = User::where('role', 'responsable_demande')->get();
            $admins = User::where('role', 'admin')->get();
        }

        // Tableau des demandes de dépôt à créer
        $demandes = [
            [
                'title' => 'Le Rouge et le Noir',
                'description' => 'Roman de Stendhal, chef-d\'œuvre du réalisme français.',
                'proposed_file' => '/documents/le-rouge-et-le-noir.pdf',
                'status' => 'pending', // En attente de vérification
            ],
            [
                'title' => 'La Philosophie de Sartre',
                'description' => 'Essai sur la pensée existentialiste de Jean-Paul Sartre.',
                'proposed_file' => '/documents/sartre-philosophie.pdf',
                'status' => 'approved_by_manager', // Approuvée par le responsable, en attente d'administration
                'assigned_manager_id' => $responsablesDemande->isNotEmpty() ? $responsablesDemande->first()->id : null,
            ],
            [
                'title' => 'Histoire de France',
                'description' => 'Ouvrage général sur l\'histoire de France depuis les origines.',
                'proposed_file' => '/documents/histoire-france.pdf',
                'status' => 'rejected_by_manager', // Rejetée par le responsable
                'assigned_manager_id' => $responsablesDemande->isNotEmpty() ? $responsablesDemande->first()->id : null,
            ],
            [
                'title' => 'Les Fleurs du Mal',
                'description' => 'Recueil de poèmes de Charles Baudelaire.',
                'proposed_file' => '/documents/fleurs-du-mal.pdf',
                'status' => 'published', // Publiée !
                'assigned_manager_id' => $responsablesDemande->isNotEmpty() ? $responsablesDemande->first()->id : null,
            ],
        ];

        // Crée chaque demande de dépôt
        foreach ($demandes as $demandeData) {
            $demandeData['applicant_id'] = $users->where('role', 'user')->random()->id;
            $demande = DepositeRequest::create($demandeData);

            // Si la demande a été vérifiée, ajoute un avis
            if (in_array($demande->status, ['approved_by_manager', 'rejected_by_manager', 'second_review', 'approved', 'rejected', 'published'])) {
                // Ajoute un avis du responsable
                if ($responsablesDemande->isNotEmpty()) {
                    $demande->reviews()->create([
                        'reviewer_id' => $responsablesDemande->first()->id,
                        'reviewer_role' => 'responsable_demande',
                        'decision' => $demande->status === 'rejected_by_manager' ? 'rejected' : 'approved',
                        'justification' => $demande->status === 'rejected_by_manager' 
                            ? 'Le document ne semble pas être dans le domaine public ou les droits ne sont pas clairs.' 
                            : 'Document conforme et intéressant pour la bibliothèque.',
                    ]);
                }

                // Si la demande est publiée ou approuvée, ajoute un avis d'admin
                if (in_array($demande->status, ['approved', 'published']) && $admins->isNotEmpty()) {
                    $demande->reviews()->create([
                        'reviewer_id' => $admins->first()->id,
                        'reviewer_role' => 'admin',
                        'decision' => 'approved',
                        'justification' => 'Validation administrative accordée, document prêt à être publié.',
                    ]);
                }
            }
        }

        // Ajoute 3 demandes de dépôt aléatoires supplémentaires
        for ($i = 0; $i < 3; $i++) {
            $demandeAleatoire = DepositeRequest::create([
                'applicant_id' => $users->where('role', 'user')->random()->id,
                'assigned_manager_id' => $responsablesDemande->isNotEmpty() ? $responsablesDemande->random()->id : null,
                'title' => 'Document ' . ($i + 1) . ' - ' . Str::random(10),
                'description' => 'Description aléatoire pour le document ' . ($i + 1),
                'proposed_file' => '/documents/document-' . ($i + 1) . '.pdf',
                'status' => ['pending', 'approved_by_manager', 'rejected_by_manager', 'published'][rand(0, 3)],
            ]);

            // Ajoute un avis si nécessaire
            if (in_array($demandeAleatoire->status, ['approved_by_manager', 'rejected_by_manager', 'published']) && $responsablesDemande->isNotEmpty()) {
                $demandeAleatoire->reviews()->create([
                    'reviewer_id' => $responsablesDemande->random()->id,
                    'reviewer_role' => 'responsable_demande',
                    'decision' => $demandeAleatoire->status === 'rejected_by_manager' ? 'rejected' : 'approved',
                    'justification' => fake()->paragraph(),
                ]);
            }
        }
    }
}
