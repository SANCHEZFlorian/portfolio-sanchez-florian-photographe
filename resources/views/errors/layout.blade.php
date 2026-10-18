<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') — @yield('title') | Florian Sanchez Photographie</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep: #060911;
            --primary: #6366f1;
            --primary-light: #818cf8;
            --accent: #a855f7;
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            min-height: 100vh;
            background-color: var(--bg-deep);
            background-image:
                radial-gradient(circle at 20% 25%, rgba(41, 62, 204, 0.22) 0%, transparent 45%),
                radial-gradient(circle at 80% 75%, rgba(157, 78, 221, 0.18) 0%, transparent 45%);
            color: var(--text-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow-x: hidden;
        }
        .err-nav {
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            background: rgba(6, 9, 17, 0.75);
            backdrop-filter: blur(12px);
        }
        .err-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #fff;
            font-family: 'Playfair Display', serif;
        }
        .err-logo-mark {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #293ecc, #7c3aed);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.04em;
        }
        .err-logo-text {
            font-size: 1.2rem;
            font-weight: 500;
            letter-spacing: 0.02em;
        }
        .err-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.25rem;
        }
        .vf-card {
            position: relative;
            width: 100%;
            max-width: 720px;
            padding: 2.5rem 2.75rem;
            background: rgba(10, 14, 24, 0.78);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.65), inset 0 1px 0 rgba(255, 255, 255, 0.06);
            text-align: center;
        }
        .vf-corner {
            position: absolute;
            width: 24px;
            height: 24px;
            border-color: rgba(129, 140, 248, 0.45);
            border-style: solid;
            pointer-events: none;
        }
        .vf-tl { top: 16px; left: 16px; border-width: 2px 0 0 2px; border-top-left-radius: 4px; }
        .vf-tr { top: 16px; right: 16px; border-width: 2px 2px 0 0; border-top-right-radius: 4px; }
        .vf-bl { bottom: 16px; left: 16px; border-width: 0 0 2px 2px; border-bottom-left-radius: 4px; }
        .vf-br { bottom: 16px; right: 16px; border-width: 0 2px 2px 0; border-bottom-right-radius: 4px; }
        .hud-top, .hud-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.72rem;
            letter-spacing: 0.12em;
            color: rgba(148, 163, 184, 0.7);
        }
        .hud-top {
            padding-bottom: 0.85rem;
            margin-bottom: 1.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        .hud-bottom {
            padding-top: 0.85rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }
        .rec-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ef4444;
            box-shadow: 0 0 10px #ef4444;
            margin-right: 6px;
        }
        .err-code {
            font-family: 'Playfair Display', serif;
            font-size: clamp(4.2rem, 9vw, 6.2rem);
            font-weight: 600;
            line-height: 1;
            background: linear-gradient(135deg, #ffffff 0%, #c7d2fe 50%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }
        .err-subtitle {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.24em;
            color: var(--primary-light);
            margin-bottom: 0.5rem;
        }
        .err-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.7rem, 3.5vw, 2.3rem);
            font-weight: 500;
            color: #f8fafc;
            margin-bottom: 1rem;
        }
        .err-desc {
            max-width: 500px;
            margin: 0 auto 2rem;
            font-size: 1rem;
            line-height: 1.7;
            color: var(--text-secondary);
        }
        .err-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0.78rem 1.65rem;
            border-radius: 9999px;
            font-size: 0.92rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s ease;
            cursor: pointer;
            border: 1px solid transparent;
        }
        .btn-primary {
            background: linear-gradient(135deg, #293ecc 0%, #5b46e5 52%, #7c3aed 100%);
            color: #ffffff;
            border-color: rgba(165, 180, 252, 0.35);
            box-shadow: 0 10px 26px rgba(59, 73, 223, 0.35);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 32px rgba(99, 102, 241, 0.5);
        }
        .btn-secondary {
            background: rgba(15, 23, 42, 0.8);
            color: #e2e8f0;
            border-color: rgba(255, 255, 255, 0.14);
        }
        .btn-secondary:hover {
            border-color: rgba(129, 140, 248, 0.5);
            color: #fff;
        }
        .err-footer {
            text-align: center;
            padding: 1.5rem;
            font-size: 0.82rem;
            color: var(--text-muted);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body>
    <nav class="err-nav">
        <a href="/" class="err-logo">
            <span class="err-logo-mark">FS</span>
            <span class="err-logo-text">Florian Sanchez</span>
        </a>
    </nav>

    <main class="err-main">
        <div class="vf-card">
            <span class="vf-corner vf-tl"></span>
            <span class="vf-corner vf-tr"></span>
            <span class="vf-corner vf-bl"></span>
            <span class="vf-corner vf-br"></span>

            <div class="hud-top">
                <span><span class="rec-dot"></span>@yield('hud', 'STUDIO_ALERT // RAW')</span>
                <span>-2 · -1 · 0 · +1 · +2</span>
            </div>

            <div class="err-code">@yield('code')</div>
            <div class="err-subtitle">@yield('subtitle')</div>
            <h1 class="err-title">@yield('title')</h1>
            <p class="err-desc">@yield('message')</p>

            <div class="err-actions">
                <a href="/" class="btn btn-primary">Retour à l'accueil</a>
                <a href="javascript:window.location.reload()" class="btn btn-secondary">Rafraîchir la page</a>
            </div>

            <div class="hud-bottom">
                <span>FLORIAN SANCHEZ PHOTOGRAPHIE</span>
                <span>[ AF-C • @yield('code') ]</span>
                <span>RAW • 3:2</span>
            </div>
        </div>
    </main>

    <footer class="err-footer">
        &copy; {{ date('Y') }} Florian Sanchez — Photographe
    </footer>
</body>
</html>
