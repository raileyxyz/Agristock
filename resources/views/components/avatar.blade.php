@props(['user' => null, 'size' => 'w-10 h-10', 'textSize' => 'text-sm'])

@php
    $user = $user ?? Auth::user();
@endphp

@if($user->avatar)
    <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}"
         {{ $attributes->merge(['class' => "$size rounded-full object-cover shrink-0"]) }}>
@else
    <div {{ $attributes->merge(['class' => "$size rounded-full bg-green-600 text-white flex items-center justify-center $textSize font-semibold shrink-0"]) }}>
        {{ strtoupper(substr($user->name, 0, 2)) }}
    </div>
@endif
