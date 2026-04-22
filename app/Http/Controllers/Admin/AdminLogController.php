<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class AdminLogController extends Controller
{
    public function index(Request $request)
    {
        $admins = User::query()
            ->where('is_admin', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->orderBy('email')
            ->get();

        $query = AdminActivityLog::query()
            ->with('admin:id,name,first_name,last_name,email')
            ->latest();

        if ($request->filled('admin_id')) {
            $query->where('admin_id', (int) $request->input('admin_id'));
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', (string) $request->input('event_type'));
        }

        if ($request->filled('module')) {
            $query->where('module', (string) $request->input('module'));
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', (string) $request->input('date_from').' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', (string) $request->input('date_to').' 23:59:59');
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($inner) use ($q) {
                $inner->where('description', 'like', '%'.$q.'%')
                    ->orWhere('route_name', 'like', '%'.$q.'%')
                    ->orWhere('subject_type', 'like', '%'.$q.'%');
            });
        }

        $logs = $query->paginate(40)->withQueryString();

        $eventTypeLabels = [
            'login' => 'Giriş',
            'logout' => 'Çıkış',
            'view' => 'Görüntüleme',
            'create' => 'Ekleme',
            'update' => 'Güncelleme',
            'delete' => 'Silme',
            'approve' => 'Onaylama',
            'reject' => 'Reddetme',
        ];

        $moduleLabels = [
            'auth' => 'Kimlik',
            'members' => 'Üyeler',
            'categories' => 'Kategoriler',
            'pages' => 'Boyama Sayfaları',
            'settings' => 'Sayfa Ayarları',
            'ads' => 'Reklam Alanları',
            'transactions' => 'İşlemler',
            'purchase-verifications' => 'Satın Alım Doğrulama',
            'newsletter' => 'E-Bülten',
            'visitor-feedback' => 'Ziyaretçi Yorumları',
            'admin-users' => 'Admin Yönetimi',
            'dashboard' => 'Genel Bakış',
            'logs' => 'Admin Logları',
            'general' => 'Genel',
        ];

        return view('admin/admin-logs/index', [
            'logs' => $logs,
            'admins' => $admins,
            'eventTypes' => ['login', 'logout', 'view', 'create', 'update', 'delete', 'approve', 'reject'],
            'eventTypeLabels' => $eventTypeLabels,
            'moduleLabels' => $moduleLabels,
            'modules' => AdminActivityLog::query()
                ->select('module')
                ->distinct()
                ->orderBy('module')
                ->pluck('module'),
        ]);
    }
}

