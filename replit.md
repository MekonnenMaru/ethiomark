# Ethiomark Bingo

A fully client-side Bingo management and gaming system for the Ethiopian market, featuring Amharic UI. No PHP/MariaDB backend required — all data lives in IndexedDB, and all 4,470 bingo card records are bundled into the JavaScript app.

## Architecture

- **Frontend**: Single `index.html` with embedded CSS + JavaScript (no framework)
- **Storage**: IndexedDB (via browser native API) for settings, game state, and audio cache
- **Card data**: `cards_data.js` — 4,470 bingo cards (cards 1–628 across categories) in compact format
- **Audio**: Fetched from `/assets/sound/` voice directories and cached in IndexedDB
- **PWA**: `service-worker.js` caches static assets for offline play; `manifest.json` for install

## Key Files

- `index.html` — The entire app (new game setup, game board, bingo grid, win checker, settings)
- `cards_data.js` — 386KB bundled card data: `[cardNumber, [b1..5], [i1..5], [n1..5], [g1..5], [o1..5]]`
- `start.sh` — Starts a simple PHP file server (no DB, no PHP logic used)
- `service-worker.js` — PWA service worker caching static assets
- `bootstrap/css/` — Existing CSS assets (style.css, bingon.css, ball.css, modal.css, etc.)
- `bootstrap/js/confetti.browser.min.js` — Confetti animation library
- `cashier/right-menu.css` — Settings menu CSS

## Game Flow

1. **New Game Panel**: Enter price (ዋጋ), pattern (1–12), card numbers (range like 1-50), category, then click ▶ ጀምር
2. **Game Board**: 75-number B/I/N/G/O grid, current ball display, recent calls, payout (derash) box
3. **Drawing**: Click START to auto-draw at set speed; STOP to pause; Shuffle to reset
4. **Win Checking**: Manual — type card number + Check; Auto — checks all registered cards each draw
5. **Settings**: Bonus, profit %, color mode, sound toggles, voice selection — persisted in IndexedDB
6. **Finish**: Click ⏹ Finish to end game and reset

## Win Logic (from original PHP)

- Rows: 5/5 matches (middle row: 4/5 since N[2]=0 is free space)
- Columns: 5/5 (N column: 4/5 for same reason)
- Diagonals: 4/4 matches (center is free space)
- Corners: 4 outer corners all drawn
- Center corners: I[1], G[1], I[3], G[3]
- Meskel: I[2], N[1], N[3], G[2]
- `count_winning_line >= pattern` = BINGO

## Removed Features (per project spec)

- PHP backend, MariaDB, all AJAX server calls
- Supportive cashier / remote admin sync
- Machine-ID binding / login authentication
- Commission calculations tied to server partner settings
