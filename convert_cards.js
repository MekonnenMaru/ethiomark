const fs   = require('fs');
const data = JSON.parse(fs.readFileSync('./attached_assets/cartela_2026-04-10_07-19-41_1775805617420.json', 'utf8'));
const rows = data.find(x => x.type === 'table').data;

const parse = s => s.split(',').map(Number);

const lines = rows.map(r => {
  const id  = Number(r.cartela_number);
  const b   = parse(r.b);
  const i   = parse(r.i);
  const n   = parse(r.n);
  const g   = parse(r.g);
  const o   = parse(r.o);
  const cat = (r.category || '').trim();
  return `  [${id},[${b}],[${i}],[${n}],[${g}],[${o}],"${cat}"]`;
});

const cats = [...new Set(rows.map(r => r.category).filter(Boolean))];
const js = `/* Auto-generated from cartela JSON export\n   ${rows.length} cards | ${cats.length} categories: ${cats.join(', ')}\n   Replace this file to update card data. */\nconst BINGO_CARDS = [\n${lines.join(',\n')}\n];\n`;

fs.writeFileSync('./cards_data.js', js, 'utf8');
console.log('Done: ' + rows.length + ' cards written');
console.log('Categories:', cats);
