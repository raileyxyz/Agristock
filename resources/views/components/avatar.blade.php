@props(['user' => null, 'size' => 'w-10 h-10', 'textSize' => 'text-sm'])

@php
    $user = $user ?? Auth::user();

    $nameParts = preg_split('/\s+/', trim($user->name));
    $firstInitial = strtoupper(substr($nameParts[0] ?? '', 0, 1));
    $lastInitial = strtoupper(substr(end($nameParts) ?: '', 0, 1));
    $initials = count($nameParts) > 1
        ? $firstInitial . $lastInitial
        : $firstInitial;
@endphp

@if($user->avatar)
    <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}"
         {{ $attributes->merge(['class' => "$size rounded-full object-cover shrink-0"]) }}>
@else
    <div {{ $attributes->merge(['class' => "$size rounded-full bg-green-600 text-white flex items-center justify-center $textSize font-semibold shrink-0"]) }}>
        {{ $initials }}
    </div>
@endif
