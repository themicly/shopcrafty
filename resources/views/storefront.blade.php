<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('shopcrafty.store_name', config('app.name', 'Shopcrafty')) }}</title>
    <style>
        :root { color-scheme: dark; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        body { margin: 0; min-height: 100vh; background: #101827; color: #f8fafc; }
        main { display: grid; place-items: center; min-height: 100vh; padding: 2rem; }
        .shell { width: min(68rem, 100%); padding: 4rem; border: 1px solid #26344d; border-radius: 2rem; background: linear-gradient(135deg, #17233a, #111827); box-shadow: 0 2rem 5rem #05091480; }
        .eyebrow { color: #93c5fd; font-size: .8rem; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; }
        h1 { max-width: 48rem; margin: 1rem 0; font-size: clamp(2.5rem, 7vw, 5.5rem); line-height: .95; letter-spacing: -.06em; }
        p { max-width: 38rem; color: #cbd5e1; font-size: 1.15rem; line-height: 1.7; }
        .button { display: inline-block; margin-top: 1rem; padding: .85rem 1.2rem; border-radius: .75rem; background: #60a5fa; color: #0f172a; font-weight: 700; text-decoration: none; }
    </style>
</head>
<body>
<main>
    <section class="shell">
        <div class="eyebrow">{{ config('shopcrafty.store_name', 'Shopcrafty') }}</div>
        <h1>Everything your store needs, beautifully brought together.</h1>
        <p>Your Shopcrafty storefront is ready. Add products, configure your store, and start welcoming customers.</p>
        <a class="button" href="#shop">Explore the shop</a>
    </section>
</main>
</body>
</html>
