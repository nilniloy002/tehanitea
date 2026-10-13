# Tehani Premium Tea — Ecommerce Development Plan

## 1. Project Overview

Tehani Premium Tea will have a modern, mobile-first ecommerce website where customers can discover products, select a pack-size variant, place an order easily, and pay through Cash on Delivery or manual bKash payment.

The store will use guest checkout. Customers do not need to create an account; their order information will be retained securely in the customer database for service, repeat-order support, reporting and marketing with consent where applicable.

## 2. Technology Stack

| Area | Recommended technology |
| --- | --- |
| Backend | Laravel 12 |
| Admin dashboard | Filament 5 |
| PHP | PHP 8.2 |
| Database | MySQL 8 |
| Frontend | Blade + Livewire + Tailwind CSS |
| Interactions | Alpine.js |
| Media storage | Laravel Storage; local storage initially, S3-compatible storage when needed |
| Payment | Cash on Delivery and manual bKash |
| Notifications | SMS / WhatsApp-ready order notification service; email optional |
| Queue | Laravel queue with database driver initially; Redis later if order volume grows |

Laravel Blade, Livewire and Tailwind CSS are the recommended frontend approach for this first version. It keeps the storefront fast, SEO-friendly, and easy to maintain from one Laravel codebase.

## 3. Customer Website Pages

### Primary Pages

- Home / landing page
- Shop / all products
- Product category page
- Product details page
- Cart
- Checkout
- Order confirmation page
- Order tracking page
- Delivery and payment information
- About Tehani
- Contact / WhatsApp enquiry
- Privacy policy, terms and refund / return policy

### Home Page Sections

1. Hero banner with Tehani brand message and product image
2. Featured products
3. Why Tehani: quality, taste, purity and nationwide delivery
4. Premium Gold Tea highlight
5. Full Leaf Green Tea highlight
6. Tea moments / lifestyle section
7. Delivery and payment overview
8. Customer review section, enabled when testimonials are available
9. WhatsApp enquiry call to action
10. Footer with contact information, Facebook page and policies

## 4. Product and Variant System

Each tea product will be created once. Its purchasable pack sizes will be created as variants under that product.

### Example: Premium Gold Tea

| Product | Variant / Pack Size | Price | SKU | Stock |
| --- | --- | ---: | --- | ---: |
| Premium Gold Tea | 250g | Admin-controlled | TPG-250 | Admin-controlled |
| Premium Gold Tea | 500g | Admin-controlled | TPG-500 | Admin-controlled |
| Premium Gold Tea | 1kg | Admin-controlled | TPG-1000 | Admin-controlled |
| Premium Gold Tea | 2kg | Admin-controlled | TPG-2000 | Admin-controlled |

The product page will show selectable pack-size buttons. When a customer selects a size, the page updates the price, stock status, SKU and quantity rules without page reload.

Each variant can have its own selling price, regular price, sale price, SKU, available stock, low-stock threshold, weight in grams, product image, sort order and active status.

The same system supports Full Leaf Green Tea and future products without new development.

## 5. Product Page Experience

Every product details page should include:

- Product image gallery with zoom
- Product name and category
- Short benefit-focused description
- Pack-size / variant selector
- Dynamic price and discount display
- Stock availability
- Quantity selector
- Add to cart button
- Buy Now button that takes the customer directly to checkout
- WhatsApp enquiry button
- Delivery charge summary
- Product description, ingredients, net weight, storage instructions and usage / brew guide
- Related products

### WhatsApp Enquiry

The enquiry button will open WhatsApp to **01711893169** with a pre-filled message:

> Assalamu Alaikum, I would like to know more about Tehani Premium Gold Tea, 500g.

The phone number must be managed from Admin Settings so it can be changed without a code update.

## 6. Cart and Checkout

### Guest Checkout

No customer account or password will be required. The checkout form will collect only:

- Customer name
- Mobile number
- Delivery address
- Delivery area / zone
- Optional order note
- Payment method
- bKash transaction ID, only when manual bKash is selected

The system will automatically create or update a customer record using the phone number.

### Checkout Flow

1. Customer selects products and variants.
2. Customer enters name, phone number and address.
3. Customer selects delivery zone.
4. System calculates delivery charge, coupon discount, tax and final total.
5. Customer selects COD or manual bKash.
6. Customer confirms the order.
7. System creates the order, reserves / reduces stock, stores payment information and shows confirmation.
8. Admin receives a new-order notification.

## 7. Dynamic Delivery Charge System

The delivery charge will be selected through a controlled zone dropdown at checkout. The exact address remains a separate required field.

| Delivery zone | Charge |
| --- | ---: |
| ঢাকা সিটি | ৳60 |
| ঢাকা সাব-সিটি | ৳100 |
| ঢাকার বাইরে | ৳120 |

### Recommended Setup

Create three active delivery zones in Filament admin. Each zone has a Bangla and English name, delivery charge, optional free-delivery threshold, active status and sort order.

This supports future expansion into district, thana, courier-specific or weight-based delivery rules without redesigning checkout.

## 8. Payment System

### Cash on Delivery

