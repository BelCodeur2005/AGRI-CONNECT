<?php

// app/Http/Controllers/Api/V1/Notifications/NotificationController.php
namespace App\Http\Controllers\Api\V1\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Resources\Notifications\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Liste des notifications
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => NotificationResource::collection($notifications),
            'unread_count' => $request->user()->unread_notifications_count,
        ]);
    }

    /**
     * Marquer comme lue
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        if ($notification->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé',
            ], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marquée comme lue',
        ]);
    }

    /**
     * Marquer toutes comme lues
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->markAllNotificationsAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Toutes les notifications sont marquées comme lues',
        ]);
    }

    /**
     * Supprimer notification
     */
    public function destroy(Notification $notification): JsonResponse
    {
        if ($notification->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé',
            ], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification supprimée',
        ]);
    }
}
