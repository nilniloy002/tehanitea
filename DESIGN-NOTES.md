# Tehani storefront refresh

The storefront is a local Laravel / Blade / Alpine implementation. Tailwind tokens and the production stylesheet are built with `npm run build`.

## Assets

- The supplied `logo.png` is a white transparent logo; CSS supplies contrast on pale backgrounds.
- The supplied `video.mp4` is used as the hero video.
- `public/media/tea-moments.webp` is an illustrative lifestyle image generated with the built-in image generation tool, then encoded to WebP for delivery. Original: `public/media/tea-moments.png`.
- Generation prompt: “Warm quiet Bangladeshi home tea table by a softly sunlit window: clear glass cup of amber black tea, cream ceramic teapot, a second tea cup, natural linen cloth, fresh green leaves and a small bowl of dry tea. Premium editorial photography, warm cream, muted forest green and amber. No people, text, logo, packaging or watermark.”
- Real packaging photographs still need to be uploaded in Products → Product photography. Current product images are brand placeholders, not product packaging claims.

## Operating the store

- Set verified pack prices and stock in Products → Pack sizes & pricing; then enable available packs.
- Add Green Tea's actual variants before accepting orders.
- Store settings supports announcement, contacts, COD/bKash switches, payment instructions, and approved policies.
- Add `bkash_number`, `bkash_account_name`, `bkash_instructions`, then set `bkash_active` to `1` only when verified.
- Orders includes item snapshots, timeline, operational status actions and bKash verification for the existing local administrator. No new staff roles were applied.
- Product/variant deletion preserves historical order item snapshots. Stock adjustments made in the editor are logged.

## Local development

`php artisan migrate` applies additive database changes. Do not use `migrate:fresh` on store data.
`php artisan storage:link` enables public gallery uploads.
`php artisan filament:assets` publishes the admin assets.
`npm install` and `npm run build` build storefront assets.
`php artisan serve --port=8002` serves the project.
`php artisan test` uses an isolated in-memory SQLite database; MySQL remains the local application database.

Commercial prices, stock, packaging photography, approved legal copy and bKash details remain business inputs. No fabricated product prices or reviews were published.
