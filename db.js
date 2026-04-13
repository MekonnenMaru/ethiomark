/* ═══════════════════════════════════════════════
   EthiomarkBingoDB  —  IndexedDB data layer
   Object stores:
     cards            — all bingo cards  (keyPath: id)
     game_state       — single 'current' record
     app_settings     — single 'settings' record
     game_history     — auto-increment log
     cashiers         — cashier accounts  (keyPath: id)
     license          — machine ID + packages + balance
     transaction      — game revenue log (auto-increment)
     wallettransaction— wallet deposit/withdraw log (auto-increment)
   ═══════════════════════════════════════════════ */

const DB_NAME    = 'EthiomarkBingoDB';
const DB_VERSION = 7;
let _db = null;

/* ── open / upgrade ── */
function openDB() {
  return new Promise((resolve, reject) => {
    if (_db) { resolve(_db); return; }
    const req = indexedDB.open(DB_NAME, DB_VERSION);
    req.onupgradeneeded = e => {
      const db         = e.target.result;
      const oldVersion = e.oldVersion;

      /* v7 — recreate cards store with category index (forces re-seed) */
      if (oldVersion < 7) {
        if (db.objectStoreNames.contains('cards')) db.deleteObjectStore('cards');
        const cs = db.createObjectStore('cards', { keyPath: 'id' });
        cs.createIndex('by_category', 'category', { unique: false });
      } else if (!db.objectStoreNames.contains('cards')) {
        const cs = db.createObjectStore('cards', { keyPath: 'id' });
        cs.createIndex('by_category', 'category', { unique: false });
      }

      /* All other stores are idempotent */
      if (!db.objectStoreNames.contains('game_state'))
        db.createObjectStore('game_state');
      if (!db.objectStoreNames.contains('app_settings'))
        db.createObjectStore('app_settings');
      if (!db.objectStoreNames.contains('game_history'))
        db.createObjectStore('game_history', { autoIncrement: true });
      if (!db.objectStoreNames.contains('cashiers'))
        db.createObjectStore('cashiers', { keyPath: 'id' });
      if (!db.objectStoreNames.contains('license'))
        db.createObjectStore('license');
      if (!db.objectStoreNames.contains('transaction'))
        db.createObjectStore('transaction', { autoIncrement: true });
      if (!db.objectStoreNames.contains('wallettransaction'))
        db.createObjectStore('wallettransaction', { autoIncrement: true });
    };
    req.onsuccess  = e => { _db = e.target.result; resolve(_db); };
    req.onerror    = e => reject(e.target.error);
  });
}

/* ── seeding ── */
async function _isSeeded() {
  const db = await openDB();
  return new Promise(resolve => {
    const req = db.transaction('cards','readonly').objectStore('cards').count();
    req.onsuccess = () => resolve(req.result > 0);
    req.onerror   = () => resolve(false);
  });
}

async function seedCards(progressCb) {
  if (await _isSeeded()) { if (progressCb) progressCb(100); return; }
  const src = (typeof BINGO_CARDS !== 'undefined') ? BINGO_CARDS : [];
  if (!src.length) return;
  const db   = await openDB();
  const CHUNK = 250;
  for (let i = 0; i < src.length; i += CHUNK) {
    const chunk = src.slice(i, i + CHUNK);
    await new Promise((res, rej) => {
      const tx = db.transaction('cards', 'readwrite');
      const st = tx.objectStore('cards');
      chunk.forEach(c => st.put({ id: c[0], b: c[1], i: c[2], n: c[3], g: c[4], o: c[5], category: c[6] || '' }));
      tx.oncomplete = res;
      tx.onerror    = e => rej(e.target.error);
    });
    if (progressCb) progressCb(Math.min(99, Math.round((i + CHUNK) / src.length * 100)));
  }
  if (progressCb) progressCb(100);
}

/* ── card queries ── */
async function getCard(id) {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const req = db.transaction('cards','readonly').objectStore('cards').get(Number(id));
    req.onsuccess = () => {
      const r = req.result;
      resolve(r ? [r.id, r.b, r.i, r.n, r.g, r.o] : null);
    };
    req.onerror = e => reject(e.target.error);
  });
}

