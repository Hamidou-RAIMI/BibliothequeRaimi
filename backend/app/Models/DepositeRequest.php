<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepositeRequest extends Model
{
    use HasFactory;

    /**
     * Les attributs qui peuvent être assignés en masse.
     *
     * @var list<string>
     */
    protected $fillable = [
        'applicant_id',
        'assigned_manager_id',
        'title',
        'description',
        'proposed_file',
        'status',
    ];

    /**
     * Relation avec l'utilisateur qui a soumis la demande
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    /**
     * Relation avec le responsable assigné à la demande
     */
    public function assignedManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_manager_id');
    }

    /**
     * Relation avec les avis/commentaires sur la demande
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(DepositeRequestReview::class, 'deposite_requests_id');
    }
}
