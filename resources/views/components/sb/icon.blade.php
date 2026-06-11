@props([
    'name',
    'class' => 'h-5 w-5',
])

@php
    $svgClass = trim('shrink-0 ' . $class);
@endphp

@switch($name)
    @case('search')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14Z"/>
        </svg>
        @break
    @case('globe')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.6 9h16.8M3.6 15h16.8M12 3a15.3 15.3 0 0 1 4 9 15.3 15.3 0 0 1-4 9 15.3 15.3 0 0 1-4-9 15.3 15.3 0 0 1 4-9Z"/>
        </svg>
        @break
    @case('book-open')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.04 19.5 3v17.22l-7.5 3-7.5-3V3L12 6.04Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.04 4.5 3v17.22l7.5 3V6.04Z"/>
        </svg>
        @break
    @case('package')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 16-9 5-9-5"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 8 9 5 9-5"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8v8l9 5 9-5V8"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 3 8l9 5 9-5-9-5Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 12v10"/>
        </svg>
        @break
    @case('map-pin')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 11.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 9.75c0 7.03-7.5 11.25-7.5 11.25S4.5 16.78 4.5 9.75a7.5 7.5 0 1 1 15 0Z"/>
        </svg>
        @break
    @case('clock')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 2"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/>
        </svg>
        @break
    @case('flame')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3s3 3.6 3 6.75a3 3 0 1 1-6 0C9 6.6 12 3 12 3Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 14.25a3.75 3.75 0 0 0 7.5 0c0-2.25-1.5-3.75-1.5-5.25 0-1.5-1.5-2.25-1.5-2.25S10.5 9 10.5 12c0 1.5-2.25 2.25-2.25 2.25Z"/>
        </svg>
        @break
    @case('tag')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.57 5.25H5.25v4.32L15.75 20.25l4.32-4.32L9.57 5.25Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h.008v.008H7.5V7.5Z"/>
        </svg>
        @break
    @case('crosshair')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v3m0 12v3m9-9h-3M6 12H3m15.36-6.36-2.12 2.12M8.76 15.24l-2.12 2.12m0-10.48 2.12 2.12m8.48 8.48 2.12 2.12"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z"/>
        </svg>
        @break
    @case('utensils')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5v15M15.75 9v10.5M12 4.5v3m0 0v12"/>
        </svg>
        @break
    @case('chart-bar')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 16.5V10M12 16.5V6M16.5 16.5v-6"/>
        </svg>
        @break
    @case('users')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
        </svg>
        @break
    @case('user-smile')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Z"/>
        </svg>
        @break
    @case('trending-down')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="m18 9-6 6-4-4-6 6"/>
        </svg>
        @break
    @case('calendar')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 8.25h18M4.5 6.75h15a1.5 1.5 0 0 1 1.5 1.5v11.25a1.5 1.5 0 0 1-1.5 1.5h-15a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5Z"/>
        </svg>
        @break
    @case('leaf')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.5 0-8-3.5-8-8 0-6 8-13 8-13s8 7 8 13c0 4.5-3.5 8-8 8Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21V11"/>
        </svg>
        @break
    @case('heart')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0 5.25-9 12-9 12S3 13.5 3 8.25a5.25 5.25 0 0 1 9-1.5 5.25 5.25 0 0 1 9 1.5Z"/>
        </svg>
        @break
    @case('heart-solid')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.292 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.218l-.022.012-.007.003-.002.001h-.002Z"/>
        </svg>
        @break
    @case('bolt')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 7.5-10.5 3 6h6l-7.5 10.5-3-6h-6Z"/>
        </svg>
        @break
    @case('rocket')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 14.25 3 21v-5.25M14.25 9.75 21 3h-5.25"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 14.25-2.25 2.25M6 18l-3 3"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 12a3 3 0 1 0-6 0 3 3 0 0 0 6 0Z"/>
        </svg>
        @break
    @case('gift')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5v15M12 7.5H7.5a2.25 2.25 0 0 1 0-4.5H9a2.25 2.25 0 0 1 2.25-2.25 2.25 2.25 0 0 1 2.25 2.25h1.5a2.25 2.25 0 0 1 0 4.5H12Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5v9a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-9"/>
        </svg>
        @break
    @case('funnel')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18l-7.2 8.4V19.5l-3.6 1.8v-7.8L3 4.5Z"/>
        </svg>
        @break
    @case('star')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M11.48 3.5a.75.75 0 0 1 1.04 0l2.7 2.66c.13.13.31.2.49.17l3.77-.55a.75.75 0 0 1 .83.83l-.55 3.77c-.03.18.04.36.17.49l2.66 2.7c.52.53.15 1.43-.59 1.43h-3.69a.75.75 0 0 0-.45.14l-3.1 1.63a.75.75 0 0 1-.7 0l-3.1-1.63a.75.75 0 0 0-.45-.14H5.59c-.74 0-1.11-.9-.59-1.43l2.66-2.7a.75.75 0 0 0 .17-.49l-.55-3.77a.75.75 0 0 1 .83-.83l3.77.55c.18.03.36-.04.49-.17l2.7-2.66Z"/>
        </svg>
        @break
    @case('check-circle')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
        </svg>
        @break
    @case('x-circle')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m15 9-6 6m0-6 6 6"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
        </svg>
        @break
    @case('eye')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
        </svg>
        @break
    @case('bank')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 4.5l9 6v1.5H3V10.5Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12v6m4.5-6v6m4.5-6v6M21 19.5H3"/>
        </svg>
        @break
    @case('banknotes')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75h18v10.5H3V6.75Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 9.75h.008v.008H7.5V9.75Zm9 0h.008v.008H16.5V9.75Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75a3 3 0 0 0 3-3v-1.5a3 3 0 0 0-6 0v1.5a3 3 0 0 0 3 3Z"/>
        </svg>
        @break
    @case('credit-card')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h18v9H3v-9Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5h18"/>
        </svg>
        @break
    @case('document-text')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 3.75h6M9 6.75h3.375a1.125 1.125 0 0 1 1.125 1.125V19.5a1.5 1.5 0 0 1-1.5 1.5H6.75a1.5 1.5 0 0 1-1.5-1.5V6a1.5 1.5 0 0 1 1.5-1.5h1.5Z"/>
        </svg>
        @break
    @case('pencil-square')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 12h.008v.008H18V12Zm0 4.5h.008v.008H18V16.5Zm-9 0h.008v.008H9V16.5Z"/>
        </svg>
        @break
    @case('trash')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18 16.5a3 3 0 0 1-3 3h-6a3 3 0 0 1-3-3l-.47-7.27m13.18 0a48.05 48.05 0 0 0-4.44 0m4.44 0 .93-1.74a1.5 1.5 0 0 0-1.07-2.06l-1.64-.22m-6.34 0a48.05 48.05 0 0 1-4.44 0m0 0-.93-1.74a1.5 1.5 0 0 1 1.07-2.06l1.64-.22"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9h4.5m-4.5 0a3 3 0 0 1-.66-1.85V6.75m5.25 2.25v-1.5A3 3 0 0 0 12 4.5h-.75A3 3 0 0 0 8.25 6.75v.4a3 3 0 0 1-.66 1.85"/>
        </svg>
        @break
    @case('x-mark')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
        </svg>
        @break
    @case('arrow-down-tray')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
        </svg>
        @break
    @case('check')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
        </svg>
        @break
    @case('sparkles')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3v2.25M9.75 18.75V21m-4.5-9.75H3m18 0h-2.25M14.25 9l1.5-1.5M8.25 15l-1.5 1.5m0-7.5L6.75 9m10.5 1.5 1.5 1.5"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z"/>
        </svg>
        @break
    {{-- Category chips (match SurpriseBiteController catalog ids) --}}
    @case('bakery')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V10.5l7-4.5 7 4.5V21"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 21v-6h6v6"/>
        </svg>
        @break
    @case('rice')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12c0-3 2.25-6 6-6s6 3 6 6"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15l-1.5 7.5h-12L4.5 12Z"/>
        </svg>
        @break
    @case('noodles')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 10c2-2 4-2 6 0s4 2 6 0"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 14c2-2 4-2 6 0s4 2 6 0"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 18h8l-1-4H9l-1 4Z"/>
        </svg>
        @break
    @case('salad')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c-2 4-6 6-6 10a6 6 0 0 0 12 0c0-4-4-6-6-10Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 13v8"/>
        </svg>
        @break
    @case('drinks')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 4h8l-1 14a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2L8 4Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10"/>
        </svg>
        @break
    @case('cafe')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 8h10a3 3 0 0 1 0 6H6V8Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 14v5h8v-5M16 11h2a2 2 0 0 0 0-4h-2"/>
        </svg>
        @break
    @case('italian')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 19.5h16L12 3Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6"/>
        </svg>
        @break
    @case('japanese')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h5v5H6V6Zm7 0h5v5h-5V6Zm-7 7h5v5H6v-5Zm7 0h5v5h-5v-5Z"/>
        </svg>
        @break
    @case('bento-deco')
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" opacity=".9">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 5h6v6H5V5Zm8 0h6v4h-6V5ZM5 13h6v6H5v-6Zm8 6h6v-8h-6v8Z"/>
        </svg>
        @break
    @default
        <svg {{ $attributes->merge(['class' => $svgClass]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/>
        </svg>
@endswitch
