@extends('layouts.customer')

@section('title', 'Beri Ulasan')

@section('content')
<div class="min-h-[70vh] bg-slate-100 px-4 py-10">
    <form method="POST" action="#"
          class="mx-auto w-full max-w-2xl rounded-2xl bg-white p-6 shadow-sm sm:p-8">
        @csrf

        <div class="text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#D5FC55] shadow">
                <svg class="h-7 w-7 text-slate-900" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h1 class="mt-5 text-2xl font-extrabold text-slate-900">Pesanan Selesai! Bagaimana Pengalaman Anda?</h1>
            <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                Berikan penilaian untuk membantu Kreator berkembang dan menjaga standar kualitas kurasi di ARTIV.
            </p>
        </div>

        <div class="mt-8">
            <h2 class="text-lg font-bold text-slate-900">Bagaimana Pengalaman Anda?</h2>
            <p class="text-xs text-slate-500">
                {{ $order->product->name ?? 'Layanan Desain' }} oleh
                <span class="font-semibold text-[#6D28D9]">{{ $order->creator->name ?? '-' }}</span>
            </p>
        </div>

        <hr class="my-5 border-slate-200">

        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Skor Kepuasan Keseluruhan</p>
        <div class="mt-2 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-5 py-4">
            <div class="flex gap-1" id="stars" role="radiogroup" aria-label="Skor kepuasan">
                @for ($i = 1; $i <= 5; $i++)
                    <button type="button" data-value="{{ $i }}" role="radio" aria-checked="false"
                            aria-label="{{ $i }} bintang"
                            class="star rounded text-slate-300 transition hover:scale-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#6D28D9]">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </button>
                @endfor
            </div>
            <input type="hidden" name="rating" id="rating" value="{{ old('rating') }}">

            <div class="border-l border-slate-200 pl-5 text-right">
                <p class="text-2xl font-extrabold text-[#6D28D9]">
                    <span id="rating-number">0.0</span>
                    <span class="text-sm font-medium text-slate-400">/ 5.0</span>
                </p>
                <span id="rating-label" class="mt-1 hidden rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700"></span>
            </div>
        </div>
        @error('rating') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

        <p class="mt-6 text-[11px] font-bold uppercase tracking-wider text-slate-500">Aspek Unggulan yang Dirasakan</p>
        <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-3">
           @foreach ($aspects as $key => $aspect)
            <label class="flex cursor-pointer">
            <input type="checkbox" name="aspects[]" value="{{ $key }}" class="peer sr-only"
               @checked(in_array($key, old('aspects', [])))>
            <span class="flex min-h-[44px] w-full items-center justify-center gap-1.5 rounded-full border border-[#6D28D9] bg-white px-4 py-2.5 text-center text-xs font-semibold leading-tight text-[#6D28D9] transition peer-checked:bg-[#6D28D9] peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-[#6D28D9] peer-focus-visible:ring-offset-2">
            <span>{{ $aspect['icon'] }}</span> {{ $aspect['label'] }}
        </span>
    </label>
        @endforeach
        </div>

        <div class="mt-6 flex items-end justify-between">
            <label for="comment" class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Bagikan Pengalaman Anda</label>
            <span class="text-[11px] text-slate-400"><span id="char-count">0</span> / 500 Karakter</span>
        </div>
        <textarea id="comment" name="comment" rows="4" maxlength="500"
                  placeholder="Ceritakan pengalaman Anda bekerja dengan kreator ini..."
                  class="mt-2 w-full resize-none rounded-xl border border-slate-200 px-4 py-3 text-sm italic text-slate-700 placeholder:not-italic placeholder:text-slate-400 focus:border-[#6D28D9] focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/20">{{ old('comment') }}</textarea>
        @error('comment') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

        <label class="mt-4 flex items-center gap-2 text-xs text-slate-600">
            <input type="checkbox" name="is_public" value="1" checked
                   class="h-4 w-4 rounded border-slate-300 text-[#6D28D9] focus:ring-[#6D28D9]">
            Tampilkan ulasan ini secara publik di halaman profil kreator
        </label>

        <hr class="my-6 border-slate-200">

        <div class="flex justify-end gap-3">
            <a href="{{ route('customer.pesanan') }}"
               class="rounded-full border border-[#6D28D9] px-6 py-2.5 text-xs font-semibold text-[#6D28D9] hover:bg-violet-50">
                Lewati
            </a>
            <button type="submit" id="btn-submit" disabled
                    class="rounded-full bg-[#D5FC55] px-6 py-2.5 text-xs font-bold text-slate-900 shadow transition hover:bg-[#C5EC45] disabled:cursor-not-allowed disabled:opacity-50">
                Kirim Review →
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    const labels  = @json($labels);
    const stars   = document.querySelectorAll('#stars .star');
    const input   = document.getElementById('rating');
    const number  = document.getElementById('rating-number');
    const label   = document.getElementById('rating-label');
    const submit  = document.getElementById('btn-submit');
    const comment = document.getElementById('comment');
    const counter = document.getElementById('char-count');

    function paint(value) {
        stars.forEach(s => {
            const v = Number(s.dataset.value);
            s.classList.toggle('text-amber-400', v <= value);
            s.classList.toggle('text-slate-300', v > value);
            s.setAttribute('aria-checked', v === Number(input.value) ? 'true' : 'false');
        });
    }

    function setRating(value) {
        input.value = value;
        number.textContent = value.toFixed(1);
        label.textContent = labels[value];
        label.classList.remove('hidden');
        submit.disabled = false;
        paint(value);
    }

    stars.forEach(s => {
        s.addEventListener('click', () => setRating(Number(s.dataset.value)));
        s.addEventListener('mouseenter', () => paint(Number(s.dataset.value)));
        s.addEventListener('mouseleave', () => paint(Number(input.value) || 0));
    });

    comment.addEventListener('input', () => counter.textContent = comment.value.length);
    counter.textContent = comment.value.length;
    if (input.value) setRating(Number(input.value));
})();
</script>
@endpush
@endsection