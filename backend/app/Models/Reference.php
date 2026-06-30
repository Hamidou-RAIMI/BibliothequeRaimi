<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reference extends Model
{
    /** @use HasFactory<\Database\Factories\ReferenceFactory> */
    use HasFactory;

    /**
     * Les attributs qui peuvent être assignés en masse.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'subtitle',
        'abstract',
        'isbn',
        'publication_year',
        'language',
        'document_type',
        'category_id',
        'publisher_id',
        'uploaded_by',
        'cover_image',
        'file_path',
        'pages',
        'download_count',
        'view_count',
        'status',
        'is_new',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = ['cover_image_url'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_new' => 'boolean',
    ];

    /**
     * Get the cover image URL.
     */
    public function getCoverImageUrlAttribute(): string
    {
        if ($this->cover_image) {
            // Vérifie si c'est déjà une URL complète (http ou https)
            if (filter_var($this->cover_image, FILTER_VALIDATE_URL)) {
                return $this->cover_image;
            }
            // Sinon, c'est un chemin local dans storage
            return url('storage/' . $this->cover_image);
        }

        // Image de placeholder par défaut valide
        $placeholderUrl = 'https://picsum.photos/400/600';

        return $placeholderUrl;
    }

    /**
     * Relation avec la catégorie (une référence appartient à une catégorie)
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Categorie::class, 'category_id');
    }

    /**
     * Relation avec l'éditeur (une référence appartient à un éditeur)
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    /**
     * Relation avec l'utilisateur qui a uploadé la référence
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Relation many-to-many avec les auteurs (une référence peut avoir plusieurs auteurs)
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'reference_author');
    }

    /**
     * Relation avec les mots-clés (une référence peut avoir plusieurs mots-clés)
     */
    public function keywords(): HasMany
    {
        // Note : Il faudrait créer un modèle ReferenceKeyword, 
        // mais pour l'instant on peut utiliser la table directement
        return $this->hasMany(\App\Models\ReferenceKeyword::class);
    }
}