Customer selects **Cash on Delivery**. The order is created with:

- Payment method: COD
- Payment status: Pending
- Order status: Pending Confirmation

Admin confirms the order by phone or operational review, then moves it through processing and delivery.

### Manual bKash Payment

Customer selects **bKash Manual Payment**. The checkout displays the active bKash payment number and clear instructions from Admin Settings. The customer enters the bKash transaction ID after payment.

The order is created with:

- Payment method: Manual bKash
- bKash number shown to customer: saved in the payment record
- Transaction ID: customer-provided
- Payment status: Submitted for Verification
- Order status: Pending Payment Verification

An admin verifies the transaction ID from the bKash merchant statement, then marks the payment as paid or rejected. The system records who verified it and when.

### Admin-Managed bKash Settings

- Account / merchant display name
- bKash number
- Payment instructions
- QR code image, optional
- Active / inactive status

This will later allow a smooth upgrade to bKash payment gateway API without changing the order system.

## 9. Coupon System

Coupons are validated at checkout before the order is created.

### Coupon Types

- Fixed BDT discount
- Percentage discount
- Free delivery

### Coupon Rules

- Coupon code
- Start and end date
- Minimum order amount
- Maximum discount amount for percentage coupons
- Usage limit for all customers
- Per-customer / per-phone usage limit
- Eligible product and category restrictions, optional
- Active / inactive status

The checkout must show the reason when a coupon cannot be applied.

## 10. Tax System

Tax must be configured from Admin Settings, not hard-coded.

### Recommended Tax Setup

For the first release, support one or more active percentage-based tax rates. The business can decide whether tax is inclusive or exclusive.

Recommended default: keep tax **inclusive** unless Tehani is required to display VAT separately. This gives customers a simpler price experience. The system will still store the tax amount on each order for reporting.

The tax rule must be snapshotted into the order at checkout; later changes must not alter old orders.

## 11. Order Management Workflow

### Suggested Order Statuses

| Status | Meaning |
| --- | --- |
| Pending Confirmation | Newly placed COD order awaiting operational confirmation |
| Pending Payment Verification | Manual bKash transaction ID awaiting verification |
| Confirmed | Order and payment / COD confirmation completed |
| Processing | Product is being prepared or packed |
| Shipped | Handed to delivery partner |
| Delivered | Successfully delivered |
| Cancelled | Cancelled before delivery |
| Returned | Returned after delivery attempt |
| Refunded | Payment refunded when applicable |

### Order Management Features

- Unique human-readable order number, such as TH-20261012-0001
- Customer and delivery information
- Ordered variants, quantities and pricing snapshot
- Coupon, delivery and tax breakdown
- Payment method and payment status
- Transaction verification screen for manual bKash
- Internal order notes
- Customer-facing order note
- Order status history with admin user and timestamp
- Printable packing slip / invoice
- Exportable order report

## 12. Customer Database

Customers are created from guest checkout automatically. A customer record is identified primarily by normalized mobile number.

### Customer Record

- Full name
- Mobile number
- Email, optional
- Default delivery address, optional
- Total orders
- Total spent
- Last order date
- Order history
- Internal customer notes
- Marketing consent status, if collected

Do not create login credentials, passwords or customer accounts in phase one.

## 13. Filament Admin Dashboard

### Dashboard

- Today’s orders and sales
- Orders by status
- COD versus bKash manual orders
- Recent orders
- Low-stock variants
- Top-selling products
- Coupon usage summary

### Admin Modules

| Module | Key capabilities |
| --- | --- |
| Categories | Create, edit, activate, reorder and add SEO content |
| Products | Product details, media, descriptions, SEO and status |
| Product Variants | Pack size, price, stock, SKU, weight and sale price |
| Inventory | Stock adjustments, low-stock threshold and movement history |
| Orders | View, confirm, process, ship, cancel, refund and print |
| Customers | Customer history, order value and internal notes |
| Delivery Zones | Manage zone names, charges and free-delivery thresholds |
| Coupons | Create and control discount rules and usage limits |
| Tax Rules | Manage inclusive or exclusive tax rates |
| Payments | Manual bKash instructions, number, QR and verification queue |
| Store Settings | Logo, contact details, WhatsApp number, social links and SEO defaults |
| Banners / Content | Manage homepage banner, featured products and static page blocks |
| Users & Roles | Control staff access by module |
| Reports | Sales, orders, products, customers, coupons and payments |
| Activity Log | Record key admin changes and order operations |

### Suggested Roles

- Super Admin
- Store Manager
- Order & Customer Support
- Inventory Manager
- Content Manager
- Accountant / Payment Verifier

Use Filament Shield or Laravel policies to give each role only the required module permissions.

## 14. Database Architecture

### Core Tables

