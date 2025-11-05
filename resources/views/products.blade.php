@extends('shopify-app::layouts.default')

@section('styles')
    @parent
    @vite('resources/css/app.css')
@endsection

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    {{-- Üst Başlık --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl text-gray-800 font-bold">Ürünler</h1>
            <p class="text-gray-500 mt-1">Shopify mağazanızdaki ürünlerin maliyet bilgilerini düzenleyin.</p>
        </div>
    </div>

    {{-- Ürün Tablosu --}}
    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-gray-600 text-sm">
                    <th class="py-3 px-5 text-left font-semibold">Ürün Adı</th>
                    <th class="py-3 px-5 text-left font-semibold">Durum</th>
                    <th class="py-3 px-5 text-left font-semibold">Varyant</th>
                    <th class="py-3 px-5 text-right font-semibold">Eylem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-sm text-gray-800">
                @forelse ($products as $productEdge)
                    @php 
                        $product = is_object($productEdge) && isset($productEdge->node) 
                            ? $productEdge->node 
                            : (object) $productEdge;
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-4 px-5 font-medium">{{ $product->title ?? '—' }}</td>
                        <td class="py-4 px-5">
                            <x-status-badge :status="$product->status ?? 'UNKNOWN'" />
                        </td>
                        <td class="py-4 px-5">{{ $product->totalVariants ?? 0 }}</td>
                        <td class="py-4 px-5 text-right">
                            @php
                                $legacyProductId = isset($product->id) ? \Illuminate\Support\Str::afterLast($product->id, '/') : null;
                            @endphp
                            @if ($legacyProductId)
                                <form method="GET" action="{{ route('product.edit', $legacyProductId) }}" class="inline">
                                    <input type="hidden" name="host" value="{{ request('host') }}">
                                    <button type="submit" class="text-blue-600 hover:text-blue-700 font-medium">
                                        Cost Düzenle
                                    </button>
                                </form>
                            @else
                                <span class="text-gray-400 text-sm">ID bulunamadı</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-gray-500 py-8">
                            Mağazada hiç ürün bulunamadı.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Sayfalama --}}
    <div class="flex justify-between items-center mt-6">
        <form id="paginationForm" method="GET" action="{{ url()->current() }}">
            <input type="hidden" name="after" id="afterInput" value="">
            <input type="hidden" name="before" id="beforeInput" value="">
            <input type="hidden" name="host" value="{{ request('host') }}">
        </form>

        <div class="flex gap-2">
            <button 
                onclick="goToPrevious('{{ $pageInfo->startCursor ?? '' }}')"
                class="px-3.5 py-2 text-sm font-medium border rounded-md {{ $pageInfo && $pageInfo->hasPreviousPage ? 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50' : 'text-gray-400 bg-white border-gray-200 cursor-not-allowed' }}"
                {{ !$pageInfo || !$pageInfo->hasPreviousPage ? 'disabled' : '' }}>
                &larr; Önceki
            </button>

            <button 
                onclick="goToNext('{{ $pageInfo->endCursor ?? '' }}')"
                class="px-3.5 py-2 text-sm font-medium border rounded-md {{ $pageInfo && $pageInfo->hasNextPage ? 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50' : 'text-gray-400 bg-white border-gray-200 cursor-not-allowed' }}"
                {{ !$pageInfo || !$pageInfo->hasNextPage ? 'disabled' : '' }}>
                Sonraki &rarr;
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @parent
    <script>
        function goToNext(cursor) {
            document.getElementById("beforeInput").value = "";
            document.getElementById("afterInput").value = cursor;
            document.getElementById("paginationForm").submit();
        }

        function goToPrevious(cursor) {
            document.getElementById("afterInput").value = "";
            document.getElementById("beforeInput").value = cursor;
            document.getElementById("paginationForm").submit();
        }
    </script>
@endsection
