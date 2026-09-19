@extends('layouts.app')
@section('title', 'Notifikasi')
@section('nav-notifications', 'active')
@section('crumb', 'Beranda')

@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">Notifikasi</h1>
    <p class="page-desc">Informasi status pengajuan dan aktivitas sistem.</p>
  </div>
  @if($notifications->isNotEmpty())
    <form method="POST" action="{{ route('notifications.read-all') }}">
      @csrf
      <button class="btn btn-outline">
        <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Tandai semua dibaca
      </button>
    </form>
  @endif
</div>

@if($notifications->isEmpty())
  <div class="card p-10 text-center text-sm" style="color:var(--ink-500)">
    <div class="empty-state">
      <div class="es-ic"><svg style="width:24px;height:24px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 01-6 0"/></svg></div>
      <p class="font-semibold text-[13.5px]" style="color:var(--ink-700)">Belum Ada Notifikasi</p>
      <p class="text-[12.5px] mt-1">Pemberitahuan tentang pengajuan dan dokumen akan muncul di sini.</p>
    </div>
  </div>
@else
  @php
    $collection = $notifications->getCollection();
    $grouped = [
      'Hari Ini' => $collection->filter(fn ($n) => $n->created_at->isToday()),
      'Kemarin'  => $collection->filter(fn ($n) => $n->created_at->isYesterday()),
      'Sebelumnya' => $collection->filter(fn ($n) => ! $n->created_at->isToday() && ! $n->created_at->isYesterday()),
    ];
  @endphp
  @foreach($grouped as $groupLabel => $items)
    @if($items->isNotEmpty())
      <div class="mb-6 last:mb-0">
        <div class="flex items-center gap-2.5 mb-3">
          <h3 class="section-title">{{ $groupLabel }}</h3>
          <span class="text-[11px] font-semibold" style="color:var(--ink-300)">{{ $items->count() }}</span>
        </div>
        <div class="card overflow-hidden">
          @foreach ($items as $n)
            <form method="POST" action="{{ route('notifications.read', $n) }}" class="block {{ !$loop->last ? 'border-b' : '' }}"
                  style="border-color:var(--line-soft); {{ $n->read_at ? '' : 'background:var(--blue-50)' }}">
              @csrf
              <button type="submit" class="w-full text-left px-5 py-4 cursor-pointer transition-colors hover:bg-[#F7F9FC]">
                <div class="flex items-start justify-between gap-4">
                  <div class="flex items-start gap-3 min-w-0">
                    <span class="w-2 h-2 rounded-full flex-none" style="margin-top:6px; background:{{ $n->read_at ? 'var(--ink-200)' : 'var(--blue-600)' }}"></span>
                    <div class="min-w-0">
                      <div class="text-[13.5px] font-semibold" style="color:var(--ink-900)">{{ $n->title }}</div>
                      @if($n->body)
                        <div class="text-[12.5px] mt-0.5" style="color:var(--ink-700)">{{ $n->body }}</div>
                      @endif
                      <div class="text-[11px] mt-1 flex items-center gap-2" style="color:var(--ink-300)">
                        <span>{{ $n->created_at->format('d M Y, H:i') }} · {{ $n->created_at->diffForHumans() }}</span>
                        @if($n->url)<span class="font-medium" style="color:var(--blue-600)">Klik untuk buka →</span>@endif
                      </div>
                    </div>
                  </div>
                  @if(! $n->read_at)
                    <span class="badge shrink-0" style="background:var(--blue-50); color:var(--blue-700); border-color:var(--blue-100)">
                      <span class="dot" style="background:var(--blue-600)"></span>Belum dibaca
                    </span>
                  @endif
                </div>
              </button>
            </form>
          @endforeach
        </div>
      </div>
    @endif
  @endforeach
  <div class="mt-4">{{ $notifications->links() }}</div>
@endif
@endsection