| Table | Purpose |
| --- | --- |
| users | Admin users and staff accounts |
| roles, permissions, model_has_roles, model_has_permissions | Admin authorization |
| categories | Product categories |
| products | Main tea product information |
| product_variants | Pack-size, price, stock and SKU information |
| product_images | Product gallery and variant-specific images |
| inventory_movements | Every stock adjustment and reason |
| customers | Guest customer database |
| customer_addresses | Saved checkout addresses for order history |
| delivery_zones | Delivery-zone charge configuration |
| carts | Session or guest cart header, optional persisted cart |
| cart_items | Guest cart items, optional persisted cart |
| coupons | Coupon definition and validation rules |
| coupon_usages | Coupon use history and limits |
| tax_rates | Tax configuration |
| orders | Order header and calculated totals |
| order_items | Immutable product-variant, price and quantity snapshot |
| order_status_histories | Order status timeline |
| payments | COD and bKash payment information |
| payment_verifications | Manual bKash approval / rejection log |
| store_settings | Global settings, including WhatsApp and bKash details |
| media | Product, banner, logo and QR-code files |
| activity_log | Admin actions and important changes |

### Important Order Snapshots

At order placement, store product name, selected variant name, SKU, unit price, quantity, delivery zone and charge, coupon code and discount, tax rate and amount, payment method, bKash number shown, and the complete delivery address.

This preserves correct historical invoices and reports even if prices or store settings later change.

## 15. Recommended Data Relationships

~~~mermaid
erDiagram
    CATEGORY ||--o{ PRODUCT : contains
    PRODUCT ||--o{ PRODUCT_VARIANT : has
    PRODUCT ||--o{ PRODUCT_IMAGE : displays
    CUSTOMER ||--o{ ORDER : places
    DELIVERY_ZONE ||--o{ ORDER : calculates
    ORDER ||--|{ ORDER_ITEM : includes
    PRODUCT_VARIANT ||--o{ ORDER_ITEM : purchased_as
    ORDER ||--o{ PAYMENT : uses
    COUPON ||--o{ COUPON_USAGE : records
    ORDER ||--o{ ORDER_STATUS_HISTORY : tracks
~~~

## 16. Stock Handling

Stock must be controlled at variant level because 250g, 500g, 1kg and 2kg packs are separately sellable items.

1. When an order is placed, reserve or deduct the selected variant quantity.
2. If the order is cancelled before dispatch, automatically restore stock.
3. Record manual adjustments with reason, staff member and timestamp.
4. Show low-stock alerts on the dashboard.

For a small initial store, deduct stock at order placement and restore it on cancellation. This is easy for operations and prevents overselling.

## 17. Frontend Design Direction

The frontend should feel modern, warm and premium while remaining very easy to use on mobile.

- Build mobile-first because most Bangladesh customers will order from Facebook, Messenger or mobile browsers.
- Use Tehani’s logo and packaging colours as the main visual direction.
- Use generous whitespace, tea-leaf texture details and warm tea-toned product photography.
- Keep the header compact with Shop, About, Delivery, Contact and a visible cart icon.
- Keep purchase controls prominent: variant buttons, dynamic price, Buy Now and WhatsApp enquiry.
- Use Bangla-first copy, with English product names where relevant.
- Keep checkout to one page and avoid unnecessary fields.

## 18. Security and Operational Requirements

- Validate all checkout inputs server-side.
- Rate-limit checkout, coupon validation and contact submissions.
- Use CSRF protection, secure sessions and hashed admin passwords.
- Never store bKash PINs, OTPs or customer payment credentials.
- Limit manual payment verification access to authorized users.
- Create daily database backups and retain media backups.
- Log stock changes, price changes, coupon changes and payment-verification decisions.
- Use HTTPS before launch.

## 19. Notifications

### Customer

- Order confirmation page immediately after successful order
- Optional SMS / WhatsApp message with order number
- Optional status updates for confirmed, shipped and delivered orders

### Admin

- New order notification
- New manual bKash payment awaiting verification
- Low-stock alert
- Failed payment verification alert, if needed

Initially, admin notifications can be in Filament and email. SMS and WhatsApp services can be added after the preferred provider is selected.

## 20. Delivery Scope for First Release

- Modern responsive Tehani storefront
- Dynamic products, categories and pack-size variants
- Shopping cart and one-page guest checkout
- Dynamic three-zone delivery charge system
- Coupon and configurable tax system
- COD and manual bKash payment with transaction ID verification
- Customer database without customer login
- Full Filament ecommerce admin dashboard
- Inventory, order, customer and sales reporting
- SEO-ready public pages
- WhatsApp product-enquiry buttons
- Admin activity log, roles and permissions

## 21. Future Enhancements

- bKash payment gateway integration
- Courier API integration
- District / thana-wise delivery pricing
- Customer account and reorder portal
- Facebook Pixel, Meta Conversions API and GA4
- Product reviews and ratings
- Abandoned-cart recovery
- Wholesale / reseller portal
- Subscription tea box
- Loyalty points and referral system
- Multi-language support

## 22. Inputs Required Before Development Starts

- Final logo, brand guideline and desired colour palette
- Product packaging files and product photographs
- Product names, pack sizes, regular price, sale price, SKU and opening stock
- Categories and product descriptions
- Company name, business address, support email and Facebook page link
- bKash number, account name, payment instructions and QR code if available
- Tax / VAT requirement and invoice details
- Delivery operation process and courier partner details
- Terms, return policy and privacy-policy approval

