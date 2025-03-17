<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->get();
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ],
        ]);
    }

    public function markAsRead(Request $request, $notificationId): JsonResponse
    {
        $notification = $request->user()->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
        return response()->json(['status' => 'success']);
    }

    public function detailNotification(Request $request, $notificationId) : JsonResponse
    {
        $notification = $request->user()->notifications()->find($notificationId);
        if (!$notification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $notification,
        ]);
    }

    public function deleteNotification(Request $request, $notificationId): JsonResponse
    {
        $notification = $request->user()->notifications()->find($notificationId);
        if (!$notification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found',
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification deleted successfully',
        ]);
    }
}
