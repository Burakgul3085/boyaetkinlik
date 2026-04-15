@extends('layouts.admin')

@section('title', 'Reklam Alanlari')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Google Ads Alanlari</h1>
    <form method="post" action="{{ route('admin.ads.update') }}" class="card mt-5 space-y-4 p-5">
        @csrf
        <textarea name="ads_header" rows="4" class="input-ui" placeholder="Header reklam kodu / placeholder">{{ $settings['ads_header'] ?? '' }}</textarea>
        <textarea name="ads_left" rows="4" class="input-ui" placeholder="Sol reklam">{{ $settings['ads_left'] ?? '' }}</textarea>
        <textarea name="ads_right" rows="4" class="input-ui" placeholder="Sag reklam">{{ $settings['ads_right'] ?? '' }}</textarea>
        <textarea name="ads_product_detail" rows="4" class="input-ui" placeholder="Urun detay reklam">{{ $settings['ads_product_detail'] ?? '' }}</textarea>
        <button class="btn-primary">Kaydet</button>
    </form>
@endsection
