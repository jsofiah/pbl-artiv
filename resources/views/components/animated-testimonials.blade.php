@props([
    'testimonials' => [],
    'duration' => '25s',
    'gap' => '12px',
])

@php
    // Fallback data kalau gak dikasih dari parent
    if (empty($testimonials)) {
        $testimonials = [
            [
                'name' => 'Isabelle Carlos',
                'handle' => '@isabellecarlos',
                'description' => 'ARTIV bener-bener ngebantu aku nemuin desainer yang cocok. Prosesnya cepat, hasilnya memuaskan!',
                'image' => 'https://images.unsplash.com/photo-1611558709798-e009c8fd7706?q=80&w=400&auto=format&fit=crop',
                'rating' => 5,
            ],
            [
                'name' => 'Lana Akash',
                'handle' => '@lanaakash',
                'description' => 'Desainnya profesional banget, komunikasi sama desainernya juga lancar. Recommended!',
                'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=400&auto=format&fit=crop',
                'rating' => 5,
            ],
            [
                'name' => 'Liam O\'Connor',
                'handle' => '@liamoc',
                'description' => 'Sistem pembayaran aman, tracking progress jelas. Worth it banget buat bisnis kecil kayak aku.',
                'image' => 'https://images.unsplash.com/photo-1552374196-c4e7ffc6e126?q=80&w=400&auto=format&fit=crop',
                'rating' => 4,
            ],
            [
                'name' => 'Isabella Mendes',
                'handle' => '@isamendes',
                'description' => 'Fitur chat-nya bikin komunikasi sama desainer jadi gampang. Gak perlu pindah aplikasi.',
                'image' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=400&auto=format&fit=crop',
                'rating' => 5,
            ],
            [
                'name' => 'Meera Patel',
                'handle' => '@meerapatel',
                'description' => 'Harga transparan, gak ada biaya tersembunyi. Puas banget pakai ARTIV!',
                'image' => 'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?q=80&w=400&auto=format&fit=crop',
                'rating' => 5,
            ],
            [
                'name' => 'Emily Chen',
                'handle' => '@emchen',
                'description' => 'Rating & review dari user lain ngebantu banget buat milih desainer yang tepat.',
                'image' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?q=80&w=400&auto=format&fit=crop',
                'rating' => 5,
            ],
        ];
    }
@endphp

<div class="w-full overflow-x-hidden py-4">
    @foreach ([false, true, false] as $reverse)
        <div
            class="group relative flex h-full w-full overflow-hidden p-2 flex-row"
            style="--duration: {{ $duration }}; --gap: {{ $gap }}; gap: {{ $gap }};"
        >
            {{-- Repeat 3x untuk seamless loop --}}
            @for ($r = 0; $r < 3; $r++)
                <div
                    class="flex shrink-0 flex-row group-hover:paused animate-canopy-horizontal {{ $reverse ? 'reverse' : '' }}"
                    style="gap: {{ $gap }};"
                >
                    @foreach ($testimonials as $t)
                        <div
                            class="group/card mx-2 flex h-32 w-80 shrink-0 cursor-pointer overflow-hidden rounded-xl border border-slate-200 bg-white p-3 transition-all hover:border-[#6D28D9] hover:shadow-[0_0_10px_#6D28D9]/40"
                        >
                            <div class="flex items-start gap-3 w-full">
                                {{-- Avatar --}}
                                <div class="relative h-12 w-12 shrink-0 overflow-hidden rounded-full border-2 border-slate-200">
                                    <img src="{{ $t['image'] }}"
                                         alt="{{ $t['name'] }}"
                                         class="h-full w-full object-cover"
                                         loading="lazy">
                                </div>

                                {{-- Content --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-baseline gap-2 flex-wrap">
                                        <span class="text-sm font-bold text-slate-900">{{ $t['name'] }}</span>
                                        <span class="text-xs text-slate-400">{{ $t['handle'] }}</span>
                                    </div>

                                    {{-- Bintang --}}
                                    @if (!empty($t['rating']))
                                        <div class="flex items-center gap-0.5 mt-0.5">
                                            @for ($s = 1; $s <= 5; $s++)
                                                <svg class="w-3 h-3 {{ $s <= $t['rating'] ? 'text-yellow-400' : 'text-slate-200' }}"
                                                     fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                    @endif

                                    <p class="mt-1 line-clamp-3 text-sm text-slate-600">
                                        {{ $t['description'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endfor

            {{-- Mask gradient kiri-kanan --}}
            {{-- Mask gradient kiri-kanan --}}
<div class="pointer-events-none absolute inset-0 z-10 h-full w-full"
     style="background: linear-gradient(to right, #F3F1FA 0%, transparent 15%, transparent 85%, #F3F1FA 100%);">
</div>
        </div>
    @endforeach
</div>