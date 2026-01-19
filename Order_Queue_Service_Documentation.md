# Order Queue & Kitchen API Service Documentation

**Version:** 1.0.0
**Date:** 2026-01-19
**Repository:** [https://github.com/Tecnophille/codingtask](https://github.com/Tecnophille/codingtask)

---

## 1. Executive Summary

The **Order Queue & Kitchen API** is a backend service designed to manage high-volume order flow for a restaurant mobile platform. It acts as a gatekeeper for the kitchen, ensuring that the number of active orders never exceeds the kitchen's operational capacity, while providing a VIP fast-track for premium customers.

## 2. System Architecture

### 2.1 Technology Stack
- **Language**: PHP 8.2
- **Framework**: Laravel 10.x
- **Database**: MySQL 8.0 (Configuration agnostic, supports SQLite/PostgreSQL)
- **Containerization**: Docker
- **Testing**: PHPUnit

### 2.2 core Components
- **Order Model**: Represents a customer order with lifecycle states (`active`, `completed`) and priority flags (`is_vip`).
- **Kitchen Throttler**: Middleware-level logic within the `OrderController` that checks current capacity ($N$) against active orders ($A$).
  - Rule: If $A \ge N$ and Order $\neq$ VIP, reject with `429`.

## 3. Business Logic & Rules

### 3.1 Kitchen Capacity
The system is configured with a maximum capacity $N$ (default: 5).
- **Throttling**: New orders are rejected if the kitchen is full.
- **Queue Management**: Capacity is freed only when an order is explicitly marked as "completed".

### 3.2 VIP Bypass
Orders flagged as **VIP** are exempt from capacity checks. This ensures high-value customers or critical orders are never rejected, temporarily exceeding the soft limit of $N$.

## 4. API Reference

### 4.1 Create Order
**POST** `/api/orders`

Submits a new order to the kitchen.

**Request Body:**
```json
{
  "items": ["burger", "fries"],
  "pickup_time": "2025-09-26T12:30:00Z",
  "vip": false
}
```

**Responses:**
- `201 Created`: Order accepted.
- `429 Too Many Orders`: Kitchen is full (Standard orders only).
- `422 Unprocessable Entity`: Validation error.

### 4.2 List Active Orders
**GET** `/api/orders/active`

Retrieves a list of all orders currently being prepared (status: `active`).

**Response:**
```json
[
  {
    "id": 1,
    "items": ["burger"],
    "status": "active",
    "is_vip": false,
    "created_at": "..."
  }
]
```

### 4.3 Complete Order
**POST** `/api/orders/{id}/complete`

Marks an order as finished, removing it from the active queue and freeing up one slot in the kitchen capacity.

**Responses:**
- `200 OK`: Data updated.
- `404 Not Found`: Order ID does not exist.

## 5. Configuration & Deployment

### 5.1 Environment Variables
Key configurations in `.env`:
- `KITCHEN_CAPACITY`: Maximum number of non-VIP active orders (Default: 5).
- `DB_CONNECTION`: Database driver (sqlite, mysql).

### 5.2 Docker Deployment
The project includes a `Dockerfile` for easy deployment.

**Build:**
```bash
docker build -t order-api .
```

**Run:**
```bash
docker run -p 8000:8000 order-api
```

## 6. Testing Strategy
The service includes a comprehensive automated test suite in `tests/Feature/OrderTest.php`.

**Key Test Cases:**
- `test_kitchen_capacity_throttling`: Verifies rejection at N=5.
- `test_vip_bypass_capacity`: Verifies acceptance at N=5 for VIPs.
- `test_completing_order_frees_capacity`: Verifies capacity recycling.

**Run Tests:**
```bash
php artisan test
```
