@props(['qrCode', 'code'])

<div class="flex items-center justify-center min-h-[350px]">
    <div class="relative inline-block">
        <img src="{{ $qrCode }}" alt="QR Code" class="w-64 h-64 block" />
        {{-- Absolutely positioned overlay in the center --}}
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none z-20">
            <span class="bg-white/90 text-black text-base font-semibold rounded px-3 py-1.5 border border-gray-300 shadow-sm whitespace-nowrap">
                {{ $code }}
            </span>
        </div>
    </div>
</div> 