async function getCardsBatch(ids) {
  const db = await openDB();
  const out = {};
  await Promise.all(ids.map(id => new Promise(resolve => {
    const req = db.transaction('cards','readonly').objectStore('cards').get(Number(id));
    req.onsuccess = () => {
      const r = req.result;
      if (r) out[id] = [r.id, r.b, r.i, r.n, r.g, r.o];
      resolve();
    };
    req.onerror = resolve;
  })));
  return out;
}

async function getAllCardIds() {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const req = db.transaction('cards','readonly').objectStore('cards').getAllKeys();
    req.onsuccess = () => resolve(req.result.sort((a,b)=>a-b));
    req.onerror   = e => reject(e.target.error);
  });
}

/* ── game state ── */
async function dbGetGameState() {
  const db = await openDB();
  return new Promise(resolve => {
    const req = db.transaction('game_state','readonly').objectStore('game_state').get('current');
    req.onsuccess = () => resolve(req.result || {});
    req.onerror   = () => resolve({});
  });
}

async function dbSaveGameState(state) {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx = db.transaction('game_state','readwrite');
    tx.objectStore('game_state').put(state, 'current');
    tx.oncomplete = resolve;
    tx.onerror    = e => reject(e.target.error);
  });
}

async function dbClearGameState() {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx = db.transaction('game_state','readwrite');
    tx.objectStore('game_state').delete('current');
    tx.oncomplete = resolve;
    tx.onerror    = e => reject(e.target.error);
  });
}

/* ── app settings ── */
async function dbGetAppSettings() {
  const db = await openDB();
  return new Promise(resolve => {
    const req = db.transaction('app_settings','readonly').objectStore('app_settings').get('settings');
    req.onsuccess = () => resolve(req.result || {});
    req.onerror   = () => resolve({});
  });
}

async function dbSaveAppSettings(settings) {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx = db.transaction('app_settings','readwrite');
    tx.objectStore('app_settings').put(settings, 'settings');
    tx.oncomplete = resolve;
    tx.onerror    = e => reject(e.target.error);
  });
}

/* ── game history ── */
async function dbGetHistory() {
  const db = await openDB();
  return new Promise(resolve => {
    const req = db.transaction('game_history','readonly').objectStore('game_history').getAll();
    req.onsuccess = () => resolve(req.result || []);
    req.onerror   = () => resolve([]);
  });
}

async function dbAddHistory(entry) {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx = db.transaction('game_history','readwrite');
    tx.objectStore('game_history').add(entry);
    tx.oncomplete = resolve;
    tx.onerror    = e => reject(e.target.error);
  });
}

/* Find the most-recent record for `round` with status === fromStatus
   and merge `updates` into it. Returns true if a record was found. */
async function dbUpdateHistoryByRound(round, fromStatus, updates) {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx    = db.transaction('game_history', 'readwrite');
    const store = tx.objectStore('game_history');
    /* open cursor newest-first (prev) so we hit the most recent match */
    const req = store.openCursor(null, 'prev');
    let found = false;
    req.onsuccess = e => {
      const cursor = e.target.result;
      if (!cursor) { resolve(found); return; }
      if (!found && cursor.value.round == round && cursor.value.status === fromStatus) {
        found = true;
        cursor.update({ ...cursor.value, ...updates });
        resolve(true);
        return; /* don't continue — we only update the newest match */
      }
      cursor.continue();
    };
    req.onerror = e => reject(e.target.error);
  });
}

/* ── game transaction log ── */
async function dbAddTransaction(entry) {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx = db.transaction('transaction', 'readwrite');
    tx.objectStore('transaction').add({ ...entry, date: entry.date || new Date().toISOString() });
    tx.oncomplete = resolve;
    tx.onerror    = e => reject(e.target.error);
  });
}

async function dbGetTransactions() {
  const db = await openDB();
  return new Promise(resolve => {
    const req = db.transaction('transaction','readonly').objectStore('transaction').getAll();
    req.onsuccess = () => resolve(req.result || []);
    req.onerror   = () => resolve([]);
  });
}

/* ── wallet transaction log ── */
async function dbAddWalletTransaction(entry) {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx = db.transaction('wallettransaction', 'readwrite');
    tx.objectStore('wallettransaction').add({ ...entry, timestamp: entry.timestamp || new Date().toISOString() });
    tx.oncomplete = resolve;
    tx.onerror    = e => reject(e.target.error);
  });
}

