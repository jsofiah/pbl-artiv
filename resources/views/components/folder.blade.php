@props([
    'color'    => '#e8e8ec',
    'tabColor' => '#c8c8d0',
    'size'     => 'sm',
    'imgLeft'   => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=400&q=80&auto=format&fit=crop',
    'imgCenter' => 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=400&q=80&auto=format&fit=crop',
    'imgRight'  => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=400&q=80&auto=format&fit=crop',
])

@php
    $sizeClasses = [
        'xxs' => 'w-36 h-26 md:w-44 md:h-28',
        'xs'  => 'w-44 h-28 md:w-52 md:h-32',
        'sm'  => 'w-56 h-40 md:w-64 md:h-44',
        'md'  => 'w-72 h-50 md:w-88 md:h-58',
    ];
    $fanCardSize = [
        'xxs' => 'w-20 h-24 md:w-24 md:h-28',
        'xs'  => 'w-24 h-28 md:w-28 md:h-32',
        'sm'  => 'w-28 h-32 md:w-32 md:h-40',
        'md'  => 'w-32 h-40 md:w-36 md:h-44',
    ];
@endphp

<div
    x-data="{ isOpen: false, isHover: false }"
    class="w-full flex items-center justify-center"
>
    <div
        @click="isOpen = !isOpen"
        @mouseenter="isHover = true"
        @mouseleave="isHover = false"
        :class="!isOpen && !isHover ? 'hover:-translate-y-1' : ''"
        class="group relative cursor-pointer transition-all duration-200 ease-in"
    >
        <div class="relative {{ $sizeClasses[$size] }}">

            {{-- ============ 3 CARD FAN-OUT ============ --}}
            {{-- Card kiri --}}
            <div
                :class="{
                    '-translate-x-[60%] -translate-y-[40%] -rotate-10 opacity-100': isOpen || isHover,
                    'translate-x-0 translate-y-0 rotate-0 opacity-100': !(isOpen || isHover)
                }"
                class="absolute left-1/4 bottom-6 -translate-x-1/2 z-30 transition-all duration-500 ease-out {{ $fanCardSize[$size] }} bg-white rounded-xl border border-slate-200 overflow-hidden"
            >
                <img src="{{ $imgLeft }}" class="w-full h-full object-cover" alt="">
            </div>

            {{-- Card tengah --}}
            <div
                :class="{
                    'translate-x-0 -translate-y-[70%] rotate-0 opacity-100': isOpen || isHover,
                    'translate-x-0 translate-y-0 rotate-0 opacity-100': !(isOpen || isHover)
                }"
                class="absolute left-1/4 bottom-6 -translate-x-1/2 z-30 transition-all duration-500 ease-out delay-75 {{ $fanCardSize[$size] }} bg-white rounded-xl border border-slate-200 overflow-hidden"
            >
                <img src="{{ $imgCenter }}" class="w-full h-full object-cover" alt="">
            </div>

            {{-- Card kanan --}}
            <div
                :class="{
                    'translate-x-[60%] -translate-y-[40%] rotate-12 opacity-100': isOpen || isHover,
                    'translate-x-0 translate-y-0 rotate-0 opacity-100': !(isOpen || isHover)
                }"
                class="absolute left-1/4 bottom-6 -translate-x-1/2 z-30 transition-all duration-500 ease-out delay-150 {{ $fanCardSize[$size] }} bg-white rounded-xl border border-slate-200 overflow-hidden"
            >
                <img src="{{ $imgRight }}" class="w-full h-full object-cover" alt="">
            </div>

            {{-- ============ BODY FOLDER ============ --}}
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 225 171"
                 class="absolute top-0 left-0 w-full h-full z-20"
                 fill="url(#folderSilverBody)">
                <defs>
                    <linearGradient id="folderSilverBody" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%"  stop-color="#ffffff"/>
                        <stop offset="45%" stop-color="#e8e8ec"/>
                        <stop offset="100%" stop-color="#b8b8c2"/>
                    </linearGradient>
                </defs>
                <path d="M0.798828 10.9374C0.798828 5.39412 5.29256 0.900391 10.8358 0.900391H61.2051C65.7717 0.900391 70.2784 1.93905 74.3843 3.93778L89.013 11.0589C93.1189 13.0576 97.6257 14.0963 102.192 14.0963H214.762C220.305 14.0963 224.799 18.59 224.799 24.1333V153.17C224.799 162.871 216.935 170.735 207.234 170.735H18.3636C8.66286 170.735 0.798828 162.871 0.798828 153.17V10.9374Z"/>
            </svg>

            {{-- Tab folder --}}
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 225 171"
                 class="absolute top-0 left-0 w-full h-full pointer-events-none z-40"
                 fill="url(#folderSilverTab)">
                <defs>
                    <linearGradient id="folderSilverTab" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%"  stop-color="#d0d0d8"/>
                        <stop offset="50%" stop-color="#a8a8b4"/>
                        <stop offset="100%" stop-color="#c8c8d0"/>
                    </linearGradient>
                </defs>
                <path d="M61.2051 0.900391C65.7717 0.900391 70.2784 1.93905 74.3843 3.93778L89.013 11.0589C93.1189 13.0576 97.6257 14.0963 102.192 14.0963H214.762C220.305 14.0963 224.799 18.59 224.799 24.1333V24.1333C224.799 18.59 220.305 14.0963 214.762 14.0963H102.192C97.6257 14.0963 93.1189 13.0576 89.013 11.0589L74.3843 3.93778C70.2784 1.93905 65.7717 0.900391 61.2051 0.900391H10.8358C5.29256 0.900391 0.798828 5.39412 0.798828 10.9374V10.9374C0.798828 5.39412 5.29256 0.900391 10.8358 0.900391H61.2051Z"/>
            </svg>

            {{-- Flaps --}}
            <div
                :class="(isOpen || isHover) ? 'skew-x-12 scale-y-[0.6]' : 'group-hover:skew-x-12 group-hover:scale-y-[0.6]'"
                class="absolute z-30 origin-bottom transition-all duration-300 ease-in-out pointer-events-none top-6 h-[calc(100%-24px)] rounded-[5px_15px_15px_15px] {{ $sizeClasses[$size] }}"
                style="background: linear-gradient(180deg, rgba(240,240,244,0.7) 0%, rgba(208,208,216,0.65) 60%, rgba(168,168,180,0.6) 100%);"
            ></div>
            <div
                :class="(isOpen || isHover) ? '-skew-x-12 scale-y-[0.6]' : 'group-hover:-skew-x-12 group-hover:scale-y-[0.6]'"
                class="absolute z-30 origin-bottom transition-all duration-300 ease-in-out pointer-events-none top-6 h-[calc(100%-24px)] rounded-[5px_15px_15px_15px] {{ $sizeClasses[$size] }}"
                style="background: linear-gradient(180deg, rgba(240,240,244,0.7) 0%, rgba(208,208,216,0.65) 60%, rgba(168,168,180,0.6) 100%);"
            ></div>

        </div>
    </div>
</div>