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
            return url('storage/' . $this->cover_image);
        }

        // Image de placeholder par défaut
        $placeholderUrl = 'https://blog.bod.fr/mettre-en-forme/faire-une-couverture-de-livre/';

        // On peut aussi définir des images différentes par catégorie si on veut
        // if ($this->category_id) {
        //     switch ($this->category_id) {
        //         case 1: $placeholderUrl = '...'; break;
        //         case 2: $placeholderUrl = '...'; break;
        //     }
        // }

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
