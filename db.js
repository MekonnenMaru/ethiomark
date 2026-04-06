/* ═══════════════════════════════════════════════
   EthiomarkBingoDB  —  IndexedDB data layer
   Object stores:
     cards        — all bingo cards  (keyPath: id)
     game_state   — single 'current' record
     app_settings — single 'settings' record
     game_history — auto-increment log
   ═══════════════════════════════════════════════ */

const DB_NAME    = 'EthiomarkBingoDB';
const DB_VERSION = 4;
let _db = null;

/* ── open / upgrade ── */
function openDB() {
  return new Promise((resolve, reject) => {
    if (_db) { resolve(_db); return; }
    const req = indexedDB.open(DB_NAME, DB_VERSION);
    req.onupgradeneeded = e => {
      const db = e.target.result;
      if (!db.objectStoreNames.contains('cards'))
        db.createObjectStore('cards', { keyPath: 'id' });
      if (!db.objectStoreNames.contains('game_state'))
        db.createObjectStore('game_state');
      if (!db.objectStoreNames.contains('app_settings'))
        db.createObjectStore('app_settings');
      if (!db.objectStoreNames.contains('game_history'))
        db.createObjectStore('game_history', { autoIncrement: true });
      if (!db.objectStoreNames.contains('cashiers'))
        db.createObjectStore('cashiers', { keyPath: 'id' });
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
      chunk.forEach(c => st.put({ id: c[0], b: c[1], i: c[2], n: c[3], g: c[4], o: c[5] }));
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
  dbGetHistory, dbAddHistory,
  seedCashiers, getCashier
};
