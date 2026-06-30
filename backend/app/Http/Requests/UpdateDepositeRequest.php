<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDepositeRequest extends FormRequest
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
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'proposed_file' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:pending,assigned,reassigned,approved_by_manager,rejected_by_manager,second_review,approved,rejected,published',
            'assigned_manager_id' => 'nullable|exists:users,id',
        ];
    }
}
