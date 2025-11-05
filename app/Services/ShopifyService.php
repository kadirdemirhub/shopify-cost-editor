<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ShopifyService
{
    public function getProducts($after = null)
    {
        $shop = Auth::user();
        if (!$shop) return [[], null];

        $afterPart = $after ? ", after: \"{$after}\"" : '';
        $query = <<<GRAPHQL
        {
            products(first: 10{$afterPart}) {
                edges {
                    cursor
                    node {
                        id
                        title
                        status
                        totalVariants
                    }
                }
                pageInfo {
                    hasNextPage
                    hasPreviousPage
                    endCursor
                    startCursor
                }
            }
        }
        GRAPHQL;

        $result = $shop->api()->graph($query);
        $data   = $result['body']->data->products ?? null;

        return [$data->edges ?? [], $data->pageInfo ?? null];
    }

    public function getProductWithVariants($id)
    {
        $shop = Auth::user();
        $gid  = "gid://shopify/Product/{$id}";

        $query = <<<GRAPHQL
        query getProductVariants(\$id: ID!) {
            product(id: \$id) {
                id
                title
                variants(first: 20) {
                    edges {
                        node {
                            id
                            title
                            sku
                            inventoryItem {
                                id
                                unitCost { amount }
                            }
                        }
                    }
                }
            }
        }
        GRAPHQL;

        $result  = $shop->api()->graph($query, ['id' => $gid]);
        $product = $result['body']->data->product ?? null;
        return $product;
    }

    public function updateInventoryCosts(array $costs)
    {
        $shop   = Auth::user();
        $errors = [];
        $updated = [];

        $mutation = <<<GRAPHQL
        mutation updateInventoryCost(\$id: ID!, \$input: InventoryItemInput!) {
          inventoryItemUpdate(id: \$id, input: \$input) {
            inventoryItem { id unitCost { amount currencyCode } }
            userErrors { field message }
          }
        }
        GRAPHQL;

        foreach ($costs as $inventoryItemId => $cost) {
            $variables = [
                'id' => $inventoryItemId,
                'input' => ['cost' => (float) $cost],
            ];

            $result = $shop->api()->graph($mutation, $variables);
            $data   = $result['body']->data->inventoryItemUpdate ?? null;
            $userErrors = $data->userErrors ?? [];

           if (!empty($userErrors) && is_array($userErrors) && count($userErrors) > 0) {
    $errors[] = $userErrors;
} else {
    $updated[] = $data->inventoryItem ?? null;
}

        }

        return [$updated, $errors];
    }
}
