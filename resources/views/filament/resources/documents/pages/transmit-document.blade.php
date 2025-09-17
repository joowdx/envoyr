<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Transmittal Details
            </x-slot>
            
            <x-slot name="description">
                Fill out the details below to transmit this document to another office or section
            </x-slot>

            <div class="space-y-4">
                <div>
                    {{ $this->form->getComponent('pick_up') }}
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        {{ $this->form->getComponent('office_id') }}
                    </div>
                    <div>
                        {{ $this->form->getComponent('section_id') }}
                    </div>
                </div>

                    <div>
                    {{ $this->form->getComponent('liaison_id') }}
                </div>

                <div class="space-y-4">
                    <div>
                        {{ $this->form->getComponent('purpose') }}
                    </div>
                    <div>
                        {{ $this->form->getComponent('remarks') }}
                    </div>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Attachment Details
            </x-slot>
            
            <x-slot name="description">
                Review and modify the contents to be transmitted with this document
            </x-slot>

            <div>
                {{ $this->form->getComponent('contents') }}
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
