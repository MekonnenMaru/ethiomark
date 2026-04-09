# Ethiomark Bingo

A fully client-side Bingo management and gaming system for the Ethiopian market, featuring Amharic UI. No PHP/MariaDB backend required — all data lives in IndexedDB, and all 4,470 bingo card records are bundled into the JavaScript app.

## Architecture

- **Frontend**: Single `index.html` with embedded CSS + JavaScript (no framework)
- **API layer**: `api.js` — the only interface the frontend talks to. Wraps all data operations behind `window.API`. Swap to a REST backend (PHP/Node.js) by replacing only this file; zero HTML changes required.
- **Storage driver**: `db.js` — IndexedDB implementation backing `api.js`. Stores: cards, game_state, app_settings, game_history, cashiers, license (DB_VERSION=5).
- **Card data**: `cards_data.js` — 4,470 bingo cards (cards 1–628 across categories) in compact format
- **Audio**: Fetched from `/assets/sound/` voice directories and cached in IndexedDB
- **PWA**: `service-worker.js` caches static assets for offline play; `manifest.json` for install

## File Structure

```
ethiomark/
  index.html              ← main game page
  login.html              ← cashier login
  reg_new_game.html       ← register cards for a round
  report.html             ← history & balance report
  keygen.html             ← admin license key generator
  api.js                  ← IDB driver (default backend)
  db.js                   ← IndexedDB implementation
  cards_data.js           ← 4,470 bundled bingo cards
  service-worker.js       ← PWA offline cache
  backend/
    api.php               ← PHP/MySQL backend handler
    api_php.js            ← PHP JS driver (drop-in for api.js)
    ethiomark_bingo.sql   ← MySQL schema (import into phpMyAdmin)
  bootstrap/css/          ← themes.css, app.css, base.css
  assets/sound/           ← voice audio files
```

### Backend switching

Two complete API drivers with identical `window.API` surfaces:

| File | Backend | When to use |
|---|---|---|
| `api.js` | IndexedDB (browser) | Default — no server, works offline |
| `backend/api_php.js` | MySQL via `backend/api.php` | XAMPP / multi-device |

**To switch to PHP/MySQL:** in each HTML file replace two lines with one:
```html
<!-- REMOVE these two lines: -->
<script src="db.js"></script>
<script src="api.js"></script>

<!-- ADD this one line instead: -->
<script src="backend/api_php.js"></script>
```
Files to update: `index.html`, `login.html`, `reg_new_game.html`, `report.html`
(`keygen.html` has its own inline HMAC — no api.js needed there)

**`backend/api.php` setup (XAMPP):**
1. Start Apache + MySQL in XAMPP Control Panel
2. Copy all project files to `C:\xampp\htdocs\ethiomark\`
3. Open `http://localhost/ethiomark/` — tables auto-created on first load
4. OR: import `backend/ethiomark_bingo.sql` in phpMyAdmin manually
5. Edit `DB_USER`/`DB_PASS` at top of `backend/api.php` if needed (defaults: root / empty)

Cards (4,470 records) remain bundled in `cards_data.js` in both modes — never stored in MySQL.
Session stays in `localStorage.em_cashier_id` in both modes.
HMAC key validation runs client-side in both modes — `keygen.html` works unchanged.
Login (`seedCashiers`) auto-inserts default cashier accounts into MySQL on first login page load.

### API layer (`api.js`) surface

| Group | Methods |
|---|---|
| Init | `init()`, `seedCards(onProgress)` |
| Cards | `getCard(n)`, `getCardsBatch(ns)`, `getAllCardIds()` |
| Game State | `getGameState()`, `saveGameState(s)`, `clearGameState()` |
| Settings | `getSettings()`, `saveSettings(s)` |
| History | `getHistory()`, `addHistory(entry)` |
| Auth | `seedCashiers(list)`, `getCashier(id)`, `verifyCredentials(id, hash)` |
| Session | `getSession()`, `setSession(id)`, `clearSession()` |
| License | `getMachineId()`, `getBalance()`, `isLicensed()`, `activatePackage(key)`, `addRevenue(amt)`, `generateLicenseKey(mid, sn, amt)` |

## License / Package System

- Machine ID: 8-char uppercase hex derived from `crypto.randomUUID()` via SHA-256. Generated once and stored permanently in IndexedDB `license` store. Binds keys to a specific machine.
- Key format: `EM-{base64(JSON)}` where JSON = `{mid, sn, amt, iat, sig}`. `sig` = HMAC-SHA256(`mid|sn|amt|iat`, secret key).
- Balance: `available = total_deposited − total_used`. App blocks game start when `available ≤ 0` and shows the package overlay.
- `keygen.html` — Admin-only key generator tool. Password-gated (admin password: `EthioAdmin@2025`). Generates and logs signed single-use keys. Kept confidential and NOT shipped to cashiers.
- Warning banner shows when balance is below 15% of deposited amount.

## Key Files

