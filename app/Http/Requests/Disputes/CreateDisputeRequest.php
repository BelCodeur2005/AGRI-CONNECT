<?php

// app/Http/Requests/Disputes/CreateDisputeRequest.php
namespace App\Http\Requests\Disputes;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\DisputeType;
use Illuminate\Validation\Rules\Enum;

class CreateDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');
        
        return auth()->check()
            && (
                $order->buyer->user_id === auth()->id() ||
                $order->items->pluck('producer.user_id')->contains(auth()->id()) ||
                $order->delivery?->transporter->user_id === auth()->id()
            );
    }

    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(DisputeType::class)],
            'reported_against' => ['required', 'integer', 'exists:users,id', 'different:' . auth()->id()],
            'description' => ['required', 'string', 'min:50', 'max:2000'],
            'evidence_photos' => ['nullable', 'array', 'max:10'],
            'evidence_photos.*' => ['image', 'mimes:jpeg,jpg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Le type de litige est requis',
            'reported_against.required' => 'Vous devez indiquer contre qui le litige est ouvert',
            'reported_against.different' => 'Vous ne pouvez pas ouvrir un litige contre vous-même',
            'description.required' => 'Une description détaillée est requise',
            'description.min' => 'La description doit contenir au moins 50 caractères',
            'evidence_photos.max' => 'Vous ne pouvez télécharger que 10 photos maximum',
            'evidence_photos.*.max' => 'Chaque photo ne doit pas dépasser 5 Mo',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $order = $this->route('order');
            $reportedAgainst = $this->reported_against;

            // Vérifier que reported_against fait partie de la commande
            $validUsers = collect([
                $order->buyer->user_id,
                ...$order->items->pluck('producer.user_id'),
                $order->delivery?->transporter->user_id
            ])->filter();

            if (!$validUsers->contains($reportedAgainst)) {
                $validator->errors()->add('reported_against', 'Cette personne n\'est pas impliquée dans cette commande');
            }

            // Vérifier qu'il n'y a pas déjà un litige ouvert
            $existingDispute = \App\Models\Dispute::where('order_id', $order->id)
                ->where('reported_by', auth()->id())
                ->where('reported_against', $reportedAgainst)
                ->where('status', '!=', \App\Enums\DisputeStatus::CLOSED)
                ->exists();

            if ($existingDispute) {
                $validator->errors()->add('order_id', 'Un litige est déjà ouvert pour cette commande');
            }
        });
    }
}
