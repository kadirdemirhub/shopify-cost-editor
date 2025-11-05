@extends('shopify-app::layouts.default')

@section('styles')
    @parent
    {{-- Shopify App Bridge --}}
    <script src="https://unpkg.com/@shopify/app-bridge@3"></script>
    @vite('resources/css/app.css')
@endsection

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    {{-- 🔙 Ürün Listesine Geri Dön --}}
    <form id="backForm" method="GET" action="{{ route('home') }}" class="mb-4">
        <input type="hidden" name="host" value="{{ request('host') }}">
        <button type="submit" 
            class="text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
            &larr; Ürün Listesine Geri Dön
        </button>
    </form>

    <h1 class="text-2xl font-semibold mb-2">{{ $product->title ?? 'Ürün Bulunamadı' }}</h1>
    <p class="text-gray-600 mb-6">Varyantların maliyet (cost) bilgilerini düzenleyin.</p>

    <form id="cost-form">
        @csrf
        <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="py-3 px-6 font-semibold text-gray-700">Varyant</th>
                        <th class="py-3 px-6 font-semibold text-gray-700">SKU</th>
                        <th class="py-3 px-6 font-semibold text-gray-700">Mevcut Maliyet (Cost)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($variants as $variantEdge)
                        @php 
                            $variant = $variantEdge->node ?? (object) [];
                            $inventoryItemId = $variant->inventoryItem->id ?? null;
                            $currentCost = $variant->inventoryItem->unitCost->amount ?? 0.00;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="py-4 px-6">{{ $variant->title ?? '—' }}</td>
                            <td class="py-4 px-6">{{ $variant->sku ?? 'N/A' }}</td>
                            <td class="py-4 px-6">
                                @if($inventoryItemId)
                                    <span class="text-gray-500 mr-1">$</span>
                                    <input type="number" step="0.01"
                                           name="inventory_costs[{{ $inventoryItemId }}]"
                                           value="{{ $currentCost }}"
                                           class="p-2 border border-gray-300 rounded-md w-32 focus:border-blue-500 focus:ring-blue-500">
                                @else
                                    <span class="text-gray-400 text-sm">Inventory ID yok</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-10 px-6 text-center text-gray-500">
                                Bu ürünün hiç varyantı bulunamadı.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <button type="button" id="save-button"
                class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300">
                Maliyetleri Kaydet
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
@parent
<script>
document.addEventListener("DOMContentLoaded", () => {
    const AppBridge = window["app-bridge"] || window["ShopifyAppBridge"];
    const createApp = AppBridge.default || AppBridge;
    const { Toast } = AppBridge.actions;
    const { authenticatedFetch } = AppBridge.utilities;

    const app = createApp({
        apiKey: "{{ env('SHOPIFY_API_KEY') }}",
        host: new URLSearchParams(window.location.search).get("host"),
        forceRedirect: true,
    });

    const saveButton = document.getElementById("save-button");
    saveButton.addEventListener("click", async () => {
        saveButton.disabled = true;
        saveButton.innerText = "Kaydediliyor...";

        const form = document.getElementById("cost-form");
        const inputs = form.querySelectorAll('input[name^="inventory_costs"]');
        const costsPayload = {};
        inputs.forEach((input) => {
            const key = input.name.match(/\[(.*?)\]/)[1];
            costsPayload[key] = input.value;
        });

        const payload = {
            inventory_costs: costsPayload,
            _token: document.querySelector('input[name="_token"]').value,
        };

        try {
            const fetchFunction = authenticatedFetch(app);
            const response = await fetchFunction("{{ route('product.update-cost') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(payload),
            });

            const data = await response.clone().json();

            const toast = Toast.create(app, {
                message: data.success
                    ? "Maliyetler başarıyla güncellendi!"
                    : "Shopify yanıtı başarısız.",
                duration: 3000,
                isError: !data.success,
            });
            toast.dispatch(Toast.Action.SHOW);

            if (data.success) {
                document.getElementById("backForm").submit();
            }
        } catch (e) {
            const toast = Toast.create(app, {
                message: "Hata: " + e.message,
                duration: 4000,
                isError: true,
            });
            toast.dispatch(Toast.Action.SHOW);
        } finally {
            saveButton.disabled = false;
            saveButton.innerText = "Maliyetleri Kaydet";
        }
    });
});
</script>
@endsection
