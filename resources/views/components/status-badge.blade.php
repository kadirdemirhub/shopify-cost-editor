@props(['status'])

@switch($status)
    @case('ACTIVE')
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-medium text-green-700 bg-green-100 rounded-full">
            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Aktif
        </span>
        @break

    @case('ARCHIVED')
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-full">
            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span> Arşiv
        </span>
        @break

    @case('DRAFT')
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">
            <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full"></span> Taslak
        </span>
        @break

    @default
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-medium text-gray-700 bg-gray-50 rounded-full">
            Bilinmiyor
        </span>
@endswitch
