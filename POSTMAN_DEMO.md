# Postman Demo: Adding Product to User Cart

## Prerequisites Setup

### 1. Environment Variables
Create a Postman environment with these variables:
- `base_url`: `http://localhost:8000/api` (adjust to your Laravel app URL)
- `user_token`: (will be set after login)
- `user_id`: (will be set after login)
- `cart_id`: (will be set after cart creation)
- `product_variant_id`: (will be set after getting products)

---

## Step-by-Step Demo

### Step 1: Register a User (Optional if user exists)

**Method:** `POST`  
**URL:** `{{base_url}}/register`  
**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "name": "John Doe",
    "email": "john.doe@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Expected Response:**
```json
{
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john.doe@example.com"
        },
        "token": "1|abc123def456..."
    }
}
```

**Post-Response Script:**
```javascript
if (pm.response.code === 201) {
    const response = pm.response.json();
    pm.environment.set("user_token", response.data.token);
    pm.environment.set("user_id", response.data.user.id);
}
```

---

### Step 2: Login User (If not registering)

**Method:** `POST`  
**URL:** `{{base_url}}/login`  
**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "email": "john.doe@example.com",
    "password": "password123"
}
```

**Expected Response:**
```json
{
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john.doe@example.com"
        },
        "token": "2|xyz789abc123..."
    }
}
```

**Post-Response Script:**
```javascript
if (pm.response.code === 200) {
    const response = pm.response.json();
    pm.environment.set("user_token", response.data.token);
    pm.environment.set("user_id", response.data.user.id);
}
```

---

### Step 3: Get Available Products

**Method:** `GET`  
**URL:** `{{base_url}}/products`  
**Headers:**
```
Accept: application/json
```

**Query Parameters (Optional):**
- `search`: `laptop`
- `per_page`: `10`

**Expected Response:**
```json
{
    "message": "Products retrieved successfully",
    "data": {
        "products": [
            {
                "id": 1,
                "name": "Gaming Laptop",
                "slug": "gaming-laptop",
                "excerpt": "High-performance gaming laptop",
                "thumbnail": "http://localhost:8000/storage/products/laptop.jpg",
                "price": "$1,299.99",
                "variants": [
                    {
                        "id": 5,
                        "sku": "LAPTOP-001",
                        "stock": 10,
                        "price": "$1,299.99"
                    }
                ]
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 1,
            "per_page": 15,
            "total": 1
        }
    }
}
```

**Post-Response Script:**
```javascript
if (pm.response.code === 200) {
    const response = pm.response.json();
    if (response.data.products.length > 0) {
        // Get the first variant ID from the first product
        const firstProduct = response.data.products[0];
        if (firstProduct.variants && firstProduct.variants.length > 0) {
            pm.environment.set("product_variant_id", firstProduct.variants[0].id);
        }
    }
}
```

---

### Step 4: Create/Get User Cart

**Method:** `POST`  
**URL:** `{{base_url}}/cart`  
**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "user_id": {{user_id}}
}
```

**Expected Response:**
```json
{
    "message": "Cart created successfully",
    "data": {
        "id": 1,
        "cart_id": "550e8400-e29b-41d4-a716-446655440000",
        "user_id": 1,
        "customer_id": 1,
        "total": "$0.00",
        "sub_total": "$0.00",
        "tax_total": "$0.00",
        "discount_total": "$0.00",
        "shipping_total": "$0.00",
        "lines_count": 0,
        "total_quantity": 0,
        "lines": [],
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z"
    }
}
```

**Post-Response Script:**
```javascript
if (pm.response.code === 201 || pm.response.code === 200) {
    const response = pm.response.json();
    pm.environment.set("cart_id", response.data.cart_id);
}
```

---

### Step 5: Add Product to Cart ⭐ (Main Demo)

**Method:** `POST`  
**URL:** `{{base_url}}/cart/add`  
**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "user_id": 1,
    "purchasable_id": 1,
    "quantity": 2,
    "meta": {
        "gift_wrap": false,
        "special_instructions": "Handle with care"
    }
}
```

**Alternative Body (using existing user and product variant):**
```json
{
    "user_id": 2,
    "purchasable_id": 2,
    "quantity": 1
}
```

**Expected Response:**
```json
{
    "message": "Item added to cart successfully",
    "data": {
        "cart": {
            "id": 1,
            "cart_id": "550e8400-e29b-41d4-a716-446655440000",
            "user_id": 1,
            "customer_id": 1,
            "total": "$2,599.98",
            "sub_total": "$2,599.98",
            "tax_total": "$0.00",
            "discount_total": "$0.00",
            "shipping_total": "$0.00",
            "lines_count": 1,
            "total_quantity": 2,
            "lines": [
                {
                    "id": 1,
                    "quantity": 2,
                    "unit_price": "$1,299.99",
                    "sub_total": "$2,599.98",
                    "total": "$2,599.98",
                    "product": {
                        "id": 1,
                        "name": "Gaming Laptop",
                        "slug": "gaming-laptop",
                        "thumbnail": "http://localhost:8000/storage/products/laptop.jpg"
                    },
                    "variant": {
                        "id": 5,
                        "sku": "LAPTOP-001",
                        "stock": 8,
                        "options": []
                    }
                }
            ],
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:35:00.000000Z"
        },
        "added_line": {
            "id": 1,
            "quantity": 2,
            "product": {
                "id": 1,
                "name": "Gaming Laptop",
                "variant_id": 5,
                "sku": "LAPTOP-001"
            }
        }
    }
}
```

---

### Step 6: Verify Cart Contents

**Method:** `GET`  
**URL:** `{{base_url}}/cart`  
**Headers:**
```
Accept: application/json
```

**Query Parameters:**
- `user_id`: `{{user_id}}`

**Expected Response:**
```json
{
    "message": "Cart retrieved successfully",
    "data": {
        "id": 1,
        "cart_id": "550e8400-e29b-41d4-a716-446655440000",
        "user_id": 1,
        "customer_id": 1,
        "total": "$2,599.98",
        "sub_total": "$2,599.98",
        "tax_total": "$0.00",
        "discount_total": "$0.00",
        "shipping_total": "$0.00",
        "lines_count": 1,
        "total_quantity": 2,
        "lines": [
            {
                "id": 1,
                "quantity": 2,
                "unit_price": "$1,299.99",
                "sub_total": "$2,599.98",
                "total": "$2,599.98",
                "product": {
                    "id": 1,
                    "name": "Gaming Laptop",
                    "slug": "gaming-laptop",
                    "thumbnail": "http://localhost:8000/storage/products/laptop.jpg"
                },
                "variant": {
                    "id": 5,
                    "sku": "LAPTOP-001",
                    "stock": 8,
                    "options": []
                }
            }
        ],
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:35:00.000000Z"
    }
}
```

---

## Alternative: Guest Cart Demo

If you want to test with a guest cart instead of a user cart:

### Add Product to Guest Cart

**Method:** `POST`  
**URL:** `{{base_url}}/cart/add`  
**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "cart_id": "550e8400-e29b-41d4-a716-446655440001",
    "purchasable_id": 5,
    "quantity": 1
}
```

