<?php
// app/Http/Requests/Disputes/ResolveDisputeRequest.php
namespace App\Http\Requests\Disputes;

use Illuminate\Foundation\Http\FormRequest;

class ResolveDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'resolution' => ['required', 'string', 'min:20', 'max:2000'],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
            'penalty_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'penalty_type' => ['nullable', 'string', 'in:warning,suspension,ban'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'resolution.required' => 'Une résolution détaillée est requise',
            'resolution.min' => 'La résolution doit contenir au moins 20 caractères',
        ];
    }
}
