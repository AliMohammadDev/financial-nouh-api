<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
  public function __construct(
    private NotificationService $notificationService
  ) {}

  public function index(Request $request)
  {
    $user     = $request->user();
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $notifications = $this->notificationService->getUserNotifications(
      user: $user,
      paginate: $paginate,
      perPage: $perPage,
      page: $page
    );


    $counts = $this->notificationService->getNotificationsCount($user);

    return response()->json([
      'data'   => $notifications,
      'counts' => $counts,
    ]);
  }
  public function markAsRead(Request $request, $id)
  {
    $user = $request->user();
    $updated = $this->notificationService->markAsRead($user, $id);

    if ($updated) {
      return response()->json([
        'message' => 'notification marked as read successfully',
      ]);
    }

    return response()->json([
      'message' => 'notification not found',
    ], 404);
  }

  public function markAllAsRead(Request $request)
  {
    $user = $request->user();
    $this->notificationService->markAllAsRead($user);

    return response()->json([
      'message' => 'all notifications marked as read successfully',
    ]);
  }
}
