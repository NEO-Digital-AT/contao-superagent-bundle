// The chat's Markdown renderer (public/neo-agent.js) in a browser: tables, emphasis, lists, the button of a
// link alone in its line, and no markup or script from the model's text. Run: node tests/e2e/markdown.e2e.cjs
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const source = fs.readFileSync(path.join(__dirname, '../../public/neo-agent.js'), 'utf8');
const start = source.indexOf('  var MD_ESCAPABLE');
const end = source.indexOf('  function init(root)');
const renderer = source.slice(start, end);

const reply = [
  'Ihre Buchung ist **bestätigt**.',
  '',
  '| Zimmer | Nächte | Preis |',
  '|---|:---:|---:|',
  '| Doppelzimmer | 2 | 240 EUR |',
  '',
  '- Frühstück inklusive',
  '- Anreise ab 15 Uhr',
  '',
  '[Jetzt bezahlen](https://pay.example.com/abc)',
  '',
  'Bild: <img src=x onerror="window.hacked=1"> und [Klick](javascript:alert(1)) und <script>window.hacked=2</script>',
].join('\n');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();
  await page.setContent('<div id="out"></div>');
  const result = await page.evaluate(({ code, text }) => {
    const fn = new Function('text', 'el', code + '\n renderMarkdown(el, text, 0);');
    const el = document.getElementById('out');
    fn(text, el);
    return {
      strong: el.querySelector('strong')?.textContent,
      th: [...el.querySelectorAll('th')].map((n) => n.textContent),
      td: [...el.querySelectorAll('td')].map((n) => n.textContent),
      align: el.querySelector('td.nsa-md-right')?.textContent,
      items: el.querySelectorAll('li').length,
      cta: el.querySelector('a.nsa-cta')?.getAttribute('href'),
      ctaText: el.querySelector('a.nsa-cta')?.textContent,
      img: el.querySelectorAll('img').length,
      script: el.querySelectorAll('script').length,
      jsLinks: [...el.querySelectorAll('a')].filter((a) => /^javascript:/i.test(a.getAttribute('href') || '')).length,
      hacked: window.hacked || 0,
    };
  }, { code: renderer, text: reply });
  await browser.close();

  const checks = {
    'bold': result.strong === 'bestätigt',
    'table head': result.th.join('|') === 'Zimmer|Nächte|Preis',
    'table row': result.td.join('|') === 'Doppelzimmer|2|240 EUR',
    'right aligned column': result.align === '240 EUR',
    'list': result.items === 2,
    'payment button': result.cta === 'https://pay.example.com/abc' && result.ctaText === 'Jetzt bezahlen',
    'no image from text': result.img === 0,
    'no script from text': result.script === 0,
    'no javascript: link': result.jsLinks === 0,
    'nothing executed': result.hacked === 0,
  };
  let failed = 0;
  for (const [name, ok] of Object.entries(checks)) {
    console.log((ok ? 'OK   ' : 'FAIL ') + name);
    failed += ok ? 0 : 1;
  }
  process.exit(failed ? 1 : 0);
})();
