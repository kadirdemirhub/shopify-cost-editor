<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ShopifyService;
use Illuminate\Support\Facades\Auth;
use Mockery;

class ShopifyServiceTest extends TestCase
{
    protected $shopifyService;
    protected $mockShop;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock user (shop)
        $this->mockShop = Mockery::mock();
        $this->mockShop->shouldReceive('api')->andReturnSelf();
        
        // Mock Auth
        Auth::shouldReceive('user')->andReturn($this->mockShop);

        $this->shopifyService = new ShopifyService();
    }

    /** @test */
    public function it_fetches_products_correctly()
    {
        // Mock GraphQL response
        $this->mockShop->shouldReceive('graph')
            ->once()
            ->andReturn([
                'body' => (object)[
                    'data' => (object)[
                        'products' => (object)[
                            'edges' => [],
                            'pageInfo' => (object)['hasNextPage' => false],
                        ]
                    ]
                ]
            ]);

        [$products, $pageInfo] = $this->shopifyService->getProducts();

        $this->assertIsArray($products);
        $this->assertFalse($pageInfo->hasNextPage);
    }

    /** @test */
    public function it_updates_inventory_costs_correctly()
    {
        $this->mockShop->shouldReceive('graph')
            ->twice() // 2 cost update
            ->andReturn([
                'body' => (object)[
                    'data' => (object)[
                        'inventoryItemUpdate' => (object)[
                            'inventoryItem' => (object)['id' => 'gid://shopify/InventoryItem/123'],
                            'userErrors' => []
                        ]
                    ]
                ]
            ]);

        [$updated, $errors] = $this->shopifyService->updateInventoryCosts([
            'gid://shopify/InventoryItem/123' => '50',
            'gid://shopify/InventoryItem/456' => '75',
        ]);

        $this->assertCount(2, $updated);
        $this->assertEmpty($errors);
    }
}