- `index.html` — Main game app (board, bingo grid, win checker, settings, bonus image, package overlay)
- `reg_new_game.html` — Visual card registration: clickable numbered ball grid (1–628), search, range-select, pattern/price/voice/speed configuration
- `report.html` — Dashboard: sidebar nav, summary stats, date-range game history table, CSV export
- `cards_data.js` — 386KB bundled card data (`BINGO_CARDS` variable): `[cardNumber, [b1..5], [i1..5], [n1..5], [g1..5], [o1..5]]`
- `assets/images/bonus-removebg.png` — Bonus mode image shown in main modal when bonus is ON
- `start.sh` — Starts a simple PHP file server (no DB, no PHP logic used)
- `service-worker.js` — PWA service worker caching static assets
- `bootstrap/css/base.css` — All CSS custom properties (design tokens). Edit colors here only.
- `bootstrap/css/app.css` — All component and layout rules for the game board (merged from the original 11 CSS files)
- `bootstrap/css/themes.css` — Theme variable overrides per `[data-theme]` attribute
- `bootstrap/js/confetti.browser.min.js` — Confetti animation library

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

## CSS Architecture

- **`bootstrap/css/base.css`** — Design tokens (CSS variables: `--bg`, `--accent`, `--border`, etc.), grid system, reset
- **`bootstrap/css/app.css`** — All components across 19 sections:
  1. Reset/base 2. Typography 3. Grid 4. Side nav 5. Header/buttons 6. Alerts 7. Ball animation 8. Game board 9. Controls 10. Modals 11. Win animation 12. Confetti 13. Package overlay 14. Settings panel 15. Bonus system 16. Register card modal 17. Data table 18. Scrollbars **19. Responsive system (global breakpoints)**
- **`bootstrap/css/themes.css`** — Theme variable overrides (6 themes)
- **`index.html` inline `<style>`** — Page-specific component styles: `.bingo-button`, `.info-bar`, `.setting_box` + responsive breakpoints at 1024 / 768 / 480 / 360px

### Responsive Breakpoints

| Breakpoint | Layout changes |
|---|---|
| ≤ 1024px (tablet) | Setting box wraps, modal narrows |
| ≤ 768px (mobile) | Info bar stacks vertically; buttons wrap to 2 rows; voice/card inputs each take 50%; CHECK button goes full-width |
| ≤ 480px (small phone) | numbox/txtbox shrink, buttons compact to 46px, nav buttons go 2-column |
| ≤ 360px (extra small) | All heights reduced to 40–42px |

Fluid sizing via `clamp()` is used for: ball (`.ball2`), called number (`.num`), B/I/N/G/O column buttons (`.bingo-button`), info-bar heights and widths, `.numbox`/`.txtbox` font sizes, `.voiceselect`, callb gap/padding.

### Bootstrap Modal Infrastructure (fully local)

`.fade`, `.modal.show`, `.modal-backdrop`, `.modal-open`, `.modal-dialog-centered` — all defined in `app.css` section 10. No CDN required.

## Theme System

- **File**: `bootstrap/css/themes.css` — loaded after `modern.css` on all pages
- **Storage**: `localStorage.em_theme` — values: `dark` | `light` | `emerald` | `purple` | `fire` | `gold`
- **Apply**: Sets `data-theme` attribute on `<html>` element before page render (inline script in `<head>`)
- **Scope**: Propagates automatically to `index.html`, `login.html`, `reg_new_game.html`, `report.html`, `keygen.html`
- **Picker UI (index.html)**: 6 color swatches in the Settings panel; 🎨 button in top-right cycles through themes
- **Light theme**: Handled purely via `themes.css` CSS variable overrides — no file swapping. The old `light/` directory has been eliminated.
- **Themes**: Dark (navy/cyan), Light (gray/white), Emerald (forest green), Purple (galaxy), Fire (crimson/orange), Gold (dark gold)

## Authentication / Login System

- `login.html` — Glassmorphism dark login page. Cashier ID + password (MD5 hashed). Inline MD5 JS function (RFC 1321). On success, stores cashier ID in `localStorage.em_cashier_id`.
- Session guard: All three pages (index.html, reg_new_game.html, report.html) redirect to `/login.html` if `em_cashier_id` is absent.
- Logout clears `em_cashier_id` and redirects to `/login.html`.
- Cashier seed accounts stored in IndexedDB `cashiers` store: `{ id, password_hash, settings_json }`.
- Default account (from SQL dump): `@temp1` / hash `a01610228fe998f515a72dd730294d87`.

## IndexedDB Schema (DB_VERSION = 5)

Stores: `cards`, `game_state`, `app_settings`, `game_history`, `cashiers`, `license`

`license` store keys: `machine_id` (8-char hex), `serial_*` (per-key used status), `balance_deposited`, `balance_used`

## Startup Modal (index.html)

The modal shown on game start displays a **2×2 stats grid**: Pattern, Cards, Price/Card, Revenue — no card number bubble list. When no cards are registered, the media area (bonus/logo image) is shown instead. Revenue is auto-calculated as cards × price.

## Removed Features (per project spec)

- PHP backend, MariaDB, all AJAX server calls
- Supportive cashier / remote admin sync
- Machine-ID binding / login authentication
- Commission calculations tied to server partner settings
