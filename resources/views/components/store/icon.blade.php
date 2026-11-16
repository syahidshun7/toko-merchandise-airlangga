@props(['name'])
<svg {{ $attributes->class('size-5 shrink-0') }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('home') <path d="M3 10l9-7 9 7M5 9v12h5v-6h4v6h5V9"/> @break
        @case('menu') <path d="M4 6h16M4 12h16M4 18h16"/> @break
        @case('filter') <path d="M3 4h18l-7 8v6l-4 2v-8z"/> @break
        @case('shirt') <path d="m8 4-5 3 2 5 3-1v9h8v-9l3 1 2-5-5-3a4 4 0 0 1-8 0Z"/> @break
        @case('bag') <path d="M5 8h14l2 13H3zM8 9V6a4 4 0 0 1 8 0v3"/> @break
        @case('bottle') <path d="M9 3h6v4l3 4v8a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-8l3-4zM7 12h10M7 17h10"/> @break
        @case('book') <rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 3v18m3-13h4m-4 4h4"/> @break
        @case('search') <circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/> @break
        @case('grid') <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/> @break
        @case('calendar') <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 10h18m-14 5h3m4 0h3"/> @break
        @case('chat') <path d="M4 20l1-4a9 9 0 1 1 4 4z"/><path d="M9 8c0 4 3 7 7 7l1-2-3-1-1 1c-1-1-2-2-2-3l1-1-1-2z"/> @break
        @case('left') <path d="m15 5-7 7 7 7"/> @break
        @case('right') <path d="m9 5 7 7-7 7"/> @break
    @endswitch
</svg>

