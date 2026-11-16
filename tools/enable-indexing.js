const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const design = path.join(root, 'design');

const files = [];
for (const d of [design, path.join(design, 'posts')]) {
  if (fs.existsSync(d)) {
    for (const f of fs.readdirSync(d)) {
      if (f.endsWith('.html') && !f.startsWith('attend-')) {
        files.push(path.join(d, f));
      }
    }
  }
}

let c = 0;
for (const f of files) {
  let content = fs.readFileSync(f, 'utf8');
  if (content.includes('noindex')) {
    content = content.replace(/<meta name=['"]robots['"] content=['"]noindex, nofollow['"]\s*\/?>\s*/gi, '');
    fs.writeFileSync(f, content, 'utf8');
    c++;
  }
}

console.log('Removed noindex from', c, 'files');
