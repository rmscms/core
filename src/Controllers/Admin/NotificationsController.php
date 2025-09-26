<?php

namespace RMS\Core\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder as QueryBuilder;
use RMS\Core\Controllers\Admin\AdminController;
use RMS\Core\Data\Field;
use RMS\Core\Contracts\List\HasList;
// Note: No forms for notifications list; only list and filters are implemented.
use RMS\Core\Contracts\Filter\ShouldFilter;
use RMS\Core\Models\Notification;
use RMS\Core\Models\Admin as CoreAdminModel;

class NotificationsController extends AdminController implements HasList, ShouldFilter
{
    // Required: table name
    public function table(): string
    {
        return 'rms_notifications';
    }

    // Required: model class
    public function modelName(): string
    {
        return Notification::class;
    }

    // For RouteHelper compatibility if ever used
    public function baseRoute(): string
    {
        return 'notifications';
    }

    public function routeParameter(): string
    {
        return 'notification';
    }

    // List fields for generic list template
    public function getListFields(): array
    {
        return [
            Field::make('id')->withTitle('ID')->sortable()->width('80px'),
            Field::make('title')->withTitle(trans('auth.title'))->searchable()->sortable(),
            Field::make('message')->withTitle(trans('auth.message'))->searchable()->width('40%'),
            Field::make('category')->withTitle(trans('auth.category'))->sortable()->width('120px'),
            Field::date('created_at')->withTitle(trans('auth.created_at'))->sortable()->width('150px'),
            Field::date('read_at')->withTitle(trans('auth.read_at'))->width('150px'),
        ];
    }

    // Restrict list to current admin's notifications
    public function query(\Illuminate\Database\Query\Builder $sql): void
    {
        parent::query($sql);
        $adminId = Auth::guard('admin')->id();
        $sql->where('notifiable_type', CoreAdminModel::class)
            ->where('notifiable_id', $adminId);
    }

    // JSON: unread list for navbar/offcanvas
    public function unreadJson(Request $request)
    {
        $adminId = Auth::guard('admin')->id();
        $items = Notification::query()
            ->where('notifiable_type', CoreAdminModel::class)
            ->where('notifiable_id', $adminId)
            ->whereNull('read_at')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['id','title','message','category','created_at'])
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => $n->message,
                    'category' => $n->category,
                    'created_at' => $n->created_at,
                    'created_at_persian' => \RMS\Helper\persian_date($n->created_at, 'Y/m/d H:i'),
                ];
            });

        return response()->json([
            'count' => $items->count(),
            'items' => $items,
        ]);
    }

    // Mark a single notification as read
    public function markRead(Request $request, int $id)
    {
        $adminId = Auth::guard('admin')->id();
        $affected = Notification::query()
            ->where('id', $id)
            ->where('notifiable_type', CoreAdminModel::class)
            ->where('notifiable_id', $adminId)
            ->update(['read_at' => now()]);

        return response()->json(['ok' => (bool)$affected]);
    }

    // Mark all unread as read
    public function markAllRead(Request $request)
    {
        $adminId = Auth::guard('admin')->id();
        $affected = Notification::query()
            ->where('notifiable_type', CoreAdminModel::class)
            ->where('notifiable_id', $adminId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => (bool)$affected]);
    }
}
