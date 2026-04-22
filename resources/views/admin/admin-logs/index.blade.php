@extends('layouts.admin')

@section('title', 'Admin Log Kayıtları')

@section('content')
    <section class="space-y-5">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Admin Log Kayıtları</h1>
            <p class="mt-1 text-sm text-slate-600">Tüm admin işlemleri en yeni en üstte olacak şekilde listelenir. Filtrelerle admin, işlem türü, modül ve tarih bazında daraltabilirsiniz.</p>
        </div>

        <form method="get" class="card grid gap-3 p-4 md:grid-cols-6">
            <label class="input-ui">
                Admin
                <select name="admin_id" class="mt-1 w-full">
                    <option value="">Tümü</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" @selected(request('admin_id') == $admin->id)>{{ $admin->display_name }} ({{ $admin->email }})</option>
                    @endforeach
                </select>
            </label>
            <label class="input-ui">
                İşlem Türü
                <select name="event_type" class="mt-1 w-full">
                    <option value="">Tümü</option>
                    @foreach($eventTypes as $eventType)
                        <option value="{{ $eventType }}" @selected(request('event_type') === $eventType)>{{ $eventType }}</option>
                    @endforeach
                </select>
            </label>
            <label class="input-ui">
                Modül
                <select name="module" class="mt-1 w-full">
                    <option value="">Tümü</option>
                    @foreach($modules as $module)
                        <option value="{{ $module }}" @selected(request('module') === $module)>{{ $module }}</option>
                    @endforeach
                </select>
            </label>
            <label class="input-ui">
                Başlangıç Tarihi
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="mt-1 w-full">
            </label>
            <label class="input-ui">
                Bitiş Tarihi
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="mt-1 w-full">
            </label>
            <label class="input-ui">
                Arama
                <input type="text" name="q" value="{{ request('q') }}" placeholder="rota, açıklama..." class="mt-1 w-full">
            </label>
            <div class="md:col-span-6 flex flex-wrap gap-2 justify-end">
                <a href="{{ route('admin.logs.index') }}" class="btn-secondary">Filtreyi Temizle</a>
                <button class="btn-primary">Filtrele</button>
            </div>
        </form>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-slate-700">Tarih</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-700">Admin</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-700">İşlem</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-700">Modül</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-700">Açıklama</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-700">Detay</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-3 py-2 whitespace-nowrap">{{ optional($log->created_at)->format('d.m.Y H:i:s') }}</td>
                            <td class="px-3 py-2">{{ $log->admin?->display_name }}<br><span class="text-xs text-slate-500">{{ $log->admin?->email }}</span></td>
                            <td class="px-3 py-2"><span class="rounded-full bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-700">{{ $log->event_type }}</span></td>
                            <td class="px-3 py-2">{{ $log->module }}</td>
                            <td class="px-3 py-2">{{ $log->description }}</td>
                            <td class="px-3 py-2 text-xs text-slate-600">
                                <div>Rota: {{ $log->route_name ?: '-' }}</div>
                                <div>Method: {{ $log->http_method ?: '-' }}</div>
                                <div>IP: {{ $log->ip_address ?: '-' }}</div>
                                @if($log->subject_type || $log->subject_id)
                                    <div>Subject: {{ $log->subject_type ?: '-' }} #{{ $log->subject_id ?: '-' }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-slate-500">Kayıt bulunamadı.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-3 py-3">
                {{ $logs->links() }}
            </div>
        </div>
    </section>
@endsection

