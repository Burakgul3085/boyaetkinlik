@extends('layouts.admin')

@section('title', 'Üyeler')

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Site Üyeleri</h1>
            <p class="mt-1 text-sm text-slate-500">
                Kayıtlı üyelerin iletişim bilgileri, sepet ve satın almaları. Şifreler güvenlik gereği saklanmaz ve görüntülenemez.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-lg bg-slate-200 px-3 py-1.5 text-xs text-slate-600" title="Sunucu saati">
                Güncellendi: {{ now()->format('d.m.Y H:i:s') }}
            </span>
            <a href="{{ route('admin.members.index', request()->query()) }}" class="btn-secondary text-sm">Yenile</a>
        </div>
    </div>

    <form method="get" action="{{ route('admin.members.index') }}" class="card mt-5 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[16rem] flex-1">
            <label class="mb-1 block text-xs font-medium text-slate-600">Ara (ad, soyad, e-posta)</label>
            <input type="search" name="q" value="{{ $search }}" class="input-ui w-full" placeholder="ör. ayse@… veya Ad Soyad">
        </div>
        <button type="submit" class="btn-primary">Ara</button>
        @if($search !== '')
            <a href="{{ route('admin.members.index') }}" class="btn-secondary">Temizle</a>
        @endif
    </form>

    <div class="card mt-5 overflow-x-auto p-4">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-slate-500">
                    <th class="py-2 pr-2">Üye</th>
                    <th class="pr-2">E-posta</th>
                    <th class="pr-2">Doğrulama</th>
                    <th class="pr-2">Sepet</th>
                    <th class="pr-2">Ödeme tamamlandı</th>
                    <th class="pr-2">Son işlem</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($members as $member)
                <tr class="border-t border-slate-100">
                    <td class="py-3 pr-2">
                        <p class="font-semibold text-slate-900">{{ $member->display_name }}</p>
                    </td>
                    <td class="pr-2 text-slate-700">{{ $member->email }}</td>
                    <td class="pr-2">
                        @if($member->email_verified_at)
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">E-posta onaylı</span>
                        @else
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Bekliyor</span>
                        @endif
                    </td>
                    <td class="pr-2">
                        <span class="font-semibold text-indigo-700">{{ $member->cart_items_count }}</span>
                        <span class="text-slate-400">ürün</span>
                    </td>
                    <td class="pr-2">
                        <span class="font-semibold text-emerald-700">{{ $member->paid_transactions_count }}</span>
                        <span class="text-slate-400">işlem</span>
                    </td>
                    <td class="pr-2 whitespace-nowrap text-slate-600">{{ $member->updated_at?->format('d.m.Y H:i') }}</td>
                    <td class="text-right">
                        <a href="{{ route('admin.members.show', $member) }}" class="btn-secondary inline-flex px-3 py-1.5 text-xs">Detay</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-10 text-center text-slate-500">Kayıtlı üye bulunamadı.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        @if($members->hasPages())
            <div class="mt-4 border-t border-slate-100 pt-4">
                {{ $members->links() }}
            </div>
        @endif
    </div>
@endsection
