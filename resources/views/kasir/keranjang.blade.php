@extends('layouts.kasir')

@section('title', 'Keranjang')
@section('back_url', route('kasir.dashboard'))

@section('content')
<div style="max-width:600px; margin: 60px auto; text-align:center; color:#6b7280;">
    <i class="bi bi-cart3" style="font-size:3rem; opacity:0.3; display:block; margin-bottom:16px;"></i>
    <h5 style="font-weight:700; color:#1a2e0f; margin-bottom:8px;">Halaman Keranjang</h5>
    <p style="font-size:0.875rem;">Halaman ini sedang dalam pengembangan.</p>
    <p style="font-size:0.8rem; margin-top:8px;">Total item: <strong>{{ $totalKeranjang }}</strong> | Total harga: <strong>Rp {{ number_format($totalHarga, 0, ',', '.') }}</strong></p>
</div>
@endsection