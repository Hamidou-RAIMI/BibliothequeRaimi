<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Reference;
use App\Models\DepositeRequest;
use App\Models\Categorie;
use App\Models\Publisher;
use App\Models\Author;
use Illuminate\Support\Facades\Auth;

class StatisticsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->role;

        if ($role === 'admin') {
            return $this->getAdminStatistics();
        } elseif ($role === 'responsable_demande') {
            return $this->getResponsableDemandeStatistics($user);
        } elseif ($role === 'responsable_rh') {
            return $this->getResponsableRhStatistics();
        } else {
            return $this->getUserStatistics();
        }
    }

    private function getAdminStatistics()
    {
        $totalUsers = User::count();
        $totalReferences = Reference::count();
        $totalDepositeRequests = DepositeRequest::count();
        $pendingRequests = DepositeRequest::where('status', 'pending')->count();
        $publishedReferences = Reference::where('status', 'published')->count();
        $newReferences = Reference::where('is_new', true)->count();
        $totalCategories = Categorie::count();
        $totalPublishers = Publisher::count();
        $totalAuthors = Author::count();

        $requestsByStatus = DepositeRequest::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $referencesByCategory = Reference::selectRaw('category_id, count(*) as count')
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->category?->name ?? 'Sans catégorie',
                    'count' => $item->count
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Statistiques récupérées avec succès',
            'data' => [
                'totalUsers' => $totalUsers,
                'totalReferences' => $totalReferences,
                'totalDepositeRequests' => $totalDepositeRequests,
                'pendingRequests' => $pendingRequests,
                'publishedReferences' => $publishedReferences,
                'newReferences' => $newReferences,
                'totalCategories' => $totalCategories,
                'totalPublishers' => $totalPublishers,
                'totalAuthors' => $totalAuthors,
                'requestsByStatus' => $requestsByStatus,
                'referencesByCategory' => $referencesByCategory,
            ]
        ]);
    }

    private function getResponsableDemandeStatistics($user)
    {
        $assignedRequests = DepositeRequest::where('assigned_manager_id', $user->id)->count();
        $pendingReviewRequests = DepositeRequest::where('assigned_manager_id', $user->id)
            ->whereIn('status', ['assigned', 'reassigned'])
            ->count();
        $approvedRequests = DepositeRequest::where('assigned_manager_id', $user->id)
            ->where('status', 'approved_by_manager')
            ->count();
        $rejectedRequests = DepositeRequest::where('assigned_manager_id', $user->id)
            ->where('status', 'rejected_by_manager')
            ->count();

        $requestsByStatus = DepositeRequest::where('assigned_manager_id', $user->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $recentRequests = DepositeRequest::where('assigned_manager_id', $user->id)
            ->with(['applicant'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Statistiques récupérées avec succès',
            'data' => [
                'assignedRequests' => $assignedRequests,
                'pendingReviewRequests' => $pendingReviewRequests,
                'approvedRequests' => $approvedRequests,
                'rejectedRequests' => $rejectedRequests,
                'requestsByStatus' => $requestsByStatus,
                'recentRequests' => $recentRequests,
            ]
        ]);
    }

    private function getResponsableRhStatistics()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $pendingUsers = User::where('status', 'pending')->count();
        $archivedUsers = User::where('status', 'archived')->count();

        $usersByRole = User::selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role');

        $recentUsers = User::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Statistiques récupérées avec succès',
            'data' => [
                'totalUsers' => $totalUsers,
                'activeUsers' => $activeUsers,
                'pendingUsers' => $pendingUsers,
                'archivedUsers' => $archivedUsers,
                'usersByRole' => $usersByRole,
                'recentUsers' => $recentUsers,
            ]
        ]);
    }

    private function getUserStatistics()
    {
        $publishedReferences = Reference::where('status', 'published')->count();
        $totalCategories = Categorie::count();

        return response()->json([
            'success' => true,
            'message' => 'Statistiques récupérées avec succès',
            'data' => [
                'publishedReferences' => $publishedReferences,
                'totalCategories' => $totalCategories,
            ]
        ]);
    }
}
