// Screenshots of the documentation (docs/<lang>/bilder/*.png) in a test installation, marked with the
// NEO annotation layer. See README.md in this folder.
//
//   node capture.cjs <en|de>
//
// Environment: NSA_BASE (default http://127.0.0.1:8000), NSA_ADMIN_USER / NSA_ADMIN_PASSWORD,
// NSA_MYSQL (default "mysql contao"), NSA_AGENT (ID of the assistant, default 1), NSA_ANNOTATE (path
// to annotate.js of the plugin neo-doku).
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const path = require('path');

const lang = process.argv[2] || 'en';
const env = (name, fallback) => process.env[name] || fallback;
const base = env('NSA_BASE', 'http://127.0.0.1:8000');
const user = env('NSA_ADMIN_USER', 'admin');
const password = env('NSA_ADMIN_PASSWORD', '');
const mysql = env('NSA_MYSQL', 'mysql contao');
const agent = Number(env('NSA_AGENT', '1'));
const out = path.resolve(__dirname, '../../docs', lang, 'bilder');
const annotate = env('NSA_ANNOTATE', path.resolve(__dirname, '../../../NEO-Claude-Plugins/plugins/neo-doku/scripts/annotate.js'));
const sql = (query) => execSync(mysql + ' -N -e "' + query.replace(/"/g, '\\"') + '"').toString().trim();
const HIDE = '.sf-toolbar,.sf-minitoolbar{display:none!important}';

const context = (browser) => browser.newContext({
  viewport: { width: 1280, height: 800 },
  deviceScaleFactor: 2,
  reducedMotion: 'reduce',
  colorScheme: 'light',
  locale: lang === 'de' ? 'de-AT' : 'en-GB',
  timezoneId: 'Europe/Vienna',
});

// Marks ([selector, number, padding]) and a clip around the union of some elements (document coordinates).
// The viewport is made as tall as the page first, so that the sticky save bar of a back-end form sits at
// the end of the page and not inside the picture.
async function shoot(page, file, marks, clipSelectors, padding = 16) {
  await page.evaluate(() => document.fonts.ready);
  const height = await page.evaluate(() => document.documentElement.scrollHeight);
  await page.setViewportSize({ width: 1280, height: Math.max(800, height) });
  await page.waitForTimeout(300);
  await page.addScriptTag({ path: annotate });
  await page.evaluate((list) => {
    for (const [selector, number, pad] of list) {
      if (document.querySelector(selector)) neoAnnotate.frame(selector, { number, padding: pad == null ? 6 : pad });
    }
  }, marks);
  const clip = await page.evaluate(([selectors, p]) => {
    const boxes = selectors.map((s) => document.querySelector(s)).filter(Boolean).map((e) => e.getBoundingClientRect());
    const left = Math.max(0, Math.min(...boxes.map((r) => r.left)) - p);
    const top = Math.max(0, Math.min(...boxes.map((r) => r.top)) - p);
    const right = Math.min(document.documentElement.scrollWidth, Math.max(...boxes.map((r) => r.right)) + p);
    const bottom = Math.max(...boxes.map((r) => r.bottom)) + p;
    return { x: left + scrollX, y: top + scrollY, width: right - left, height: bottom - top };
  }, [clipSelectors, padding]);
  await page.screenshot({ path: path.join(out, file), clip });
  await page.setViewportSize({ width: 1280, height: 800 });
  console.log(path.join(out, file));
}

(async () => {
  if (!password) {
    console.error('Set NSA_ADMIN_PASSWORD.');
    process.exit(2);
  }
  // The back end and the website in the language of the images; both are restored at the end.
  const userLanguage = sql("SELECT language FROM tl_user WHERE username = '" + user + "'");
  const root = sql('SELECT p.id FROM tl_page p WHERE p.type = \'root\' ORDER BY p.sorting LIMIT 1');
  const rootLanguage = sql('SELECT language FROM tl_page WHERE id = ' + root);
  sql("UPDATE tl_user SET language = '" + lang + "' WHERE username = '" + user + "'");
  sql("UPDATE tl_page SET language = '" + lang + "' WHERE id = " + root);
  const record = sql('SELECT id FROM tl_neo_agent_language WHERE pid = ' + agent + " AND language = '" + lang + "'");
  const browser = await chromium.launch();

  try {
    // 1. The chat window with greeting and starter questions, as a visitor sees it after consent.
    const visitor = await (await context(browser)).newPage();
    await visitor.goto(base + '/', { waitUntil: 'networkidle' });
    const accept = visitor.locator('[data-ncc]:not([hidden]) [data-ncc-view="banner"] [data-ncc-action="accept"]');
    if (await accept.count()) await accept.first().click();
    await visitor.click('[data-nsa-toggle]');
    await visitor.waitForSelector('[data-nsa-suggest] .nsa-chip');
    await visitor.waitForTimeout(600);
    await shoot(visitor, 'chat-window.png', [['[data-nsa-ai]', 1], ['[data-nsa-suggest]', 2], ['[data-nsa-suggestions]', 3]], ['[data-nsa-panel]', '[data-nsa-toggle]'], 24);
    await visitor.context().close();

    const page = await (await context(browser)).newPage();
    await page.goto(base + '/contao/login');
    await page.fill('input[name="username"]', user);
    await page.fill('input[name="password"]', password);
    await Promise.all([page.waitForNavigation(), page.click('button[type="submit"]')]);

    // 2. The assistant's form, top: publishing with the consent category, identity, languages and starter questions.
    await page.goto(base + '/contao?do=neo_agent&act=edit&id=' + agent, { waitUntil: 'networkidle' });
    await page.addStyleTag({ content: HIDE });
    await shoot(page, 'assistant-form.png', [
      ['#pal_publish_legend .widget:has(select[name="consent_category"])', 1, 5],
      ['.nsa-languages__list', 2, 4],
      ['.nsa-languages__item a.tl_submit', 3, 3],
      ['.nsa-languages > p:last-child a.tl_submit', 4, 3],
    ], ['#pal_title_legend', '#pal_publish_legend', '#pal_identity_legend', '#pal_languages_legend'], 8);

    // 3. The language record: starter questions with an optional answer, questions & answers.
    await page.goto(base + '/contao?do=neo_agent&table=tl_neo_agent_language&act=edit&id=' + record, { waitUntil: 'networkidle' });
    await page.addStyleTag({ content: HIDE });
    await page.evaluate(() => document.getElementById('pal_ui_legend').scrollIntoView({ block: 'start', behavior: 'instant' }));
    await shoot(page, 'language-record.png', [
      ['#pal_ui_legend .widget:has([name^="starter_questions["])', 1, 5],
      ['#pal_ui_legend .widget:has([name^="canned["])', 2, 5],
    ], ['#pal_ui_legend'], 8);

    // 4. A new connection to an MCP server (Plus), filled in, not saved: no request leaves the installation.
    await page.goto(base + '/contao?do=neo_agent&table=tl_neo_agent_connection', { waitUntil: 'networkidle' });
    await Promise.all([page.waitForURL(/act=edit/), page.locator('.header_new').first().click()]);
    await page.waitForLoadState('networkidle');
    await page.addStyleTag({ content: HIDE });
    await page.fill('input[name="name"]', lang === 'de' ? 'Termine' : 'Appointments');
    await page.fill('input[name="url"]', 'https://mcp.example.com/mcp');
    await shoot(page, 'connection-form.png', [
      ['.widget:has(input[name="name"])', 1, 5],
      ['.widget:has(input[name="url"])', 2, 5],
      ['.widget:has([name="auth"])', 3, 5],
      ['.widget:has([name="personal_data"])', 4, 5],
    ], ['#pal_title_legend', '#pal_auth_legend', '#pal_data_legend'], 8);
    // Leave the draft: Contao removes unsaved new records itself.
    await page.goto(base + '/contao?do=neo_agent&table=tl_neo_agent_connection', { waitUntil: 'networkidle' });

    // 5. A new skill from GitHub (Plus), filled in, not saved.
    await page.goto(base + '/contao?do=neo_agent&table=tl_neo_agent_skill', { waitUntil: 'networkidle' });
    await Promise.all([page.waitForURL(/act=edit/), page.locator('.header_new').first().click()]);
    await page.waitForLoadState('networkidle');
    await page.addStyleTag({ content: HIDE });
    // GitHub is the preset source; its address field is shown with it.
    await page.fill('input[name="url"]', 'https://github.com/owner/repo/tree/main/skills/appointments');
    await shoot(page, 'skill-form.png', [
      ['.widget:has([name="source"])', 1, 5],
      ['.widget:has(input[name="url"])', 2, 5],
    ], ['#pal_source_legend', '#pal_check_legend'], 8);
    await page.goto(base + '/contao?do=neo_agent&table=tl_neo_agent_skill', { waitUntil: 'networkidle' });
  } finally {
    await browser.close();
    sql("UPDATE tl_user SET language = '" + userLanguage + "' WHERE username = '" + user + "'");
    sql("UPDATE tl_page SET language = '" + rootLanguage + "' WHERE id = " + root);
  }
})();
