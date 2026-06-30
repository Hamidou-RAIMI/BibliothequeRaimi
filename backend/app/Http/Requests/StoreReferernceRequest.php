<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReferernceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
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
        ];
    }
}
