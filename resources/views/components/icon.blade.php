@props(['name'])

@php
$icons = [
    'home' => '<path d="M4 11.5 12 4l8 7.5" /><path d="M6 10v9a1 1 0 0 0 1 1h3v-6h4v6h3a1 1 0 0 0 1-1v-9" />',
    'users' => '<circle cx="9" cy="8" r="3" /><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5" /><circle cx="17" cy="9" r="2.4" /><path d="M15.5 14.3c2.6.3 4.5 2.3 4.5 5.2" />',
    'briefcase' => '<rect x="3" y="8" width="18" height="11" rx="1.5" /><path d="M8 8V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /><path d="M3 13h18" />',
    'file-text' => '<path d="M7 3h7l4 4v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z" /><path d="M14 3v4h4" /><path d="M9 13h6M9 16.5h6M9 9.5h2" />',
    'chart-bar' => '<path d="M4 20V10M10 20V4M16 20v-7M4 20h16" />',
    'layout-kanban' => '<rect x="4" y="4" width="16" height="16" rx="1.5" /><path d="M9 4v16M15 4v9" />',
    'building-store' => '<path d="M4 10v9a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-9" /><path d="M3 6l1.5-3h15L21 6" /><path d="M3 6a2 2 0 0 0 4 0 2 2 0 0 0 4 0 2 2 0 0 0 4 0 2 2 0 0 0 4 0" /><path d="M10 20v-5h4v5" />',
    'logout' => '<path d="M9 4H6a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h3" /><path d="M15 16l4-4-4-4" /><path d="M19 12H9" />',
    'key' => '<circle cx="8" cy="15" r="4" /><path d="M10.5 12.5 20 3" /><path d="M17 6l2 2" /><path d="M14 9l2 2" />',
    'shield' => '<path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3Z" /><path d="M9 12l2 2 4-4" />',
    'chevron-down' => '<path d="M6 9l6 6 6-6" />',
];
$path = $icons[$name] ?? '';
@endphp

<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" {{ $attributes }}>
    {!! $path !!}
</svg>
