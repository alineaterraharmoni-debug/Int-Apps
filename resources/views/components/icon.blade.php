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
    'sun' => '<circle cx="12" cy="12" r="4" /><path d="M12 3v2M12 19v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M3 12h2M19 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4" />',
    'moon' => '<path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5Z" />',
    'chevron-down' => '<path d="M6 9l6 6 6-6" />',
    'download' => '<path d="M12 4v11" /><path d="M7.5 11.5 12 16l4.5-4.5" /><path d="M5 19.5h14" />',
    'search' => '<circle cx="10.5" cy="10.5" r="6.5" /><path d="M20 20l-4.8-4.8" />',
    'sliders' => '<path d="M4 6h10M18 6h2M4 12h4M12 12h8M4 18h13M21 18h-1" /><circle cx="16" cy="6" r="2" /><circle cx="8" cy="12" r="2" /><circle cx="18" cy="18" r="2" />',
    'arrow-up-down' => '<path d="M8 4v16M8 4 4.5 7.5M8 4l3.5 3.5" /><path d="M16 20V4M16 20l3.5-3.5M16 20l-3.5-3.5" />',
    'alert' => '<path d="M12 3 2 20h20L12 3Z" /><path d="M12 10v4" /><circle cx="12" cy="17" r="0.6" fill="currentColor" />',
    'plus' => '<path d="M12 5v14M5 12h14" />',
    'x' => '<path d="M6 6l12 12M18 6 6 18" />',
    'truck' => '<rect x="2" y="7" width="12" height="9" rx="1" /><path d="M14 10h4l3.5 3.5V16h-7.5" /><circle cx="6.5" cy="18" r="1.6" /><circle cx="17" cy="18" r="1.6" />',
];
$path = $icons[$name] ?? '';
@endphp

<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" {{ $attributes }}>
    {!! $path !!}
</svg>