async function dbGetWalletTransactions() {
  const db = await openDB();
  return new Promise(resolve => {
    const req = db.transaction('wallettransaction','readonly').objectStore('wallettransaction').getAll();
    req.onsuccess = () => resolve(req.result || []);
    req.onerror   = () => resolve([]);
  });
}

/* ── license ── */
async function dbGetLicense() {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const req = db.transaction('license','readonly').objectStore('license').get('data');
    req.onsuccess = () => resolve(req.result || null);
    req.onerror   = e => reject(e.target.error);
  });
}

async function dbSaveLicense(data) {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx = db.transaction('license','readwrite');
    tx.objectStore('license').put(data, 'data');
    tx.oncomplete = resolve;
    tx.onerror    = e => reject(e.target.error);
  });
}

async function dbDeleteLicense() {
  const db = await openDB();

  return new Promise((resolve, reject) => {
    const tx = db.transaction('license', 'readwrite');
    const store = tx.objectStore('license');

    const req = store.delete('data'); // ✅ correct key delete

    req.onsuccess = () => resolve(true);
    req.onerror = (e) => reject(e.target.error);
  });
}

/* ── card categories ── */

/* Returns a sorted array of all distinct non-empty category names */
async function dbGetCardCategories() {
  const db = await openDB();
  return new Promise(resolve => {
    const cats = new Set();
    const req  = db.transaction('cards','readonly').objectStore('cards').openCursor();
    req.onsuccess = e => {
      const c = e.target.result;
      if (c) { if (c.value.category) cats.add(c.value.category); c.continue(); }
      else resolve([...cats].sort());
    };
    req.onerror = () => resolve([]);
  });
}

/* Returns sorted card IDs belonging to a given category.
   Pass '' or null to return ALL card IDs. */
async function dbGetCardIdsByCategory(cat) {
  const db = await openDB();
  if (!cat) return getAllCardIds();
  return new Promise(resolve => {
    const ids = [];
    const st  = db.transaction('cards','readonly').objectStore('cards');
    let req;
    try {
      req = st.index('by_category').openCursor(IDBKeyRange.only(cat));
    } catch(_) {
      req = st.openCursor();   /* fallback: full scan if index missing */
    }
    req.onsuccess = e => {
      const c = e.target.result;
      if (c) {
        if (!cat || c.value.category === cat) ids.push(c.value.id);
        c.continue();
      } else resolve(ids.sort((a, b) => a - b));
    };
    req.onerror = () => resolve([]);
  });
}

/* ── cashier helpers ── */

/* Merge `updates` into the cashier record for `id` */
async function dbUpdateCashier(id, updates) {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx  = db.transaction('cashiers', 'readwrite');
    const st  = tx.objectStore('cashiers');
    const req = st.get(id);
    req.onsuccess = () => {
      const existing = req.result;
      if (!existing) { resolve(false); return; }
      st.put(Object.assign({}, existing, updates));
      resolve(true);
    };
    req.onerror  = e => reject(e.target.error);
    tx.onerror   = e => reject(e.target.error);
  });
}

/* ── cashiers ── */
async function seedCashiers(list) {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx  = db.transaction('cashiers', 'readwrite');
    const st  = tx.objectStore('cashiers');
    list.forEach(c => { st.put(c); });
    tx.oncomplete = resolve;
    tx.onerror    = e => reject(e.target.error);
  });
}

async function getCashier(id) {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const req = db.transaction('cashiers','readonly').objectStore('cashiers').get(id);
    req.onsuccess = () => resolve(req.result || null);
    req.onerror   = e => reject(e.target.error);
  });
}

/* ── export namespace ── */
window.EthiomarkDB = {
  openDB, seedCards,
  getCard, getCardsBatch, getAllCardIds,
  dbGetGameState, dbSaveGameState, dbClearGameState,
  dbGetAppSettings, dbSaveAppSettings,
  dbGetHistory, dbAddHistory, dbUpdateHistoryByRound,
  dbAddTransaction, dbGetTransactions,
  dbAddWalletTransaction, dbGetWalletTransactions,
  seedCashiers, getCashier, dbUpdateCashier,
  dbGetCardCategories, dbGetCardIdsByCategory,
  dbGetLicense, dbSaveLicense, dbDeleteLicense
};
