<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositeRequestReview extends Model
{
    use HasFactory;

    /**
     * Les attributs qui peuvent être assignés en masse.
     *
     * @var list<string>
     */
    protected $fillable = [
        'deposite_requests_id',
        'reviewer_id',
        'reviewer_role',
        'decision',
        'justification',
    ];

    /**
     * Relation avec la demande de dépôt
     */
    public function depositeRequest(): BelongsTo
    {
        return $this->belongsTo(DepositeRequest::class, 'deposite_requests_id');
    }

    /**
     * Relation avec l'utilisateur qui a donné l'avis
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
