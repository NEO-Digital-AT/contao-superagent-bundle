// Screenshots of the documentation (docs/<lang>/bilder/*.png) in a test installation, marked with the
// NEO annotation layer (frames and numbers). See README.md in this folder.
//
//   node capture.cjs <en|de> [image,image,…]
//
// Without a list of images all are taken. Environment: see README.md.
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const path = require('path');

const lang = process.argv[2] || 'en';
const only = (process.argv[3] || '').split(',').filter(Boolean);
const env = (name, fallback) => process.env[name] || fallback;
const base = env('NSA_BASE', 'http://127.0.0.1:8000');
const user = env('NSA_ADMIN_USER', 'admin');
const password = env('NSA_ADMIN_PASSWORD', '');
const mysql = env('NSA_MYSQL', 'mysql contao');
const out = path.resolve(__dirname, '../../docs', lang, 'bilder');
const annotate = env('NSA_ANNOTATE', path.resolve(__dirname, '../../../NEO-Claude-Plugins/plugins/neo-doku/scripts/annotate.js'));
const sql = (query) => execSync(mysql + ' -N -e "' + query.replace(/"/g, '\\"') + '"').toString().trim();
const quote = (value) => "'" + String(value).replace(/\\/g, '\\\\').replace(/'/g, "\\'") + "'";
// The debug toolbar hidden; the help text below a field of the back end a little further from it, so that the
// frame round label and control does not touch it.
const HIDE = '.sf-toolbar,.sf-minitoolbar{display:none!important}.widget>.tl_help{margin-top:13px!important}';
const wanted = (file) => !only.length || only.includes(file.replace(/\.png$/, ''));

// Texts typed into the images, per language.
const T = {
  en: {
    connection: 'Appointments',
    newConnection: 'Calendar',
    starter: 'When can I check in?',
    email: 'jane.doe@example.com',
    name: 'Jane Doe',
    phone: '7700 900123',
    message: 'I would like to book the flat from 12 to 15 March for two people.',
    sources: ['https://www.example.com/llms.txt', 'https://www.example.org/events'],
  },
  de: {
    connection: 'Termine',
    newConnection: 'Kalender',
    starter: 'Ab wann kann ich einchecken?',
    email: 'erika.muster@example.com',
    name: 'Erika Muster',
    phone: '151 23456789',
    message: 'Ich möchte die Wohnung vom 12. bis 15. März für zwei Personen buchen.',
    sources: ['https://www.example.com/llms.txt', 'https://www.example.org/veranstaltungen'],
  },
}[lang];

const context = (browser, height = 800) => browser.newContext({
  viewport: { width: 1280, height },
  deviceScaleFactor: 2,
  reducedMotion: 'reduce',
  colorScheme: 'light',
  locale: lang === 'de' ? 'de-AT' : 'en-GB',
  timezoneId: 'Europe/Vienna',
});

// Marks: [selector, number, options]. Options: pad (space between element and frame, default 4), whole (frame a
// field of a back-end form with its help text), at (where the number sits: 'auto' (default) = 'side' in the left
// part of the window, 'corner' elsewhere; 'corner' top left of the frame, 'right' top right, 'above' centred above the frame,
// 'side' left of the frame at the height of its first line, 'after' right of the frame at its middle). The clip is the union of clipSelectors plus padding
// (a number, or { x, y, right } for the sides, for top and bottom and, if given, for the right side; document
// coordinates). The viewport is made as tall as the page first, so that the sticky save bar of a
// back-end form sits at the end of the page and not inside the picture (not for the chat window, which is fixed
// to the viewport: grow = false).
async function shoot(page, file, marks, clipSelectors, padding = 16, grow = true) {
  if (!wanted(file)) return;
  await page.evaluate(() => document.fonts.ready);
  const viewport = page.viewportSize();
  if (grow) {
    const height = await page.evaluate(() => document.documentElement.scrollHeight);
    await page.setViewportSize({ width: 1280, height: Math.max(800, height) });
  }
  await page.waitForTimeout(400);
  await page.addScriptTag({ path: annotate });
  const missing = await page.evaluate((list) => {
    const lost = [];
    const box = (r) => {
      const el = document.createElement('div');
      el.style.cssText = 'position:absolute;pointer-events:none;left:' + (r.left + scrollX) + 'px;top:' + (r.top + scrollY) + 'px;width:' + (r.right - r.left) + 'px;height:' + (r.bottom - r.top) + 'px';
      document.documentElement.appendChild(el);
      return el;
    };
    for (const [selector, number, options = {}] of list) {
      const el = document.querySelector(selector);
      if (!el || !el.getClientRects().length) {
        lost.push(selector);
        continue;
      }
      const pad = options.pad != null ? options.pad : 4;
      // A field of a back-end form: the frame goes round its label and control, not round its help text.
      let r = el.getBoundingClientRect();
      if (el.classList.contains('widget') && !options.whole) {
        const parts = [...el.children].filter((c) => !c.matches('.tl_help, .tl_tip, script, input[type="hidden"]') && c.getClientRects().length).map((c) => c.getBoundingClientRect());
        if (parts.length) {
          r = { left: Math.min(...parts.map((q) => q.left)), top: Math.min(...parts.map((q) => q.top)), right: Math.max(...parts.map((q) => q.right)), bottom: Math.max(...parts.map((q) => q.bottom)) };
          // The help text keeps its distance (HIDE gives it room), so the frame ends above it.
          const help = el.querySelector(':scope > .tl_help');
          if (help) r.bottom = Math.min(r.bottom, help.getBoundingClientRect().top - pad - 5);
        }
      }
      neoAnnotate.frame(box(r), { padding: pad });
      if (number == null) continue;
      let at = options.at || 'auto';
      if (at === 'auto') at = r.left < innerWidth * 0.4 ? 'side' : 'corner';
      const point = {
        corner: [r.left - pad, r.top - pad],
        right: [r.right + pad, r.top - pad],
        above: [(r.left + r.right) / 2, r.top - pad - 17],
        side: [r.left - pad - 17, r.top + Math.min(12, (r.bottom - r.top) / 2)],
        after: [r.right + pad + 17, (r.top + r.bottom) / 2],
      }[at];
      neoAnnotate.number(box({ left: point[0], top: point[1], right: point[0], bottom: point[1] }), number);
    }
    return lost;
  }, marks);
  if (missing.length) console.warn(file + ': not found: ' + missing.join(' | '));
  const clip = await page.evaluate(([selectors, p]) => {
    const boxes = selectors.map((s) => document.querySelector(s)).filter((e) => e && e.getClientRects().length).map((e) => e.getBoundingClientRect());
    const px = typeof p === 'number' ? p : p.x;
    const py = typeof p === 'number' ? p : p.y;
    const pr = typeof p === 'number' || p.right == null ? px : p.right;
    const left = Math.max(0, Math.min(...boxes.map((r) => r.left)) - px);
    const top = Math.max(0, Math.min(...boxes.map((r) => r.top)) - py);
    const right = Math.min(document.documentElement.scrollWidth, Math.max(...boxes.map((r) => r.right)) + pr);
    const bottom = Math.max(...boxes.map((r) => r.bottom)) + py;
    return { x: left + scrollX, y: top + scrollY, width: right - left, height: bottom - top };
  }, [clipSelectors, padding]);
  await page.screenshot({ path: path.join(out, file), clip });
  await page.evaluate(() => {
    document.getElementById('neo-annotation-layer')?.remove();
  });
  await page.setViewportSize(viewport);
  console.log(path.join(out, file));
}

// A back-end page, with the given sections of the form opened and closed (Contao keeps their state per user).
async function open(page, url, sections = [], closed = []) {
  await page.goto(base + url, { waitUntil: 'networkidle' });
  await page.addStyleTag({ content: HIDE });
  for (const id of sections) {
    const button = page.locator('#' + id + '.collapsed > legend button');
    if (await button.count()) await button.click();
  }
  for (const id of closed) {
    const button = page.locator('#' + id + ':not(.collapsed) > legend button');
    if (await button.count()) await button.click();
  }
  if (sections.length || closed.length) await page.waitForTimeout(300);
}

(async () => {
  if (!password) {
    console.error('Set NSA_ADMIN_PASSWORD.');
    process.exit(2);
  }

  // The back end in the language of the images. The website: the root page of that language, or else the first
  // root page set to that language for the time of the images. Everything is restored at the end.
  const restore = [];
  const userLanguage = sql("SELECT language FROM tl_user WHERE username = " + quote(user));
  sql("UPDATE tl_user SET language = " + quote(lang) + " WHERE username = " + quote(user));
  restore.push("UPDATE tl_user SET language = " + quote(userLanguage) + " WHERE username = " + quote(user));
  let visitorPage = env('NSA_PAGE', '');
  if (!visitorPage) {
    if (sql("SELECT id FROM tl_page WHERE type = 'root' AND language = " + quote(lang) + ' LIMIT 1')) {
      visitorPage = '/' + lang + '/';
    } else {
      const root = sql("SELECT id FROM tl_page WHERE type = 'root' ORDER BY sorting LIMIT 1");
      restore.push('UPDATE tl_page SET language = ' + quote(sql('SELECT language FROM tl_page WHERE id = ' + root)) + ' WHERE id = ' + root);
      sql('UPDATE tl_page SET language = ' + quote(lang) + ' WHERE id = ' + root);
      visitorPage = '/';
    }
  }
  // The assistant of that language (SuperAgent 1.0: one assistant, one language), its language record, the
  // connection with tools and the skill that is loaded but not taken over yet.
  const agent = Number(env('NSA_AGENT', '') || sql('SELECT l.pid FROM tl_neo_agent_language l JOIN tl_neo_agent a ON a.id = l.pid WHERE l.language = ' + quote(lang) + ' ORDER BY a.id LIMIT 1'));
  const record = sql('SELECT id FROM tl_neo_agent_language WHERE pid = ' + agent + ' AND language = ' + quote(lang));
  const connection = Number(env('NSA_CONNECTION', '') || sql("SELECT id FROM tl_neo_agent_connection WHERE tools LIKE '%list_free_slots%' ORDER BY id LIMIT 1") || 0);
  const skill = Number(env('NSA_SKILL', '') || sql('SELECT id FROM tl_neo_agent_skill WHERE pending IS NOT NULL ORDER BY id LIMIT 1') || 0);
  if (connection) {
    restore.push('UPDATE tl_neo_agent_connection SET name = ' + quote(sql('SELECT name FROM tl_neo_agent_connection WHERE id = ' + connection)) + ' WHERE id = ' + connection);
    sql('UPDATE tl_neo_agent_connection SET name = ' + quote(T.connection) + ' WHERE id = ' + connection);
  }
  // Web search on, with two approved sources, for the image of the section (restored at the end).
  const search = sql('SELECT CONCAT_WS(0x1f, web_search, web_search_mode, web_search_max, IFNULL(HEX(web_search_sources), \'\')) FROM tl_neo_agent WHERE id = ' + agent).split('\x1f');
  restore.push('UPDATE tl_neo_agent SET web_search = ' + quote(search[0]) + ', web_search_mode = ' + quote(search[1]) + ', web_search_max = ' + quote(search[2]) + ', web_search_sources = ' + (search[3] ? 'UNHEX(' + quote(search[3]) + ')' : 'NULL') + ' WHERE id = ' + agent);
  const sources = 'a:' + T.sources.length + ':{' + T.sources.map((s, i) => 'i:' + i + ';s:' + Buffer.byteLength(s) + ':"' + s + '";').join('') + '}';
  sql("UPDATE tl_neo_agent SET web_search = '1', web_search_mode = 'sources', web_search_max = '3', web_search_sources = " + quote(sources) + ' WHERE id = ' + agent);

  const browser = await chromium.launch();

  try {
    // ---- The chat on the website, as a visitor sees it after consent. The window is fixed to the viewport, which
    // is tall enough for the forwarding form.
    const visitor = await (await context(browser, 1100)).newPage();
    await visitor.goto(base + visitorPage, { waitUntil: 'networkidle' });
    const accept = visitor.locator('[data-ncc]:not([hidden]) [data-ncc-view="banner"] [data-ncc-action="accept"]');
    if (await accept.count()) await accept.first().click();
    await visitor.locator('[data-nsa-toggle]').click();
    await visitor.waitForSelector('[data-nsa-suggest] .nsa-chip');
    await visitor.waitForTimeout(600);
    const chat = ['[data-nsa-panel]', '[data-nsa-toggle]'];
    await shoot(visitor, 'chat-window.png', [
      ['[data-nsa-new]', 1, { pad: 3, at: 'above' }],
      ['[data-nsa-ai]', 2, { pad: 2 }],
      ['[data-nsa-suggest]', 3, { pad: 4 }],
      ['[data-nsa-input]', 4, { pad: 3 }],
      ['[data-nsa-mic]', 5, { pad: 3, at: 'above' }],
      ['[data-nsa-handoff]', 6, { pad: 0 }],
      ['[data-nsa-toggle]', 7, { pad: 3 }],
    ], chat, 28, false);

    // The forwarding form, opened with the button below the conversation and filled in (not sent).
    await visitor.click('[data-nsa-handoff]');
    await visitor.waitForSelector('[data-nsa-esc-form]:not([hidden])');
    await visitor.waitForTimeout(600);
    const fill = async (selector, value) => {
      if (await visitor.locator(selector).count()) await visitor.fill(selector, value);
    };
    await fill('[data-nsa-esc-field="name"]', T.name);
    await fill('[data-nsa-esc-email]', T.email);
    await fill('[data-nsa-esc-field="phone"]', T.phone);
    await fill('[data-nsa-esc-message]', T.message);
    if (await visitor.locator('[data-nsa-esc-consent]').count()) await visitor.check('[data-nsa-esc-consent]');
    await visitor.evaluate(() => document.activeElement?.blur());
    await visitor.waitForTimeout(300);
    await shoot(visitor, 'chat-forwarding.png', [
      ['.nsa-field:has([data-nsa-esc-field="name"])', 1, { pad: 3, at: 'side' }],
      ['.nsa-field:has([data-nsa-esc-email])', 2, { pad: 3, at: 'side' }],
      ['.nsa-field:has([data-nsa-esc-field="phone"])', 3, { pad: 3, at: 'side' }],
      ['.nsa-field:has([data-nsa-esc-message])', 4, { pad: 3, at: 'side' }],
      ['.nsa-check:has([data-nsa-esc-consent])', 5, { pad: 3, at: 'side' }],
      ['[data-nsa-esc-form] button[type="submit"]', 6, { pad: 3, at: 'side' }],
    ], chat, 28, false);

    // A starter question with a stored answer: the AI service words the reply (needs a working AI connection).
    if (wanted('chat-answer.png')) {
      await visitor.click('[data-nsa-esc-cancel]');
      await visitor.locator('[data-nsa-suggest] .nsa-chip', { hasText: T.starter }).first().click();
      await visitor.waitForFunction(() => {
        const last = document.querySelector('[data-nsa-log]')?.lastElementChild;
        return last && last.classList.contains('nsa-msg--bot') && !last.hasAttribute('data-nsa-typing') && last.textContent.trim().length > 10;
      }, null, { timeout: 90000 }).catch(() => console.warn('chat-answer.png: no answer within 90 s'));
      await visitor.waitForTimeout(1200);
      await shoot(visitor, 'chat-answer.png', [
        ['[data-nsa-log] > .nsa-msg--user', 1, { pad: 3, at: 'side' }],
        ['[data-nsa-log] > .nsa-msg--bot:last-child', 2, { pad: 3 }],
        ['[data-nsa-suggestions]', 3, { pad: 3, at: 'side' }],
      ], chat, 28, false);
    }
    await visitor.context().close();

    // ---- The back end.
    const page = await (await context(browser)).newPage();
    await page.goto(base + '/contao/login');
    await page.fill('input[name="username"]', user);
    await page.fill('input[name="password"]', password);
    await Promise.all([page.waitForNavigation(), page.click('button[type="submit"]')]);

    // Access keys and licences (NEO Basic): the licence of SuperAgent, the AI connection.
    await open(page, '/contao?do=neo_access', ['pal_ai_legend'], ['pal_neo_terms_legend']);
    await shoot(page, 'access-licence.png', [
      ['.widget:has(> input[name="license_superagent_key"])', 1, { pad: 3, at: 'side' }],
      ['#neo_license_superagent .tl_message', 2, { pad: 3, at: 'right' }],
      ['#pal_neo_terms_legend > legend', 3, { pad: 3, at: 'after' }],
      ['.widget:has(select[name="ai_route"])', 4, { pad: 3, at: 'side' }],
      ['.widget:has(input[name="ai_api_key"])', 5, { pad: 3 }],
      ['#neo_access_ai button.tl_submit', 6, { pad: 3, at: 'after' }],
    ], ['#pal_neo_license_legend', '#pal_neo_terms_legend', '#pal_ai_legend'], { x: 24, y: 8 });

    // The terms as a new installation shows them: the confirmation of SuperAgent is set aside for this image.
    if (wanted('access-terms.png')) {
      const undo = "UPDATE tl_neo_terms SET module = 'superagent' WHERE module = 'superagent-docs-capture'";
      restore.push(undo);
      sql("UPDATE tl_neo_terms SET module = 'superagent-docs-capture' WHERE module = 'superagent'");
      await open(page, '/contao?do=neo_access', ['pal_neo_terms_legend']);
      const terms = '#pal_neo_terms_legend .widget:has(#neo_terms_ok_superagent)';
      await shoot(page, 'access-terms.png', [
        [terms + ' > h3', 1, { pad: 2, at: 'side' }],
        [terms + ' > details > summary', 2, { pad: 2, at: 'side' }],
        [terms + ' .tl_checkbox_single_container', 3, { pad: 6, at: 'side' }],
        [terms + ' button[name="neo_terms_accept"]', 4, { pad: 3, at: 'after' }],
      ], ['#pal_neo_terms_legend > legend', terms], { x: 24, y: 8 });
      await open(page, '/contao?do=neo_access', [], ['pal_neo_terms_legend']);
      sql(undo);
    }

    // The front-end module.
    const module = sql("SELECT id FROM tl_module WHERE type = 'neo_agent' AND agent = " + agent + ' ORDER BY id LIMIT 1');
    if (module) {
      const theme = sql('SELECT pid FROM tl_module WHERE id = ' + module);
      await open(page, '/contao?do=themes&table=tl_module&id=' + module + '&act=edit&pid=' + theme);
      await shoot(page, 'module-form.png', [
        ['.widget:has([name="type"])', 1, { pad: 4 }],
        ['.widget:has([name="agent"])', 2, { pad: 4 }],
        ['.widget:has([name="nsa_ga_event"])', 3, { pad: 4 }],
      ], ['#pal_title_legend', '#pal_config_legend'], { x: 34, y: 10 });
    }

    // The list of assistants.
    await open(page, '/contao?do=neo_agent');
    await shoot(page, 'assistant-list.png', [
      ['#tl_buttons .header_new', 1, { pad: 5, at: 'above' }],
      ['#tl_buttons .header_settings', 2, { pad: 5, at: 'above' }],
      ['#tl_buttons .header_connections', 3, { pad: 5, at: 'above' }],
      ['#tl_buttons .header_skills', 4, { pad: 5, at: 'above' }],
      ['#tl_buttons .header_tool_log', 5, { pad: 5, at: 'above' }],
      ['.tl_listing a[href*="act=edit"][href*="&id=' + agent + '&"]', 6, { pad: 5, at: 'above' }],
      ['.tl_listing a[href*="act=copy"][href*="&id=' + agent + '&"]', 7, { pad: 5, at: 'above' }],
    ], ['#tl_buttons', '.tl_listing'], { x: 12, y: 40 });

    // The assistant's form, section by section.
    const form = '/contao?do=neo_agent&act=edit&id=' + agent;
    await open(page, form);
    await shoot(page, 'assistant-form.png', [
      ['.widget:has(input[name="published"])', 1, { pad: 4 }],
      ['.widget:has([name="visibility"])', 2, { pad: 4 }],
      ['.widget:has(select[name="consent_category"])', 3, { pad: 4 }],
      ['.widget:has(input[name="assistant_name"])', 4, { pad: 4 }],
      ['.widget:has([name="avatar"])', 5, { pad: 4 }],
      ['.widget:has([name="widget_side"])', 6, { pad: 4 }],
      ['.nsa-languages__item', 7, { pad: 4 }],
      ['.nsa-languages__item a.tl_submit', 8, { pad: 3, at: 'right' }],
    ], ['#pal_publish_legend', '#pal_identity_legend', '#pal_languages_legend'], { x: 34, y: 10 });
    await shoot(page, 'assistant-model.png', [
      ['#pal_provider_legend .widget:has([name="model"])', 1, { pad: 4 }],
      ['#pal_provider_legend .widget-group > .widget.clr', 2, { pad: 3, whole: true }],
      ['.widget:has([name="temperature"]), .widget:has([name="reasoning_effort"])', 3, { pad: 4 }],
      ['.widget:has([name="max_tokens"])', 4, { pad: 4, at: 'after' }],
    ], ['#pal_provider_legend'], { x: 34, y: 10 });
    await shoot(page, 'assistant-answers.png', [
      ['.widget:has([name="tone_address"])', 1, { pad: 4 }],
      ['.widget:has([name="answer_length"])', 2, { pad: 4 }],
      ['.widget:has([name="tone_style"])', 3, { pad: 4 }],
      ['.widget:has([name="grounding_source"])', 4, { pad: 4 }],
      ['.widget:has(input[name="no_medical"])', 5, { pad: 4 }],
      ['.widget:has(input[name="privacy_mode"])', 6, { pad: 4, at: 'after' }],
    ], ['#pal_persona_legend', '#pal_grounding_legend', '#pal_safety_legend'], { x: 34, y: 10 });
    await shoot(page, 'assistant-voice-search.png', [
      ['.widget:has(input[name="voice_input"])', 1, { pad: 4 }],
      ['.widget:has([name="transcription_model"])', 2, { pad: 4 }],
      ['.widget:has(input[name="voice_in_privacy"])', 3, { pad: 4 }],
      ['.widget:has(input[name="web_search"])', 4, { pad: 4 }],
      ['.widget:has([name="web_search_mode"])', 5, { pad: 4 }],
      ['.widget:has([name^="web_search_sources"])', 6, { pad: 4 }],
      ['.widget:has([name="web_search_max"])', 7, { pad: 4 }],
    ], ['#pal_voice_legend', '#pal_research_legend'], { x: 34, y: 10 });
    await shoot(page, 'assistant-escalation.png', [
      ['.widget:has(input[name="escalation_email"])', 1, { pad: 4 }],
      ['.widget:has(input[name="escalation_name"])', 2, { pad: 4 }],
      ['.widget:has([name="contact_page"])', 3, { pad: 4 }],
      ['.widget:has([name="form_fields[]"])', 4, { pad: 4 }],
      ['.widget:has([name="form_required[]"])', 5, { pad: 4 }],
      ['.widget:has([name="phone_country"])', 6, { pad: 4 }],
      ['.widget:has(input[name="form_consent"])', 7, { pad: 4 }],
      ['.widget:has([name="privacy_page"])', 8, { pad: 4 }],
      ['.widget:has([name="newsletter_module"])', 9, { pad: 4 }],
    ], ['#pal_escalation_legend'], { x: 34, y: 10 });
    // Skills and tools: the tools of the first connection shown, the other connections folded (Contao keeps the
    // state of the groups per user).
    const togglers = await page.locator('#ctrl_mcp_tools a.foldable').all();
    for (const [index, toggler] of togglers.entries()) {
      const shown = await toggler.evaluate((a) => {
        const id = (a.getAttribute('onclick') || '').match(/'(cbc_[^']+)'/)?.[1];
        const group = id && document.getElementById(id);
        return Boolean(group && group.style.display !== 'none');
      });
      if (shown !== (index === 0)) await toggler.click();
    }
    await page.waitForTimeout(400);
    await shoot(page, 'assistant-tools.png', [
      ['#ctrl_skills', 1, { pad: 4 }],
      ['#ctrl_mcp_tools', 2, { pad: 4 }],
      ['#pal_mcp_legend .widget-group > .widget.clr:not(:has(#ctrl_mcp_tools))', 3, { pad: 3, whole: true }],
    ], ['#pal_skills_legend', '#pal_mcp_legend'], { x: 34, y: 10 });

    // The language record: language and texts, starter questions, the texts of the window.
    if (record) {
      const recordUrl = '/contao?do=neo_agent&table=tl_neo_agent_language&act=edit&id=' + record;
      await open(page, recordUrl, [], ['pal_labels_legend']);
      await shoot(page, 'language-texts.png', [
        ['.widget:has([name="language"])', 1, { pad: 4 }],
        ['.widget:has(input[name="subtitle"])', 2, { pad: 4 }],
        ['.widget:has([name="greeting"])', 3, { pad: 4 }],
        ['.widget:has([name="fact_note"])', 4, { pad: 4 }],
        ['.widget:has([name="input_placeholder"])', 5, { pad: 4 }],
      ], ['#pal_language_legend', '#pal_identity_legend'], { x: 34, y: 10 });
      await shoot(page, 'language-record.png', [
        ['#pal_ui_legend .widget:has([name^="starter_questions["])', 1, { pad: 4 }],
        ['#pal_ui_legend .widget:has([name^="starter_questions["]) tbody tr:first-child td:last-child', 2, { pad: 2, at: 'right' }],
        ['#pal_ui_legend .widget:has([name^="canned["])', 3, { pad: 4 }],
        ['#neo_suggestions', 4, { pad: 3, at: 'after' }],
      ], ['#pal_ui_legend', '.tl_formbody_submit'], { x: 34, y: 10 });
      await open(page, recordUrl, ['pal_labels_legend']);
      await shoot(page, 'language-widget.png', [
        ['#pal_labels_legend .widget:has([name="ui_ai_notice"])', 1, { pad: 4 }],
      ], ['#pal_labels_legend > legend', '#pal_labels_legend .widget:has([name="ui_ai_notice"])', '#pal_labels_legend .widget:has([name="ui_privacy_reject"])'], { x: 34, y: 10 });
      await open(page, recordUrl, [], ['pal_labels_legend']);
    }

    // A new connection to an MCP server (Plus), filled in, not saved: no request leaves the installation.
    await open(page, '/contao?do=neo_agent&table=tl_neo_agent_connection');
    if (wanted('connection-form.png')) {
      await Promise.all([page.waitForURL(/act=edit/), page.locator('.header_new').first().click()]);
      await page.waitForLoadState('networkidle');
      await page.addStyleTag({ content: HIDE });
      await page.fill('input[name="name"]', T.newConnection);
      await page.fill('input[name="url"]', 'https://mcp.example.com/mcp');
      await shoot(page, 'connection-form.png', [
        ['.widget:has([name="template"])', 1, { pad: 4 }],
        ['.widget:has(input[name="name"])', 2, { pad: 4, at: 'after' }],
        ['.widget:has(input[name="url"])', 3, { pad: 4 }],
        ['.widget:has([name="auth"])', 4, { pad: 4 }],
        ['.widget:has([name="personal_data"])', 5, { pad: 4 }],
      ], ['#pal_title_legend', '#pal_auth_legend', '#pal_data_legend'], { x: 34, y: 10 });
      // The draft is removed again (a new record keeps tstamp 0 until it is saved).
      const draft = Number(new URL(page.url()).searchParams.get('id'));
      if (draft) sql('DELETE FROM tl_neo_agent_connection WHERE id = ' + draft + ' AND tstamp = 0');
    }

    // A stored connection: its tools, the locks and the confirmation.
    if (connection) {
      await open(page, '/contao?do=neo_agent&table=tl_neo_agent_connection&act=edit&id=' + connection);
      await shoot(page, 'connection-tools.png', [
        ['#pal_tools_legend button[name="neo_mcp_read"], #pal_tools_legend .tl_submit', 1, { pad: 3, at: 'right' }],
        ['#pal_tools_legend tr:has(input[value="list_free_slots"])', 2, { pad: 2, at: 'side' }],
        ['#pal_tools_legend tr:has(input[value="book_appointment"])', 3, { pad: 2, at: 'side' }],
        ['#pal_tools_legend tr:has(input[value="book_appointment"]) td:last-child', 4, { pad: 3, at: 'right' }],
        ['#pal_confirm_legend .widget', 5, { pad: 3, whole: true }],
      ], ['#pal_tools_legend', '#pal_confirm_legend'], { x: 34, y: 10 });
    }

    // The tool log.
    await open(page, '/contao?do=neo_agent&table=tl_neo_agent_tool_log');
    await shoot(page, 'tool-log.png', [
      ['.tl_listing tr:has(> td)', 1, { pad: 3 }],
      ['.tl_listing tr:has(> td) ~ tr:has(> td)', 2, { pad: 3 }],
    ], ['#tl_buttons', '.tl_listing, .tl_empty'], { x: 34, y: 10, right: 12 });

    // A new skill from GitHub (Plus), filled in, not saved.
    await open(page, '/contao?do=neo_agent&table=tl_neo_agent_skill');
    if (wanted('skill-form.png')) {
      await Promise.all([page.waitForURL(/act=edit/), page.locator('.header_new').first().click()]);
      await page.waitForLoadState('networkidle');
      await page.addStyleTag({ content: HIDE });
      // GitHub is the preset source; its address field is shown with it.
      await page.fill('input[name="url"]', 'https://github.com/owner/repo/tree/main/skills/appointments');
      await shoot(page, 'skill-form.png', [
        ['.widget:has([name="source"])', 1, { pad: 4 }],
        ['.widget:has(input[name="url"])', 2, { pad: 4 }],
        ['button[name="neo_skill_fetch"]', 3, { pad: 3, at: 'right' }],
      ], ['#pal_source_legend', '#pal_check_legend'], { x: 34, y: 10 });
      const draft = Number(new URL(page.url()).searchParams.get('id'));
      if (draft) sql('DELETE FROM tl_neo_agent_skill WHERE id = ' + draft + ' AND tstamp = 0');
    }

    // A skill that is loaded and checked, not taken over yet.
    if (skill) {
      await open(page, '/contao?do=neo_agent&table=tl_neo_agent_skill&act=edit&id=' + skill);
      await shoot(page, 'skill-review.png', [
        ['#pal_check_legend .tl_message', 1, { pad: 4 }],
        ['#pal_check_legend details', 2, { pad: 4 }],
        ['#pal_check_legend .tl_checkbox_single_container', 3, { pad: 4 }],
        ['.tl_submit_container #save', 4, { pad: 3, at: 'above' }],
      ], ['#pal_check_legend', '.tl_formbody_submit'], { x: 34, y: 10 });
    }
  } finally {
    await browser.close();
    for (const query of restore) sql(query);
  }
})();
