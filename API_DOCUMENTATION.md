# E-commerce API Documentation

## Base URL
```
/api
```

## Authentication Routes

### Register User
- **POST** `/register`
- **Parameters:**
  ```json
  {
    "name": "string (required)",
    "email": "string (required, email, unique)",
    "password": "string (required, min:8)",
    "password_confirmation": "string (required, same:password)"
  }
  ```
- **Response:** User data with authentication token

### Login User
- **POST** `/login`
- **Parameters:**
  ```json
  {
    "email": "string (required, email)",
    "password": "string (required)"
  }
  ```
- **Response:** User data with authentication token

### Logout User (Protected)
- **POST** `/logout`
- **Headers:** `Authorization: Bearer {token}`
- **Response:** Success message

### Get Current User (Protected)
- **GET** `/user`
- **Headers:** `Authorization: Bearer {token}`
- **Response:** Current user data

---

## Product Routes

### Get Products
- **GET** `/products`
- **Parameters:**
  ```json
  {
    "search": "string (optional)",
    "category": "string (optional)",
    "per_page": "integer (optional, max:100, default:15)"
  }
  ```
- **Response:** Paginated list of products

---

## Cart Routes

### Get Cart
- **GET** `/cart`
- **Parameters:**
  ```json
  {
    "user_id": "integer (optional, exists:users,id)",
    "cart_id": "string (optional, uuid)"
  }
  ```
- **Response:** Cart details or 404 if not found

### Create Cart
- **POST** `/cart`
- **Parameters:**
  ```json
  {
    "user_id": "integer (optional, exists:users,id)",
    "cart_id": "string (optional, uuid)"
  }
  ```
- **Response:** Created cart details

### Add Item to Cart
- **POST** `/cart/add`
- **Parameters:**
  ```json
  {
    "user_id": "integer (optional, exists:users,id)",
    "cart_id": "string (optional, uuid)",
    "purchasable_id": "integer (required, exists:product_variants,id)",
    "quantity": "integer (required, min:1, max:10000)",
    "meta": "array (optional)"
  }
  ```
- **Response:** Updated cart with added item details

### Update Cart Lines
- **PUT** `/cart/update`
- **Parameters:**
  ```json
  {
    "user_id": "integer (optional, exists:users,id)",
    "cart_id": "string (optional, uuid)",
    "lines": [
      {
        "id": "integer (required, exists:cart_lines,id)",
        "quantity": "integer (required, min:0, max:10000)"
      }
    ]
  }
  ```
- **Response:** Updated cart details

### Remove Item from Cart
- **DELETE** `/cart/remove`
- **Parameters:**
  ```json
  {
    "user_id": "integer (optional, exists:users,id)",
    "cart_id": "string (optional, uuid)",
    "line_id": "integer (required, exists:cart_lines,id)"
  }
  ```
- **Response:** Updated cart details

### Clear Cart
- **DELETE** `/cart/clear`
- **Parameters:**
  ```json
  {
    "user_id": "integer (optional, exists:users,id)",
    "cart_id": "string (optional, uuid)"
  }
  ```
- **Response:** Cleared cart details

---

## Checkout Routes

### Get Available Countries
- **GET** `/checkout/countries`
- **Response:** List of available countries for shipping

### Get Shipping Options
- **GET** `/checkout/shipping-options`
- **Parameters:**
  ```json
  {
    "user_id": "integer (optional, exists:users,id)",
    "cart_id": "string (optional, uuid)"
  }
  ```
- **Response:** Available shipping options for the cart
- **Note:** Requires shipping address to be set first

### Get Checkout Summary
- **GET** `/checkout/summary`
- **Parameters:**
  ```json
  {
    "user_id": "integer (optional, exists:users,id)",
    "cart_id": "string (optional, uuid)"
  }
  ```
- **Response:** Complete checkout summary including cart, addresses, shipping options, and checkout steps

### Set Shipping Address
- **POST** `/checkout/shipping-address`
- **Parameters:**
  ```json
  {
    "user_id": "integer (optional, exists:users,id)",
    "cart_id": "string (optional, uuid)",
    "shipping_address": {
      "first_name": "string (required, max:255)",
      "last_name": "string (required, max:255)",
      "company_name": "string (optional, max:255)",
      "line_one": "string (required, max:255)",
      "line_two": "string (optional, max:255)",
      "line_three": "string (optional, max:255)",
      "city": "string (required, max:255)",
      "state": "string (optional, max:255)",
      "postcode": "string (required, max:20)",
      "country_id": "integer (required, exists:countries,id)",
      "delivery_instructions": "string (optional, max:500)",
      "contact_email": "string (required, email, max:255)",
      "contact_phone": "string (optional, max:20)"
    },
    "shipping_is_billing": "boolean (optional, default:false)"
  }
  ```