---

## Error Scenarios to Test

### 1. Invalid Product Variant
**Body:**
```json
{
    "user_id": 1,
    "purchasable_id": 999999,
    "quantity": 1
}
```
**Expected:** `422 Validation Error`

### 2. Insufficient Stock
**Body:**
```json
{
    "user_id": 1,
    "purchasable_id": 5,
    "quantity": 100
}
```
**Expected:** `422 Insufficient stock available`

### 3. Invalid Quantity
**Body:**
```json
{
    "user_id": 1,
    "purchasable_id": 5,
    "quantity": 0
}
```
**Expected:** `422 Validation Error`

---

## Postman Collection Import

You can create a Postman collection with these requests. Here's the JSON structure:

```json
{
    "info": {
        "name": "E-commerce Cart API",
        "description": "Demo for adding products to user cart"
    },
    "item": [
        {
            "name": "1. Register User",
            "request": {
                "method": "POST",
                "header": [
                    {
                        "key": "Content-Type",
                        "value": "application/json"
                    }
                ],
                "body": {
                    "mode": "raw",
                    "raw": "{\n    \"name\": \"John Doe\",\n    \"email\": \"john.doe@example.com\",\n    \"password\": \"password123\",\n    \"password_confirmation\": \"password123\"\n}"
                },
                "url": {
                    "raw": "{{base_url}}/register",
                    "host": ["{{base_url}}"],
                    "path": ["register"]
                }
            }
        },
        {
            "name": "2. Get Products",
            "request": {
                "method": "GET",
                "url": {
                    "raw": "{{base_url}}/products",
                    "host": ["{{base_url}}"],
                    "path": ["products"]
                }
            }
        },
        {
            "name": "3. Create Cart",
            "request": {
                "method": "POST",
                "header": [
                    {
                        "key": "Content-Type",
                        "value": "application/json"
                    }
                ],
                "body": {
                    "mode": "raw",
                    "raw": "{\n    \"user_id\": {{user_id}}\n}"
                },
                "url": {
                    "raw": "{{base_url}}/cart",
                    "host": ["{{base_url}}"],
                    "path": ["cart"]
                }
            }
        },
        {
            "name": "4. Add Product to Cart",
            "request": {
                "method": "POST",
                "header": [
                    {
                        "key": "Content-Type",
                        "value": "application/json"
                    }
                ],
                "body": {
                    "mode": "raw",
                    "raw": "{\n    \"user_id\": {{user_id}},\n    \"purchasable_id\": {{product_variant_id}},\n    \"quantity\": 2\n}"
                },
                "url": {
                    "raw": "{{base_url}}/cart/add",
                    "host": ["{{base_url}}"],
                    "path": ["cart", "add"]
                }
            }
        },
        {
            "name": "5. Get Cart",
            "request": {
                "method": "GET",
                "url": {
                    "raw": "{{base_url}}/cart?user_id={{user_id}}",
                    "host": ["{{base_url}}"],
                    "path": ["cart"],
                    "query": [
                        {
                            "key": "user_id",
                            "value": "{{user_id}}"
                        }
                    ]
                }
            }
        }
    ]
}
```

This demo provides a complete workflow for testing the "Add Product to Cart" functionality with proper setup, execution, and verification steps.

---

## Quick Test with Your Database Data

Since your database already has users and products, you can test immediately:

### Quick Add to Cart Test

**Method:** `POST`  
**URL:** `http://localhost:8000/api/cart/add`  
**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "user_id": 1,
    "purchasable_id": 1,
    "quantity": 2
}
```

**Available Test Data in Your Database:**
- **Users:** ID 1 (Fern Jacobson), ID 2 (Jose Brekke)
- **Product Variants:** ID 1, 2, 3 (Levi's® 501® Straight Fit Jeans variants)
- **Stock:** 500 units available for each variant

### Verify Cart Contents

**Method:** `GET`  
**URL:** `http://localhost:8000/api/cart?user_id=1`

This should show the cart with the added Levi's jeans.