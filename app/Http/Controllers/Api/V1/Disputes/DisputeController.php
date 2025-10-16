<?php

// app/Http/Controllers/Api/V1/Disputes/DisputeController.php
namespace App\Http\Controllers\Api\V1\Disputes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Disputes\CreateDisputeRequest;
use App\Http\Resources\Disputes\DisputeResource;
use App\Models\Order;
use App\Models\Dispute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    /**
     * Créer litige
     */
    public function store(CreateDisputeRequest $request, Order $order): JsonResponse
    {
        $this->authorize('create', [Dispute::class, $order]);

        $data = $request->validated();
        $data['order_id'] = $order->id;
        $data['reported_by'] = auth()->id();
        $data['status'] = 'open';

        // Upload evidence photos
        if ($request->hasFile('evidence_photos')) {
            $photos = [];
            foreach ($request->file('evidence_photos') as $photo) {
                $photos[] = $photo->store('disputes', 'public');
            }
            $data['evidence_photos'] = $photos;
        }

        $dispute = Dispute::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Litige créé',
            'data' => new DisputeResource($dispute),
        ], 201);
    }

    /**
     * Mes litiges
     */
    public function index(Request $request): JsonResponse
    {
        $disputes = Dispute::where('reported_by', auth()->id())
            ->orWhere('reported_against', auth()->id())
            ->with('order', 'reporter', 'reportedUser')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => DisputeResource::collection($disputes),
        ]);
    }

    /**
     * Détail litige
     */
    public function show(Dispute $dispute): JsonResponse
    {
        $this->authorize('view', $dispute);

        return response()->json([
            'success' => true,
            'data' => new DisputeResource($dispute->load(['order', 'reporter', 'reportedUser'])),
        ]);
    }
}