- **Response:** Updated cart with shipping address (and billing if shipping_is_billing is true)

### Set Billing Address
- **POST** `/checkout/billing-address`
- **Parameters:**
  ```json
  {
    "user_id": "integer (optional, exists:users,id)",
    "cart_id": "string (optional, uuid)",
    "billing_address": {
      "first_name": "string (required, max:255)",
      "last_name": "string (required, max:255)",
      "company_name": "string (optional, max:255)",
      "line_one": "string (required, max:255)",
      "line_two": "string (optional, max:255)",
      "line_three": "string (optional, max:255)",
      "city": "string (required, max:255)",
      "state": "string (optional, max:255)",
      "postcode": "string (required, max:20)",
      "country_id": "integer (required, exists:countries,id)",
      "delivery_instructions": "string (optional, max:500)",
      "contact_email": "string (required, email, max:255)",
      "contact_phone": "string (optional, max:20)"
    }
  }
  ```
- **Response:** Updated cart with billing address

### Set Shipping Option
- **POST** `/checkout/shipping-option`
- **Parameters:**
  ```json
  {
    "user_id": "integer (optional, exists:users,id)",
    "cart_id": "string (optional, uuid)",
    "shipping_option": "string (required)"
  }
  ```
- **Response:** Updated cart with selected shipping option
- **Note:** Requires shipping address to be set first

### Process Checkout
- **POST** `/checkout/process`
- **Parameters:**
  ```json
  {
    "user_id": "integer (optional, exists:users,id)",
    "cart_id": "string (optional, uuid)"
  }
  ```
- **Response:** Created order details
- **Note:** Requires cart to be ready (shipping address, billing address, shipping option, and items in cart)
- **Payment Method:** Cash-in-hand (as specified)

---

## Order Routes

### Get User Orders
- **GET** `/orders`
- **Parameters:**
  ```json
  {
    "user_id": "integer (required, exists:users,id)",
    "status": "string (optional)",
    "per_page": "integer (optional, min:1, max:100, default:15)"
  }
  ```
- **Response:** Paginated list of user's orders

### Get Order Details
- **GET** `/orders/{orderId}`
- **Parameters:**
  ```json
  {
    "user_id": "integer (required, exists:users,id)"
  }
  ```
- **Response:** Detailed order information including lines, addresses, and transactions

---

## Complete E-commerce Flow

### 1. User Registration/Login
```bash
# Register
POST /api/register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}

# Login
POST /api/login
{
  "email": "john@example.com",
  "password": "password123"
}
```

### 2. Browse Products
```bash
GET /api/products?search=laptop&per_page=20
```

### 3. Cart Management
```bash
# Create/Get cart for user
POST /api/cart
{
  "user_id": 1
}

# Add product to cart
POST /api/cart/add
{
  "user_id": 1,
  "purchasable_id": 5,
  "quantity": 2
}

# Update cart item
PUT /api/cart/update
{
  "user_id": 1,
  "lines": [
    {
      "id": 1,
      "quantity": 3
    }
  ]
}
```

### 4. Checkout Process
```bash
# Get available countries
GET /api/checkout/countries

# Set shipping address
POST /api/checkout/shipping-address
{
  "user_id": 1,
  "shipping_address": {
    "first_name": "John",
    "last_name": "Doe",
    "line_one": "123 Main St",
    "city": "New York",
    "postcode": "10001",
    "country_id": 1,
    "contact_email": "john@example.com"
  },
  "shipping_is_billing": true
}

# Get shipping options
GET /api/checkout/shipping-options?user_id=1

# Set shipping option
POST /api/checkout/shipping-option
{
  "user_id": 1,
  "shipping_option": "basic-delivery"
}

# Get checkout summary
GET /api/checkout/summary?user_id=1

# Process checkout
POST /api/checkout/process
{
  "user_id": 1
}
```

### 5. Order Management
```bash
# Get user's orders
GET /api/orders?user_id=1

# Get specific order
GET /api/orders/123?user_id=1
```

---

## Error Responses

All endpoints return consistent error responses:

```json
{
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

Common HTTP status codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## Notes

1. **User vs Guest Carts**: Use `user_id` for logged-in users or `cart_id` (UUID) for guest users
2. **Payment Method**: Currently only supports "cash-in-hand" payment method
3. **Countries**: Currently limited to GBR (United Kingdom) and USA (United States)
4. **Stock Validation**: Automatic stock checking when adding items to cart
5. **Address Validation**: Full address validation with required fields
6. **Checkout Steps**: Sequential checkout process (shipping address → shipping option → billing address → payment)