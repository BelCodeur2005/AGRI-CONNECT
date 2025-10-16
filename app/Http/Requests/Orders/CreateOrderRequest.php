<?php
// app/Http/Requests/Orders/CreateOrderRequest.php
namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\PaymentMethod;
use Illuminate\Validation\Rules\Enum;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isBuyer();
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.offer_id' => ['required', 'exists:offers,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
            
            'delivery_address' => ['required', 'string', 'max:500'],
            'delivery_location_id' => ['required', 'exists:locations,id'],
            'requested_delivery_date' => [
                'required',
                'date',
                'after:today',
                'before_or_equal:' . now()->addDays(30)->toDateString()
            ],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', new Enum(PaymentMethod::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Vous devez ajouter au moins un article',
            'items.min' => 'Vous devez commander au moins un article',
            'items.max' => 'Vous ne pouvez pas commander plus de 50 articles différents',
            'items.*.offer_id.exists' => 'Une des offres n\'existe pas',
            'items.*.quantity.min' => 'La quantité doit être supérieure à 0',
            'requested_delivery_date.after' => 'La date de livraison doit être après aujourd\'hui',
            'requested_delivery_date.before_or_equal' => 'La date de livraison ne peut pas dépasser 30 jours',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($this->items ?? [] as $index => $item) {
                $offer = \App\Models\Offer::find($item['offer_id']);
                
                if ($offer && !$offer->canOrder($item['quantity'])) {
                    if ($offer->status !== \App\Enums\OfferStatus::ACTIVE) {
                        $validator->errors()->add("items.{$index}.offer_id", "Cette offre n'est plus disponible");
                    } elseif ($offer->remaining_quantity < $item['quantity']) {
                        $validator->errors()->add("items.{$index}.quantity", "Stock insuffisant pour cette offre");
                    }
                }
            }
        });
    }
}
