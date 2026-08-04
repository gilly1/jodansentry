@php
    $colorMap = [
        'slate' => 'bg-slate-100 text-slate-700',
        'amber' => 'bg-amber-100 text-amber-700',
        'sky' => 'bg-sky-100 text-sky-700',
        'red' => 'bg-red-100 text-red-700',
        'indigo' => 'bg-indigo-100 text-indigo-700',
        'blue' => 'bg-blue-100 text-blue-700',
        'emerald' => 'bg-emerald-100 text-emerald-700',
        'orange' => 'bg-orange-100 text-orange-700',
        'stone' => 'bg-stone-100 text-stone-700',
    ];
    $color = $colorMap[$status->color()] ?? 'bg-slate-100 text-slate-700';
@endphp

<span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $color }}">
    {{ $status->label() }}
</span>
