@props(['qrCode', 'code'])

<div class="flex flex-col items-center justify-center min-h-[350px]">
    <div class="relative w-64 h-64 flex items-center justify-center">
        <img src="{{ $qrCode }}" alt="QR Code" class="w-full h-full" />
        {{-- Overlay positioned in the center with minimal interference --}}
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <span
                class="px-3 py-1 rounded bg-white/90 text-black text-base font-semibold shadow border border-gray-300"
                style="line-height: 1.2; min-width: 80px; text-align: center;"
            >
                {{ $code }}
            </span>
        </div>
    </div>
</div> 