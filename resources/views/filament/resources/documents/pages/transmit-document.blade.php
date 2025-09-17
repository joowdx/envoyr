<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Transmittal Details
        </x-slot>
        
        <x-slot name="description">
            Fill out the details below to transmit this document to another office or section
        </x-slot>

        {{ $this->form }}
    </x-filament::section>

</x-filament-panels::page>
