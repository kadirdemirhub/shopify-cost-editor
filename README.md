# 🧾 Shopify Cost Editor – Laravel Embedded App

A small Shopify embedded app built with **Laravel 11**, allowing merchants to **view and edit product costs** directly from their Shopify Admin.  
When a cost is updated inside this app, it **syncs automatically to Shopify** via the **GraphQL Admin API**.

---

## 🚀 Features

### 🔐 Shopify Authentication
- Full OAuth 2.0 flow using [`kyon147/laravel-shopify`](https://github.com/kyon147/laravel-shopify).
- Embedded app experience (runs inside Shopify Admin).
- Access token and shop domain stored securely in database.
- Required scopes:
  ```
  read_products, write_products, read_inventory, write_inventory
  ```

---

### 📦 Product List
- Paginated table (10 items per page).
- Columns: Product **Title**, **Status**, **Variant Count**, and **Edit** button.
- Pagination handled with Shopify GraphQL cursors (`after`, `before`).

---

### 💰 Edit Cost
- Click “Cost Düzenle” to open the product detail.
- Displays all variants (SKU, current cost, editable input).
- On save:
  - Sends GraphQL mutation to update `inventoryItem.unitCost`.
  - Shows success toast and refreshes the view.
- Fully uses `Shopify App Bridge` for authentication and embedded context.

---

## 🧱 Tech Stack

| Layer | Technology |
|-------|-------------|
| Backend | Laravel 11, PHP 8.2 |
| Frontend | Blade + TailwindCSS |
| Shopify SDK | `kyon147/laravel-shopify` |
| API | Shopify Admin GraphQL |
| Auth | OAuth via App Bridge |
| Database | MySQL |
| Tests | PHPUnit (Unit Tests) |

---

## 🧩 Project Structure

```
app/
 ├─ Http/
 │   └─ Controllers/
 │       └─ ProductController.php
 ├─ Services/
 │   └─ ShopifyService.php
resources/
 ├─ views/
 │   ├─ products.blade.php
 │   ├─ product-edit.blade.php
 │   └─ components/
 │       └─ status-badge.blade.php
 ├─ js/
 │   └─ shopify-edit.js
tests/
 └─ Unit/
     └─ ShopifyServiceTest.php
```

---

## ⚙️ Installation Guide

### 1️⃣ Clone the Repository
```bash
git clone https://github.com/kadirdemirhub/shopify-cost-editor.git
cd shopify-cost-editor
```

### 2️⃣ Install Dependencies
```bash
composer install
npm install && npm run build
```

### 3️⃣ Configure Environment
Copy `.env.example` to `.env` and fill in:
```env
SHOPIFY_API_KEY=your_api_key
SHOPIFY_API_SECRET=your_api_secret
SHOPIFY_API_VERSION=2024-04
SHOPIFY_APP_URL=https://your-app-domain.com
SHOPIFY_APP_HANDLE=solverhood-test-app
```

### 4️⃣ Database Setup
```bash
php artisan migrate
```

### 5️⃣ Run the App
```bash
php artisan serve
```

Then open your **Shopify Partner Dashboard** and set your app URL:
```
https://your-ngrok-url/auth
```

---

## 🧭 Shopify Developer Setup (Dev Store Installation)

1. Go to your **Shopify Partner Dashboard → Apps → Create App**  
   - Choose **Custom App**  
   - App URL:  
     ```
     https://<your-ngrok-subdomain>.ngrok-free.dev/auth
     ```
   - Allowed redirect URL(s):  
     ```
     https://<your-ngrok-subdomain>.ngrok-free.dev/auth/callback
     ```

2. Copy the generated **API Key & Secret** into your `.env`.

3. Click **“Test on development store”** → select your dev store.

4. Shopify will install the app into the store and redirect you to:  
   ```
   https://admin.shopify.com/store/<your-store>/apps/solverhood-test-app
   ```

5. Your Laravel app runs embedded inside Shopify Admin — no extra setup needed.  

✅ From here you can view products, edit costs, and save changes live.

---

## 🧪 Running Tests

This project includes a `ShopifyServiceTest` that validates service layer logic  
without touching the real Shopify API.

```bash
php artisan test --filter=ShopifyServiceTest
```

Example Output:
```
PASS  Tests\Unit\ShopifyServiceTest
✓ it fetches products correctly
✓ it updates inventory costs correctly
```

---

## ⚠️ Troubleshooting

| Issue | Possible Fix |
|--------|---------------|
| App 404 after login | Check `APP_URL` matches your Ngrok URL and is HTTPS. |
| Session errors | Use `SESSION_DRIVER=database` and run `php artisan session:table && migrate`. |
| Redirect loop | Ensure `SHOPIFY_APP_EMBEDDED=true` in .env. |

---

## 💡 Error Handling
- API calls wrapped in `try/catch`.
- User feedback through Shopify `Toast` notifications.
- 422 and API-level userErrors are handled gracefully.

---

## 📚 GraphQL Queries Used

### Fetch Products
```graphql
{
  products(first: 10, after: $cursor) {
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
```

### Fetch Product Variants
```graphql
query getProductVariants($id: ID!) {
  product(id: $id) {
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
```

### Update Inventory Cost
```graphql
mutation updateInventoryCost($id: ID!, $input: InventoryItemInput!) {
  inventoryItemUpdate(id: $id, input: $input) {
    inventoryItem { id unitCost { amount currencyCode } }
    userErrors { field message }
  }
}
```

---

## 👤 Author

**Kadir Demir**
📧 kadirr1574@icloud.com
