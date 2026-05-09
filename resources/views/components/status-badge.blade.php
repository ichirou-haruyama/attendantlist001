@props(['status'])

@php
    $classes = match ($status) {
        \App\Models\Member::STATUS_PRESENT => 'bg-emerald-100 text-emerald-700',
        \App\Models\Member::STATUS_ABSENT => 'bg-rose-100 text-rose-700',
        default => 'bg-slate-100 text-slate-600',
    };
@endphp

<span {{ $attributes->class("inline-flex rounded-full px-2.5 py-1 text-sm font-bold {$classes}") }}>
    {{ $status }}
</span>
