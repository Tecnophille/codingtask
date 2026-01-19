# My 5 Cent Explanation: Order Queue & Kitchen API

This project is a backend service for a restaurant's mobile ordering platform, acting like a "traffic controller" for the kitchen.

## The Core Problem
The kitchen has a limit. It can only handle **5 active orders** at a time. If we blindly accept every order, the kitchen gets overwhelmed.

## The Solution
This API manages the order queue with a strict **Capacity Throttling** system:
1.  **Validation**: When a new order comes in (`POST /orders`), we check how many orders are currently "active" (cooking).
2.  **Enforcement**:
    *   If active orders < 5: **Accept** (201 Created).
    *   If active orders >= 5: **Reject** (429 Too Many Orders).

## The Exception (VIP)
**VIPs** break the rules. If an order is marked `VIP: true`, it bypasses the limit and is accepted immediately, even if the kitchen is full.

## Tech Stack
*   **Laravel**: The framework powering the logic.
*   **MySQL**: Where we store the orders.
*   **Docker**: For running the app consistently in a container.
*   **Feature Tests**: proof that the logic works (run `php artisan test`).
