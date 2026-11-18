/* =========================================================================
   SuperAgent — front-end chat widget (vanilla JS, no dependencies).

   - Reads its configuration from the [data-nsa-config] JSON the module renders.
   - Consent-aware: the chat waits for the consent category of the assistant when
     the page has a consent tool: window.cookieConsent.allows(category) of the
     cookie consent extension of this family (re-checked on "cookie-consent:change"),
     or window.superAgentConsent(category) of any other tool (re-checked on
     "superagent:consent-change"). Without a consent tool the chat is available
     immediately.
   - Talks to the bundle endpoints; when the AI service gives no answer, it shows a notice.
   - Messages of the visitor are inserted as text; replies of the assistant are
     Markdown, built from DOM nodes and text nodes (renderMarkdown). Never
     innerHTML with message text → no injection.
   - Without a licence the panel carries the credit line of the free edition,
     drawn and kept visible by the shared script of the Basic bundle
     (bundles/neobasic/credit.js, window.NeoCredit). While it is not visible,
     the chat takes no input.
   ========================================================================= */
(function () {
  'use strict';

  /* =========================================================================
     Replies of the assistant: a small Markdown renderer.

     Paragraphs and line breaks, **bold** / __bold__, *italic* / _italic_,
     `code`, headings (# to ######, shown as a bold line, never as a heading of
     the page), lists (-, *, +, •, – and 1. / 1), nested by indentation), pipe
     tables (header row and separator row; shown row by row as cards when
     the table is wider than the bubble), fenced code, quotes, rules and links.

     Built from DOM nodes and text nodes only, never innerHTML: the model's text
     can never become markup. Links go only to http(s) addresses, to paths of
     this website ("/…"), to mailto: and tel:; any other address (javascript:,
     data: …) stays text. A paragraph or line that is nothing but one
     [label](address) becomes a button (a.nsa-cta): that is how a payment link
     appears. E-mail addresses arrive defused from the server ("(at)", MailGuard)
     and stay text.
     ========================================================================= */
  var MD_ESCAPABLE = '\\`*_{}[]()#+-.!|>~<';
  var MD_WORD = /[0-9A-Za-zÀ-ɏ]/;
  var MD_BULLET = /^(\s*)([-*+•–]|\d{1,3}[.)])\s+(.*)$/;
  var MD_RULE = /^\s{0,3}([-*_])(?:\s*\1){2,}\s*$/;
  var MD_HEADING = /^\s{0,3}#{1,6}\s+(.*?)(?:\s+#+)?\s*$/;
  var MD_FENCE = /^\s{0,3}(`{3,}|~{3,})/;
  var MD_QUOTE = /^\s{0,3}>\s?(.*)$/;
  var MD_TABLE_SEP = /^\s*\|?\s*:?-+:?\s*(?:\|\s*:?-+:?\s*)*\|?\s*$/;
  var MD_LINK = /^\[((?:\\.|[^\\\]\n])+)\]\(\s*<?((?:[^\s()<>]|\([^\s()<>]*\))+)>?(?:\s+"[^"\n]*")?\s*\)/;
  var MD_AUTOLINK = /^<((?:https?:\/\/|mailto:|tel:)[^\s<>]+)>/i;
  var MD_URL = /^https?:\/\/[^\s<]*[^\s<.,;:!?)\]'"»“”*_]/i;
  var MD_BR = /^<br\s*\/?>/i;
  var MD_CODE = /^`([^`\n]+)`/;
  var MD_DEPTH = 4;

  // Address of a link, or '' when it is not allowed.
  function mdHref(url) {
    url = String(url || '');
    if (!url || /[\s\u0000-\u001f\u007f]/.test(url)) return '';
    var target;
    if (/^https?:\/\//i.test(url)) {
      try { target = new URL(url); } catch (e) { return ''; }
      return target.protocol === 'http:' || target.protocol === 'https:' ? target.href : '';
    }
    // A path of this website: only on this host.
    if (/^\/(?![\/\\])/.test(url)) {
      try { target = new URL(url, location.href); } catch (e) { return ''; }
      return target.origin === location.origin ? target.href : '';
    }
    if (/^mailto:[^\s@()<>,;:"]+@[^\s@()<>,;:"]+\.[a-z]{2,}$/i.test(url)) return url;
    if (/^tel:\+?[0-9 ().\/-]{3,30}$/i.test(url)) return 'tel:' + url.slice(4).replace(/[^0-9+]/g, '');
    return '';
  }

  // [label](address), <address> or a web address in the text. A link that is not
  // allowed leaves its label (or the address) as text.
  function mdLink(label, url, depth) {
    var href = mdHref(url);
    if (!href) {
      var rest = document.createDocumentFragment();
      if (label !== null) mdInline(rest, label, depth + 1, true);
      else rest.appendChild(document.createTextNode(url));
      return rest;
    }
    var a = document.createElement('a');
    a.href = href;
    a.rel = 'noopener';
    if (label !== null) {
      mdInline(a, label, depth + 1, true);
      // Only a link with its own label can become a button (mdSoleLink).
      a._nsaLabel = !/^(?:https?:|mailto:|tel:|www\.)/i.test(a.textContent.trim());
    } else {
      a.textContent = url.replace(/^(?:mailto|tel):/i, '');
    }
    return a;
  }

  // **bold** / *italic* starting at s[i]: where the text inside starts and ends.
  // The opening marker is followed by text, the closing one follows text;
  // underscores inside a word (snake_case) stay as they are.
  function mdEmphasis(s, i, c) {
    var dbl = s.charAt(i + 1) === c;
    var n = dbl ? 2 : 1;
    var open = i + n;
    if (!s.charAt(open) || /\s/.test(s.charAt(open))) return null;
    if (c === '_' && MD_WORD.test(s.charAt(i - 1))) return null;
    var marker = dbl ? c + c : c;
    var j = open;
    for (;;) {
      var k = s.indexOf(marker, j);
      if (k === -1) return null;
      // A **…** inside *…*: skip it as a whole.
      if (!dbl && s.charAt(k + 1) === c) { j = k + 2; continue; }
      if (k === open || /\s/.test(s.charAt(k - 1)) || (c === '_' && MD_WORD.test(s.charAt(k + n)))) { j = k + n; continue; }
      return { strong: dbl, from: open, to: k, next: k + n };
    }
  }

  // Inline formatting into parent. noLinks: inside the label of a link.
  function mdInline(parent, s, depth, noLinks) {
    var buf = '';
    var i = 0;
    var m, span, node;
    function add(child) {
      if (buf) { parent.appendChild(document.createTextNode(buf)); buf = ''; }
      parent.appendChild(child);
    }
    while (i < s.length) {
      var c = s.charAt(i);
      var rest = s.slice(i);
      if (c === '\\' && i + 1 < s.length && MD_ESCAPABLE.indexOf(s.charAt(i + 1)) !== -1) {
        buf += s.charAt(i + 1);
        i += 2;
      } else if (c === '\n') {
        add(document.createElement('br'));
        i++;
      } else if (c === '<' && (m = MD_BR.exec(rest))) {
        // <br> in a table cell: a line break; any other tag stays text.
        add(document.createElement('br'));
        i += m[0].length;
      } else if (c === '`' && (m = MD_CODE.exec(rest))) {
        node = document.createElement('code');
        node.textContent = m[1];
        add(node);
        i += m[0].length;
      } else if (!noLinks && c === '[' && (m = MD_LINK.exec(rest))) {
        add(mdLink(m[1], m[2], depth));
        i += m[0].length;
      } else if (!noLinks && c === '<' && (m = MD_AUTOLINK.exec(rest))) {
        add(mdLink(null, m[1], depth));
        i += m[0].length;
      } else if (!noLinks && (c === 'h' || c === 'H') && !MD_WORD.test(s.charAt(i - 1)) && (m = MD_URL.exec(rest))) {
        add(mdLink(null, m[0], depth));
        i += m[0].length;
      } else if ((c === '*' || c === '_') && depth < MD_DEPTH && (span = mdEmphasis(s, i, c))) {
        node = document.createElement(span.strong ? 'strong' : 'em');
        mdInline(node, s.slice(span.from, span.to), depth + 1, noLinks);
        add(node);
        i = span.next;
      } else {
        buf += c;
        i++;
      }
    }
    if (buf) parent.appendChild(document.createTextNode(buf));
  }

  // The one link a paragraph consists of (also inside **…**), or null.
  function mdSoleLink(node) {
    var kids = [].filter.call(node.childNodes, function (n) { return n.nodeType !== 3 || n.nodeValue.trim(); });
    if (kids.length !== 1) return null;
    var only = kids[0];
    if (only.nodeName === 'STRONG' || only.nodeName === 'EM') return mdSoleLink(only);
    return only.nodeName === 'A' && only._nsaLabel ? only : null;
  }

  function mdParagraph(src) {
    var p = document.createElement('p');
    mdInline(p, src, 0, false);
    var link = mdSoleLink(p);
    if (link) {
      p.className = 'nsa-md-cta';
      link.className = 'nsa-cta';
      if (link.parentNode !== p) { p.textContent = ''; p.appendChild(link); }
    }
    return p;
  }

  // Cells of a table row; "\|" and pipes inside `code` are text.
  function mdCells(line) {
    var s = line.trim();
    if (s.charAt(0) === '|') s = s.slice(1);
    if (s.slice(-1) === '|' && s.slice(-2) !== '\\|') s = s.slice(0, -1);
    var cells = [];
    var cur = '';
    var code = false;
    for (var i = 0; i < s.length; i++) {
      var c = s.charAt(i);
      if (c === '\\' && s.charAt(i + 1) === '|') { cur += '\\|'; i++; continue; }
      if (c === '`') code = !code;
      if (c === '|' && !code) { cells.push(cur.trim()); cur = ''; continue; }
      cur += c;
    }
    cells.push(cur.trim());
    return cells;
  }

  // A table starts here: a row with pipes, then a separator row (|---|---|) with as many cells.
  function mdTableAt(lines, i) {
    return i + 1 < lines.length && lines[i].indexOf('|') !== -1 && lines[i + 1].indexOf('|') !== -1 &&
      MD_TABLE_SEP.test(lines[i + 1]) && mdCells(lines[i + 1]).length === mdCells(lines[i]).length;
  }

  // A table wider than the bubble is shown row by row ("Header: value" per cell, nsa-md-table--stacked):
  // no sideways scrolling, no words broken in the middle. Measured again when the width of the window changes.
  var mdBoxes = null;
  function mdFit(box) {
    box.classList.remove('nsa-md-table--stacked');
    box.classList.toggle('nsa-md-table--stacked', box.scrollWidth > box.clientWidth + 1);
  }
  function mdWatch(box) {
    var raf = window.requestAnimationFrame || function (f) { return setTimeout(f, 0); };
    raf(function () { mdFit(box); });
    if (!window.ResizeObserver) return;
    if (!mdBoxes) {
      mdBoxes = new ResizeObserver(function (entries) {
        entries.forEach(function (e) {
          var w = Math.round(e.contentRect.width);
          if (w === e.target._nsaWidth) return;
          e.target._nsaWidth = w;
          raf(function () { mdFit(e.target); });
        });
      });
    }
    mdBoxes.observe(box);
  }
  function mdTable(lines, i, parent) {
    var head = mdCells(lines[i]);
    var align = mdCells(lines[i + 1]).map(function (c) {
      var left = c.charAt(0) === ':';
      var right = c.slice(-1) === ':';
      return left && right ? 'nsa-md-center' : (right ? 'nsa-md-right' : '');
    });
    var box = document.createElement('div');
    box.className = 'nsa-md-table';
    var table = document.createElement('table');
    var row = table.createTHead().insertRow();
    head.forEach(function (text, k) {
      var th = document.createElement('th');
      th.scope = 'col';
      if (align[k]) th.className = align[k];
      mdInline(th, text, 0, false);
      row.appendChild(th);
    });
    var tbody = table.createTBody();
    for (i += 2; i < lines.length && lines[i].trim() && lines[i].indexOf('|') !== -1; i++) {
      var cells = mdCells(lines[i]);
      row = tbody.insertRow();
      for (var k = 0; k < head.length; k++) {
        var td = row.insertCell();
        if (align[k]) td.className = align[k];
        td.setAttribute('data-label', table.tHead.rows[0].cells[k].textContent.trim());
        mdInline(td, cells[k] || '', 0, false);
      }
    }
    box.appendChild(table);
    parent.appendChild(box);
    mdWatch(box);
    return i;
  }

  // Lists; an item indented by two or more spaces more than the one above is
  // nested in it (up to three levels), an indented line without a marker
  // continues the item. A blank line between items keeps the list.
  function mdList(lines, i, parent) {
    var stack = [];
    while (i < lines.length) {
      var line = lines[i];
      if (!line.trim()) {
        var j = i + 1;
        while (j < lines.length && !lines[j].trim()) j++;
        if (j < lines.length && MD_BULLET.test(lines[j]) && !MD_RULE.test(lines[j])) { i = j; continue; }
        break;
      }
      var m = MD_BULLET.exec(line);
      if (!m || MD_RULE.test(line)) {
        if (stack.length && /^\s{2,}\S/.test(line)) {
          var li = stack[stack.length - 1].li;
          li.appendChild(document.createElement('br'));
          mdInline(li, line.trim(), 0, false);
          i++;
          continue;
        }
        break;
      }
      var indent = m[1].replace(/\t/g, '    ').length;
      var ordered = /\d/.test(m[2].charAt(0));
      while (stack.length > 1 && indent < stack[stack.length - 1].indent) stack.pop();
      var top = stack[stack.length - 1];
      if (top && indent >= top.indent + 2 && stack.length < 3) {
        var nested = document.createElement(ordered ? 'ol' : 'ul');
        top.li.appendChild(nested);
        stack.push(top = { indent: indent, ordered: ordered, list: nested, li: null });
      } else if (!top || top.ordered !== ordered) {
        // The first list, or another kind of list at the same level: a new list next to it.
        var list = document.createElement(ordered ? 'ol' : 'ul');
        (top ? top.list.parentNode : parent).appendChild(list);
        if (top) { top.list = list; top.ordered = ordered; }
        else stack.push(top = { indent: indent, ordered: ordered, list: list, li: null });
      }
      var item = document.createElement('li');
      // The number the model wrote, not a count (1., 3., 7. stays 1., 3., 7.).
      if (ordered) item.value = parseInt(m[2], 10);
      mdInline(item, m[3], 0, false);
      top.list.appendChild(item);
      top.li = item;
      i++;
    }
    return i;
  }

  function mdCode(lines, i, fence, parent) {
    var body = [];
    for (i++; i < lines.length && lines[i].trim().slice(0, fence.length) !== fence; i++) body.push(lines[i]);
    var pre = document.createElement('pre');
    var code = document.createElement('code');
    code.textContent = body.join('\n');
    pre.appendChild(code);
    parent.appendChild(pre);
    return i + 1;
  }

  function renderMarkdown(el, text, depth) {
    var lines = String(text == null ? '' : text).replace(/\r\n?/g, '\n').split('\n');
    var para = [];
    var i = 0;
    var m, p;
    function flush() {
      if (para.length) el.appendChild(mdParagraph(para.join('\n')));
      para = [];
    }
    while (i < lines.length) {
      var line = lines[i];
      if (!line.trim()) {
        flush();
        i++;
      } else if ((m = MD_FENCE.exec(line))) {
        flush();
        i = mdCode(lines, i, m[1], el);
      } else if ((m = MD_HEADING.exec(line)) && m[1]) {
        flush();
        p = document.createElement('p');
        p.className = 'nsa-md-h';
        var strong = document.createElement('strong');
        mdInline(strong, m[1], 0, false);
        p.appendChild(strong);
        el.appendChild(p);
        i++;
      } else if (MD_RULE.test(line)) {
        flush();
        el.appendChild(document.createElement('hr'));
        i++;
      } else if (mdTableAt(lines, i)) {
        flush();
        i = mdTable(lines, i, el);
      } else if (MD_BULLET.test(line)) {
        flush();
        i = mdList(lines, i, el);
      } else if (depth < 2 && MD_QUOTE.test(line)) {
        flush();
        var quote = [];
        for (; i < lines.length && (m = MD_QUOTE.exec(lines[i])); i++) quote.push(m[1]);
        var bq = document.createElement('blockquote');
        renderMarkdown(bq, quote.join('\n'), depth + 1);
        el.appendChild(bq);
      } else {
        // A line that is only a link (e.g. the payment link) is a button, also without a blank line around it.
        p = /^[*_]{0,2}\[/.test(line.trim()) ? mdParagraph(line.trim()) : null;
        if (p && p.className === 'nsa-md-cta') {
          flush();
          el.appendChild(p);
        } else {
          para.push(line.trim());
        }
        i++;
      }
    }
    flush();
  }

  function init(root) {
    if (root.__nsaReady) return;
    root.__nsaReady = true;

    var cfgEl = root.querySelector('[data-nsa-config]');
    var cfg;
    try {
      cfg = JSON.parse(cfgEl ? cfgEl.textContent : '{}');
    } catch (e) {
      return;
    }
    var t = cfg.i18n || {};

    // Small avatar beside bot messages (shown with --nsa-msg-avatar: flex): the image of the assistant or the bot icon.
    function avatar(el) {
      if (cfg.avatar) {
        var img = document.createElement('img');
        img.src = cfg.avatar;
        img.alt = '';
        el.appendChild(img);
      } else {
        var icon = document.createElement('span');
        icon.className = 'nsa-i nsa-i--bot';
        el.appendChild(icon);
      }
      el.setAttribute('aria-hidden', 'true');
    }

    var fab = root.querySelector('[data-nsa-toggle]');
    var panel = root.querySelector('[data-nsa-panel]');
    var closeBtn = root.querySelector('[data-nsa-close]');
    var gate = root.querySelector('[data-nsa-gate]');
    var body = root.querySelector('[data-nsa-body]');
    var log = root.querySelector('[data-nsa-log]');
    var input = root.querySelector('[data-nsa-input]');
    var sendBtn = root.querySelector('[data-nsa-send]');
    var micBtn = root.querySelector('[data-nsa-mic]');
    var micStatus = root.querySelector('[data-nsa-mic-status]');
    var inputbar = root.querySelector('[data-nsa-inputbar]');
    var escalate = root.querySelector('[data-nsa-escalate]');
    var escForm = root.querySelector('[data-nsa-esc-form]');
    var escEmail = root.querySelector('[data-nsa-esc-email]');
    var escMessage = root.querySelector('[data-nsa-esc-message]');
    var escCancel = root.querySelector('[data-nsa-esc-cancel]');
    var escError = root.querySelector('[data-nsa-esc-error]');
    var escNewsletter = root.querySelector('[data-nsa-esc-newsletter]');
    var escConsent = root.querySelector('[data-nsa-esc-consent]');
    var emailHint = root.querySelector('[data-nsa-email-hint]');
    var phoneInput = escForm ? escForm.querySelector('[data-nsa-esc-field="phone"]') : null;
    var ccBox = root.querySelector('[data-nsa-cc]');

    /* ---- Phone: country code (select box with filter) + national number -> E.164 ----
       Closed it is a button like a select box; open it shows a search field over the
       list (WAI-ARIA: button with aria-haspopup, combobox search with a listbox).
       Only listed countries can be chosen. */
    var phoneCfg = (cfg.form && cfg.form.phone) || null;
    var country = phoneCfg ? phoneCfg.country : '';
    var ccBtn = ccBox ? ccBox.querySelector('[data-nsa-cc-btn]') : null;
    var ccValue = ccBox ? ccBox.querySelector('[data-nsa-cc-value]') : null;
    var ccPop = ccBox ? ccBox.querySelector('[data-nsa-cc-pop]') : null;
    var ccSearch = ccBox ? ccBox.querySelector('[data-nsa-cc-search]') : null;
    var ccList = ccBox ? ccBox.querySelector('[data-nsa-cc-list]') : null;
    var ccItems = null;
    var ccActive = -1;

    function countryName(iso) {
      try { return new Intl.DisplayNames([cfg.lang || 'en'], { type: 'region' }).of(iso) || iso; } catch (e) { return iso; }
    }

    // The box shows code and calling code; its accessible name says the country.
    function ccShow(iso) {
      if (!ccBtn) return;
      ccValue.textContent = iso + ' +' + phoneCfg.dial[iso];
      ccBtn.setAttribute('aria-label', (t.escCountryCode || '') + ': ' + countryName(iso) + ' +' + phoneCfg.dial[iso]);
    }

    function ccAll() {
      if (ccItems) return ccItems;
      var collator = typeof Intl !== 'undefined' && Intl.Collator ? new Intl.Collator(cfg.lang || 'en') : null;
      ccItems = Object.keys(phoneCfg.dial).map(function (iso) { return { iso: iso, dial: phoneCfg.dial[iso], name: countryName(iso) }; });
      ccItems.sort(function (a, b) { return collator ? collator.compare(a.name, b.name) : (a.name < b.name ? -1 : 1); });
      // The preselected country first: most visitors keep it.
      ccItems.sort(function (a, b) { return (b.iso === phoneCfg.country) - (a.iso === phoneCfg.country); });
      return ccItems;
    }

    function ccFilter(query) {
      var q = String(query || '').toLowerCase().replace(/^\+|^00/, '').trim();
      if (!q) return ccAll();
      return ccAll().filter(function (c) {
        return c.name.toLowerCase().indexOf(q) !== -1 || c.iso.toLowerCase() === q || c.dial.indexOf(q) === 0;
      });
    }

    function ccRender(list) {
      ccList.textContent = '';
      ccActive = list.length ? 0 : -1;
      list.forEach(function (c, i) {
        var li = document.createElement('li');
        li.id = ccList.id + '-' + c.iso;
        li.setAttribute('role', 'option');
        li.setAttribute('data-iso', c.iso);
        li.setAttribute('aria-selected', c.iso === country ? 'true' : 'false');
        li.textContent = c.name + ' +' + c.dial;
        li.addEventListener('mousedown', function (e) { e.preventDefault(); ccChoose(c.iso); });
        ccList.appendChild(li);
        if (c.iso === country) ccActive = i;
      });
      ccMark();
    }

    function ccMark() {
      var items = ccList.querySelectorAll('[role="option"]');
      [].forEach.call(items, function (li, i) { li.classList.toggle('is-active', i === ccActive); });
      var active = items[ccActive];
      if (active) { ccSearch.setAttribute('aria-activedescendant', active.id); active.scrollIntoView({ block: 'nearest' }); }
      else ccSearch.removeAttribute('aria-activedescendant');
    }

    function ccOpen() {
      ccSearch.value = '';
      ccRender(ccAll());
      ccPop.hidden = false;
      ccBtn.setAttribute('aria-expanded', 'true');
      ccSearch.focus();
    }

    function ccClose(focusButton) {
      if (ccPop.hidden) return;
      ccPop.hidden = true;
      ccBtn.setAttribute('aria-expanded', 'false');
      if (focusButton) ccBtn.focus();
    }

    function ccChoose(iso) {
      country = iso;
      ccShow(iso);
      ccClose(false);
      if (phoneInput) phoneInput.focus();
    }

    if (phoneCfg && ccBtn && ccPop && ccSearch && ccList) {
      ccShow(country);
      ccBtn.addEventListener('click', function () { if (ccPop.hidden) ccOpen(); else ccClose(true); });
      ccBtn.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') { e.preventDefault(); ccOpen(); }
      });
      ccSearch.addEventListener('input', function () { ccRender(ccFilter(ccSearch.value)); });
      ccSearch.addEventListener('keydown', function (e) {
        var count = ccList.children.length;
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
          e.preventDefault();
          ccActive = e.key === 'ArrowDown' ? Math.min(count - 1, ccActive + 1) : Math.max(0, ccActive - 1);
          ccMark();
        } else if (e.key === 'Enter') {
          // Never submits the form from here.
          e.preventDefault();
          if (ccList.children[ccActive]) ccChoose(ccList.children[ccActive].getAttribute('data-iso'));
        } else if (e.key === 'Escape') {
          e.preventDefault();
          e.stopPropagation();
          ccClose(true);
        } else if (e.key === 'Tab') {
          ccClose(false);
        }
      });
      // A click elsewhere closes the list without changing the choice.
      document.addEventListener('mousedown', function (e) { if (!ccBox.contains(e.target)) ccClose(false); });
    }

    function digits(s) { return String(s || '').replace(/\D/g, ''); }

    // The longest calling code at the start of an international number; the
    // preselected or chosen country wins when several share the code (+1, +44 …).
    function countryOfNumber(intl) {
      for (var len = 3; len >= 1; len--) {
        var code = intl.slice(0, len);
        if (phoneCfg.dial[country] === code) return country;
        var hit = Object.keys(phoneCfg.dial).filter(function (iso) { return phoneCfg.dial[iso] === code; });
        if (hit.length) return hit.indexOf(phoneCfg.country) !== -1 ? phoneCfg.country : hit[0];
      }
      return '';
    }

    function trunkOf(iso) {
      if (phoneCfg.trunk && Object.prototype.hasOwnProperty.call(phoneCfg.trunk, iso)) return phoneCfg.trunk[iso];
      return phoneCfg.dial[iso] === '1' ? '1' : '0';
    }

    // National number without trunk prefix; international input moves the country.
    function nationalPart() {
      var raw = String(phoneInput.value || '').trim();
      if (/^(\+|00)/.test(raw)) {
        var intl = digits(raw.replace(/^00/, ''));
        var iso = countryOfNumber(intl);
        if (iso) {
          country = iso;
          ccShow(iso);
          return intl.slice(phoneCfg.dial[iso].length);
        }
        return intl;
      }
      var national = digits(raw);
      var trunk = trunkOf(country);
      // Only a real trunk prefix: RU/KZ "8" and +1 "1" only before a full-length number.
      if (trunk && national.indexOf(trunk) === 0 && (trunk === '0' || trunk === '06' || trunk === '80' || national.length === 11)) {
        national = national.slice(trunk.length);
      }
      return national;
    }

    function phoneE164() {
      if (!phoneInput || !phoneCfg || !String(phoneInput.value || '').trim()) return '';
      var national = nationalPart();
      return national ? '+' + phoneCfg.dial[country] + national : '';
    }

    if (phoneInput && phoneCfg) {
      // Input mask: digits and the usual separators only.
      phoneInput.addEventListener('input', function () {
        var clean = phoneInput.value.replace(/[^\d\s+()\/\-.]/g, '');
        if (clean !== phoneInput.value) phoneInput.value = clean;
      });
      // On leaving the field: the trunk prefix (usually the leading 0) or the typed
      // international prefix goes; the visitor's own spacing stays.
      phoneInput.addEventListener('blur', function () {
        var raw = phoneInput.value.trim();
        if (!raw) return;
        var drop = digits(raw).length - nationalPart().length;
        var out = '';
        for (var i = 0; i < raw.length; i++) {
          var ch = raw.charAt(i);
          if (drop > 0 && /\d/.test(ch)) { drop--; continue; }
          if (drop > 0 || (!out && /[\s+()\/\-.]/.test(ch))) continue;
          out += ch;
        }
        phoneInput.value = out.trim();
      });
    }

    /* ---- E-mail: hint for typos of well-known providers ---- */
    var MAIL_DOMAINS = ['gmail.com', 'googlemail.com', 'gmx.at', 'gmx.de', 'gmx.net', 'gmx.ch', 'gmx.com', 'web.de', 'outlook.com', 'outlook.de', 'hotmail.com', 'hotmail.de', 'live.com', 'live.at', 'live.de', 'msn.com', 'yahoo.com', 'yahoo.de', 'icloud.com', 'me.com', 'aon.at', 'a1.net', 'chello.at', 't-online.de', 'freenet.de', 'posteo.de', 'mailbox.org', 'proton.me', 'protonmail.com', 'mail.com', 'orange.fr', 'free.fr', 'libero.it', 'wp.pl', 'seznam.cz', 'freemail.hu', 'siol.net'];

    function distance(a, b) {
      var row = [];
      for (var j = 0; j <= b.length; j++) row[j] = j;
      for (var i = 1; i <= a.length; i++) {
        var prev = row[0];
        row[0] = i;
        for (var k = 1; k <= b.length; k++) {
          var tmp = row[k];
          row[k] = Math.min(row[k] + 1, row[k - 1] + 1, prev + (a.charAt(i - 1) === b.charAt(k - 1) ? 0 : 1));
          prev = tmp;
        }
      }
      return row[b.length];
    }

    function emailSuggestion(value) {
      var at = value.lastIndexOf('@');
      if (at < 1) return '';
      var domain = value.slice(at + 1).toLowerCase();
      if (domain.length < 5 || MAIL_DOMAINS.indexOf(domain) !== -1) return '';
      var best = '';
      var bestDist = 3;
      MAIL_DOMAINS.forEach(function (d) {
        var dist = distance(domain, d);
        if (dist < bestDist) { bestDist = dist; best = d; }
      });
      return best ? value.slice(0, at + 1) + best : '';
    }

    if (escEmail && emailHint) {
      escEmail.addEventListener('blur', function () {
        var suggestion = emailSuggestion((escEmail.value || '').trim());
        emailHint.textContent = '';
        emailHint.hidden = !suggestion;
        if (!suggestion) return;
        var parts = String(t.escEmailSuggest || '%email%').split('%email%');
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'nsa-linkbtn';
        btn.textContent = suggestion;
        btn.addEventListener('click', function () { escEmail.value = suggestion; emailHint.hidden = true; escEmail.focus(); });
        emailHint.appendChild(document.createTextNode(parts[0] || ''));
        emailHint.appendChild(btn);
        emailHint.appendChild(document.createTextNode(parts[1] || ''));
      });
    }
    var handoff = root.querySelector('[data-nsa-handoff]');

    var open = false;
    var seeded = false;
    var sending = false;
    var gaFired = false;
    var history = [];
    // Messages of the conversation the server gets with each new message.
    var HISTORY = 30;
    // Signed by the server: what the visitor proved in this conversation (guest check of a connection).
    var guest = '';
    // Encrypted by the server: tool results and loaded skills of earlier answers (unreadable here).
    var memory = '';

    /* ---- Session: the chat stays open across pages ----
       After consent, the conversation and whether the window is open are kept in
       sessionStorage (until the tab is closed); the next page opens the chat again
       at the same place. "New conversation" clears it. */
    var STORE = 'nsa:' + (cfg.agent || 0) + ':' + (cfg.lang || '');
    var transcript = [];
    function save() {
      if (!allowed()) return;
      try { sessionStorage.setItem(STORE, JSON.stringify({ v: 1, open: open, log: transcript.slice(-40), history: history, chips: chipsUsed, guest: guest, memory: memory })); } catch (e) { /* storage blocked */ }
    }
    function restore() {
      if (!allowed()) return null;
      try {
        var s = JSON.parse(sessionStorage.getItem(STORE) || 'null');
        return s && s.v === 1 && Array.isArray(s.log) ? s : null;
      } catch (e) { return null; }
    }
    // A suggestion was asked already: the chips below the greeting are gone (the button brings them back).
    var chipsUsed = false;
    var saved = restore();
    if (saved && Array.isArray(saved.history)) history = saved.history.slice(-HISTORY);
    if (saved && typeof saved.guest === 'string') guest = saved.guest;
    if (saved && typeof saved.memory === 'string') memory = saved.memory;
    if (saved) chipsUsed = !!saved.chips;

    // Required AI notice (EU AI Act, Art. 50): put back below the head if a template left it out.
    if (!panel.querySelector('[data-nsa-ai]') && t.aiNotice) {
      var aiNotice = document.createElement('p');
      aiNotice.className = 'nsa-ai';
      aiNotice.setAttribute('data-nsa-ai', '');
      aiNotice.textContent = t.aiNotice;
      var head = panel.querySelector('.nsa-head');
      panel.insertBefore(aiNotice, head ? head.nextSibling : panel.firstChild);
    }

    /* ---- Credit line (shared script of the Basic bundle) ---- */
    // The licence code is part of this file, not of the configuration.
    var credit = null;
    var creditBlocked = false;
    var notice = null;
    function blockInput(on, message) {
      creditBlocked = on;
      if (input) input.disabled = on;
      if (handoff) handoff.disabled = on;
      if (micBtn) micBtn.disabled = on;
      if (on) setEscalating(false);
      syncSend();
      if (on && !notice && body) {
        notice = document.createElement('p');
        notice.className = 'nsa-notice';
        notice.setAttribute('role', 'status');
        body.insertBefore(notice, inputbar || null);
      }
      if (notice) {
        notice.textContent = on ? message : '';
        notice.hidden = !on;
      }
    }
    function useCredit(api) {
      api.attach(panel, { module: 'superagent', proof: cfg.proof || null, lang: cfg.lang, onBlock: blockInput })
        .then(function (c) {
          credit = c;
          if (creditBlocked && !c.isBlocked()) blockInput(false, '');
        });
    }
    if (window.NeoCredit) {
      useCredit(window.NeoCredit);
    } else {
      document.addEventListener('neo:credit-ready', function () { useCredit(window.NeoCredit); }, { once: true });
      // Without the shared script the chat takes no input.
      setTimeout(function () { if (!credit) blockInput(true, t.error); }, 5000);
    }

    // Consent of the visitor for a category, from the consent tool of the page:
    // window.cookieConsent.allows(category) (the cookie consent extension of this
    // family, or a tool with the same interface; its list "categories" names what
    // its banner asks for), else window.superAgentConsent(category) of any other tool
    // (true, false, or null when it never asks for the category; documentation,
    // "Customising"). 'granted', 'denied', 'never' (the tool will not ask for it)
    // or 'none' (no consent tool on the page).
    function consentState(cat) {
      var cc = window.cookieConsent;
      if (cc && typeof cc.allows === 'function') {
        try { if (cc.allows(cat)) return 'granted'; } catch (e) { return 'denied'; }
        return Array.isArray(cc.categories) && cc.categories.indexOf(cat) < 0 ? 'never' : 'denied';
      }
      if (typeof window.superAgentConsent === 'function') {
        var v;
        try { v = window.superAgentConsent(cat); } catch (e) { return 'denied'; }
        if (v === null) return 'never';
        return v ? 'granted' : 'denied';
      }
      return 'none';
    }

    // The chat for the category of the assistant (cfg.consent; empty = no consent
    // needed). A page without a consent tool gets the chat right away.
    function allowed() {
      if (!cfg.consent) return true;
      var state = consentState(cfg.consent);
      return state === 'granted' || state === 'none';
    }

    // The consent tool never asks for the category: the chat can never start and says so.
    function unavailable() {
      return !!cfg.consent && consentState(cfg.consent) === 'never';
    }

    // Statistics consent for the GA key event, a purpose of its own: only with a
    // consent tool that grants it.
    function statsConsent() {
      return consentState('statistics') === 'granted';
    }

    // The text for the gate when the chat is not available (added to templates without it).
    var gateUnavailable = gate ? gate.querySelector('[data-nsa-gate-unavailable]') : null;
    if (gate && !gateUnavailable) {
      gateUnavailable = document.createElement('p');
      gateUnavailable.className = 'nsa-gate__unavailable';
      gateUnavailable.setAttribute('data-nsa-gate-unavailable', '');
      gateUnavailable.setAttribute('tabindex', '-1');
      gateUnavailable.textContent = t.gateUnavailable || '';
      gateUnavailable.hidden = true;
      gate.appendChild(gateUnavailable);
    }
    var warned = false;

    /* ---- View ---- */
    // Where the focus goes back when the window closes: the element that had it
    // when the window opened (e.g. the settings button of the buttons at the edge
    // of the screen of NEO Basic, whose menu entry opened the chat while the chat
    // button is hidden), else the chat button.
    var returnTo = null;
    function visible(el) {
      return !!el && document.contains(el) && el.getClientRects().length > 0 && getComputedStyle(el).visibility !== 'hidden';
    }
    function setOpen(next) {
      if (!next) stopRecording(true);
      var was = open;
      // Check before hiding: setting hidden blurs the active element at once.
      var hadFocus = panel.contains(document.activeElement);
      if (next && !was) {
        var active = document.activeElement;
        returnTo = active && active !== document.body && !root.contains(active) ? active : null;
      }
      open = next;
      panel.hidden = !open;
      fab.setAttribute('aria-expanded', open ? 'true' : 'false');
      fab.classList.toggle('is-open', open);
      save();
      if (open) {
        render();
        if (credit) setTimeout(credit.check, 400);
        var focusable = allowed() ? input : (unavailable() ? gateUnavailable : panel.querySelector('[data-nsa-consent]'));
        if (focusable) setTimeout(function () { focusable.focus(); }, 30);
      } else if (was && hadFocus) {
        // Focus back instead of losing it in the hidden panel.
        (visible(returnTo) ? returnTo : fab).focus();
      }
      if (!open) returnTo = null;
    }

    function render() {
      var ok = allowed();
      var never = !ok && unavailable();
      if (gate) {
        gate.hidden = ok;
        // Not available: only the icon and the reason; otherwise the consent request.
        [].forEach.call(gate.children, function (el) {
          if (el === gateUnavailable) el.hidden = !never;
          else if (!el.classList.contains('nsa-gate__icon')) el.hidden = never;
        });
      }
      if (never && !warned) {
        warned = true;
        if (window.console) console.warn('SuperAgent: the consent tool of this page never asks for the category "' + cfg.consent + '", so the chat is not available. Add a service in this category to the consent tool (NEO Cookie Consent: the service "AI chat assistant") or change the consent category of the assistant.');
      }
      if (body) body.hidden = !ok;
      if (ok && !seeded) seed();
    }

    function seed() {
      seeded = true;
      if (saved && saved.log.length) {
        saved.log.forEach(function (m) {
          var b = addMessage(m.from, m.text);
          if (m.rejected) b.parentNode.classList.add('nsa-msg--rejected');
          if (m.nav) showNav(m.nav, false);
        });
        saved = null;
        // Restored conversation: the chips stay below the greeting until one was asked.
        var greeting = log.querySelector('.nsa-msg');
        if (!chipsUsed && greeting) insertChips(greeting.nextSibling);
        return;
      }
      addMessage('bot', cfg.greeting || '');
      insertChips(null);
      scroll();
    }

    /* ---- Suggested questions ----
       Below the greeting until the visitor asks one of them (also after a page
       change); the button "Suggestions" shows them again at the end of the
       conversation at any time, never twice. */
    function chipBlock() {
      var list = cfg.suggestions || [];
      if (!list.length) return null;
      var wrap = document.createElement('div');
      wrap.className = 'nsa-suggest';
      wrap.setAttribute('data-nsa-suggest', '');
      wrap.setAttribute('role', 'group');
      wrap.setAttribute('aria-label', t.suggestions || '');
      list.forEach(function (s) {
        var chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'nsa-chip';
        chip.textContent = s;
        chip.addEventListener('click', function () {
          chipsUsed = true;
          dropSuggestions();
          send(s);
          // The chip is gone: the focus goes to the input instead of the page.
          if (input) input.focus();
        });
        wrap.appendChild(chip);
      });
      return wrap;
    }

    function insertChips(before) {
      var block = chipBlock();
      if (block) log.insertBefore(block, before);
      return block;
    }

    function dropSuggestions() {
      [].slice.call(log.querySelectorAll('[data-nsa-suggest]')).forEach(function (s) { s.remove(); });
    }

    var suggestBtn = root.querySelector('[data-nsa-suggestions]');
    if (!suggestBtn && body && inputbar && (cfg.suggestions || []).length) {
      // A template without the button: put it above the input row.
      suggestBtn = document.createElement('button');
      suggestBtn.type = 'button';
      suggestBtn.className = 'nsa-suggest-btn';
      suggestBtn.setAttribute('data-nsa-suggestions', '');
      suggestBtn.textContent = t.suggestions || '';
      inputbar.parentNode.insertBefore(suggestBtn, inputbar);
    }
    if (suggestBtn) {
      if (!(cfg.suggestions || []).length) {
        suggestBtn.hidden = true;
      } else {
        suggestBtn.addEventListener('click', function () {
          dropSuggestions();
          var block = insertChips(null);
          scroll();
          if (block && block.firstChild) block.firstChild.focus();
        });
        // The button is only there when it can do something: while the questions are the
        // last thing in the conversation they are in view already, and a click would change
        // nothing a visitor can see.
        var syncSuggestBtn = function () {
          var last = log.lastElementChild;
          suggestBtn.hidden = !!(last && last.hasAttribute('data-nsa-suggest'));
        };
        if (window.MutationObserver) new MutationObserver(syncSuggestBtn).observe(log, { childList: true });
        syncSuggestBtn();
      }
    }

    /* ---- Messages ---- */
    function addMessage(from, text) {
      var msg = document.createElement('div');
      msg.className = 'nsa-msg nsa-msg--' + (from === 'user' ? 'user' : 'bot');
      if (from !== 'user') {
        var av = document.createElement('span');
        av.className = 'nsa-msg__av';
        avatar(av);
        msg.appendChild(av);
      }
      var bubble = document.createElement('div');
      bubble.className = 'nsa-bubble';
      if (from === 'user') {
        bubble.textContent = text;
      } else {
        // Replies are Markdown (renderMarkdown); a table may use the whole width of the row.
        bubble.classList.add('nsa-md');
        renderMarkdown(bubble, text, 0);
        if (bubble.querySelector('table')) bubble.classList.add('nsa-bubble--table');
      }
      msg.appendChild(bubble);
      log.appendChild(msg);
      transcript.push({ from: from === 'user' ? 'user' : 'bot', text: text });
      scroll();
      save();
      return bubble;
    }

    // Button below a reply that opens a page of this website; with now, the page opens at once.
    function showNav(nav, now) {
      var target;
      try { target = new URL(nav.url, location.href); } catch (e) { return; }
      if (target.host !== location.host) return;
      var a = document.createElement('a');
      a.className = 'nsa-nav';
      a.href = target.href;
      a.textContent = (t.openPage || '%title%').replace('%title%', nav.title || '');
      log.appendChild(a);
      if (transcript.length) transcript[transcript.length - 1].nav = { url: target.href, title: nav.title || '' };
      scroll();
      save();
      if (now && target.href !== location.href) setTimeout(function () { save(); location.assign(target.href); }, 1200);
    }

    // Download of a file a tool delivered (e.g. an invoice): once, within 15 minutes,
    // so the button is not kept with the conversation and becomes text once used.
    function showFiles(files) {
      files.forEach(function (file) {
        var target;
        try { target = new URL(String(file.url || ''), location.href); } catch (e) { return; }
        if (target.host !== location.host) return;
        var name = String(file.name || '');
        var a = document.createElement('a');
        a.className = 'nsa-file';
        a.href = target.href;
        a.setAttribute('download', name);
        a.textContent = (t.fileDownload || '%file%').replace('%file%', name);
        a.addEventListener('click', function () {
          // After the browser has taken the address: the link is spent.
          setTimeout(function () {
            var done = document.createElement('span');
            done.className = 'nsa-file nsa-file--used';
            done.textContent = (t.fileUsed || '%file%').replace('%file%', name);
            var focused = document.activeElement === a;
            a.replaceWith(done);
            // Keyboard users stay where they were instead of landing at the top of the page.
            if (focused) { done.tabIndex = -1; done.focus(); }
          }, 0);
        });
        log.appendChild(a);
      });
      scroll();
    }

    function showTyping() {
      var msg = document.createElement('div');
      msg.className = 'nsa-msg nsa-msg--bot';
      msg.setAttribute('data-nsa-typing', '');
      var av = document.createElement('span');
      av.className = 'nsa-msg__av';
      avatar(av);
      var bubble = document.createElement('div');
      bubble.className = 'nsa-bubble nsa-typing';
      bubble.innerHTML = '<span class="nsa-dot"></span><span class="nsa-dot"></span><span class="nsa-dot"></span><span class="nsa-typing__text" data-nsa-step></span>';
      msg.appendChild(av);
      msg.appendChild(bubble);
      log.appendChild(msg);
      setStep(msg, 'thinking');
      scroll();
      return msg;
    }

    /* What the assistant is doing right now (thinking, pages, form, search), in the bubble. */
    function setStep(typing, step) {
      var el = typing.querySelector('[data-nsa-step]');
      var key = 'status' + String(step || 'thinking').charAt(0).toUpperCase() + String(step || 'thinking').slice(1);
      if (el && t[key]) el.textContent = t[key];
    }

    /* Reads the event stream of the chat endpoint: "step" events update the status,
       "say" events are what the assistant says while its tools run (shown at once),
       "done" or "error" carries the answer. Without a readable stream (old browsers,
       hosting that buffers) the events are read at the end; the result is the same. */
    function readEvents(r, onStep, onSay) {
      var result = null;
      function handle(block) {
        var ev = 'message';
        var data = '';
        block.split('\n').forEach(function (line) {
          if (line.indexOf('event:') === 0) ev = line.slice(6).trim();
          else if (line.indexOf('data:') === 0) data += line.slice(5).trim();
        });
        var obj = {};
        try { obj = JSON.parse(data || '{}') || {}; } catch (e) { obj = {}; }
        if (ev === 'step') onStep(obj.step);
        else if (ev === 'say') onSay(obj.text);
        else if (ev === 'done' || ev === 'error') { obj.ok = ev === 'done'; result = obj; }
      }
      function parse(buffer) {
        var parts = buffer.replace(/\r/g, '').split('\n\n');
        var rest = parts.pop();
        parts.forEach(function (p) { if (p.trim()) handle(p); });
        return rest;
      }
      if (!r.body || !r.body.getReader || typeof TextDecoder === 'undefined') {
        return r.text().then(function (text) { parse(text + '\n\n'); return result || { ok: false }; });
      }
      var reader = r.body.getReader();
      var decoder = new TextDecoder();
      var buffer = '';
      function pump() {
        return reader.read().then(function (chunk) {
          if (chunk.done) { parse(buffer + '\n\n'); return result || { ok: false }; }
          buffer = parse(buffer + decoder.decode(chunk.value, { stream: true }));
          return pump();
        });
      }
      return pump();
    }

    // A notice of the window (e.g. the AI service did not answer): not a message of
    // the assistant, not kept in the conversation.
    function addNotice(text) {
      var p = document.createElement('p');
      p.className = 'nsa-msg-notice';
      p.setAttribute('role', 'status');
      p.textContent = text;
      log.appendChild(p);
      scroll();
    }

    function scroll() { log.scrollTop = log.scrollHeight; }

    /* ---- Notice before personal data goes to the AI service ----
       Privacy mode off: when the server finds an e-mail address, phone number,
       IBAN, card number, social security number or date of birth in a message, it
       calls no AI service and answers with the kinds found ("confirm"). The
       message goes back into the input, the notice takes the place of the
       conversation; "Yes, send" sends it again with the confirmation, "Edit
       message" (or Escape) returns to the input. Every message is asked anew. */
    var pii = root.querySelector('[data-nsa-pii]');
    var piiFound = pii ? pii.querySelector('[data-nsa-pii-found]') : null;
    var piiSendBtn = pii ? pii.querySelector('[data-nsa-pii-send]') : null;
    var piiEditBtn = pii ? pii.querySelector('[data-nsa-pii-edit]') : null;
    var piiText = null;
    var PII_LABELS = { email: 'piiEmail', phone: 'piiPhone', iban: 'piiIban', card: 'piiCard', svnr: 'piiSvnr', birthdate: 'piiBirthdate' };

    function showPii(types, text) {
      if (!pii) return false;
      piiText = text;
      var names = types.map(function (k) { return t[PII_LABELS[k]] || k; }).join(', ');
      if (piiFound) piiFound.textContent = (t.piiFound || '%types%').replace('%types%', names);
      log.hidden = true;
      pii.hidden = false;
      if (inputbar) inputbar.inert = true;
      if (piiEditBtn) piiEditBtn.focus();
      return true;
    }

    function hidePii() {
      if (!pii) return;
      piiText = null;
      pii.hidden = true;
      log.hidden = false;
      if (inputbar) inputbar.inert = false;
      scroll();
    }

    function editPii() {
      hidePii();
      input.focus();
      try { input.setSelectionRange(input.value.length, input.value.length); } catch (e) { /* not a text field */ }
    }

    if (pii) {
      if (piiEditBtn) piiEditBtn.addEventListener('click', editPii);
      if (piiSendBtn) piiSendBtn.addEventListener('click', function () {
        var text = piiText;
        hidePii();
        input.value = '';
        syncSend();
        if (text) send(text, true);
      });
      pii.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
          e.preventDefault();
          e.stopPropagation();
          editPii();
          return;
        }
        // Focus stays in the notice (aria-modal): from the last control to the first and back.
        if (e.key !== 'Tab') return;
        var focusable = [].slice.call(pii.querySelectorAll('a[href], button:not([disabled])'));
        if (!focusable.length) return;
        var first = focusable[0], last = focusable[focusable.length - 1];
        if (e.shiftKey && document.activeElement === first) { last.focus(); e.preventDefault(); }
        else if (!e.shiftKey && document.activeElement === last) { first.focus(); e.preventDefault(); }
      });
    }

    /* ---- Send ---- */
    function send(text, confirmed) {
      var value = (text != null ? text : input.value).trim();
      if (!value || sending || !credit || creditBlocked) return;
      // GA4 key event: the first user message of this conversation counts once.
      // Double gate: gtag must exist AND statistics consent must be granted —
      // gtag can also be loaded marketing-only (Ads), which must not count here.
      if (!gaFired && cfg.gaEvent && typeof window.gtag === 'function' && statsConsent()) {
        try { window.gtag('event', cfg.gaEvent); gaFired = true; } catch (e) { /* GA must never block the chat */ }
      }
      var mine = addMessage('user', value);
      // Privacy mode, voice input: the markers of the transcript go to the server (see withMarks).
      var outgoing = withMarks(value);
      input.value = '';
      syncSend();
      sending = true;
      var typing = showTyping();
      // What the assistant said while its tools ran: own messages above the typing bubble.
      var said = [];
      function say(text) {
        text = String(text || '').trim();
        if (!text) return;
        addMessage('bot', text);
        if (typing.parentNode) log.appendChild(typing);
        said.push(text);
        scroll();
      }

      fetch(cfg.endpoints.chat, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'Accept': 'text/event-stream, application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ message: outgoing, confirmed: confirmed === true, guest: guest, memory: memory, history: history, lang: cfg.lang, agent: cfg.agent, page: { url: location.pathname + location.search, title: document.title, root: cfg.root || 0 } })
      })
        .then(function (r) {
          // Errors before the answer (rate limit, no assistant) come as JSON; the answer as a stream.
          if ((r.headers.get('Content-Type') || '').indexOf('text/event-stream') !== -1) {
            return readEvents(r, function (step) { setStep(typing, step); scroll(); }, say);
          }
          return r.json().catch(function () { return {}; }).then(function (data) { data = data || {}; data.ok = r.ok; return data; });
        })
        .then(function (data) {
          typing.remove();
          // Without a stream the texts said while the tools ran come with the answer.
          if (!said.length && Array.isArray(data.interim)) data.interim.forEach(say);
          // Personal data found, nothing sent to the AI service: the message leaves the
          // conversation again and waits in the input until the visitor decides.
          if (data.ok && Array.isArray(data.confirm) && data.confirm.length && pii) {
            mine.parentNode.remove();
            for (var j = transcript.length - 1; j >= 0; j--) {
              if (transcript[j].from === 'user') { transcript.splice(j, 1); break; }
            }
            save();
            input.value = value;
            syncSend();
            showPii(data.confirm, value);
            return;
          }
          // Privacy mode removed personal data before the AI call: the browser keeps
          // that version too, never the original.
          if (typeof data.message === 'string' && data.message) {
            // The model keeps its markers; the visitor sees "[removed]" in the page language.
            outgoing = data.message;
            var shown = outgoing.replace(/\[removed: [^\]]*\]/g, '[' + (t.removed || '…') + ']');
            mine.textContent = shown;
            for (var i = transcript.length - 1; i >= 0; i--) {
              if (transcript[i].from === 'user') { transcript[i].text = shown; break; }
            }
            save();
          }
          // No answer from the AI service: a notice of the window, not a reply of the assistant.
          if (!data.ok || !data.reply) {
            addNotice(t.error);
            return;
          }
          var reply = data.reply;
          if (typeof data.guest === 'string') guest = data.guest;
          if (typeof data.memory === 'string') memory = data.memory;
          addMessage('bot', reply);
          if (data.navigate && data.navigate.url) showNav(data.navigate, !!data.navigate.now);
          if (Array.isArray(data.files) && data.files.length) showFiles(data.files);
          history.push({ role: 'user', content: outgoing });
          // One answer, with what was said while the tools ran: the history keeps more turns.
          history.push({ role: 'assistant', content: said.concat([reply]).join('\n\n') });
          if (history.length > HISTORY) history = history.slice(-HISTORY);
          // The assistant cannot send anything itself: it opens the forwarding
          // form, prefilled — the visitor checks it, adds the e-mail and sends.
          if (data && data.handoff && escForm) {
            if (escMessage && !escMessage.value.trim()) escMessage.value = String(data.handoff);
            setEscalating(true);
          }
        })
        .catch(function () {
          typing.remove();
          addNotice(t.error);
        })
        .finally(function () { sending = false; });
    }

    function syncSend() {
      if (sendBtn) sendBtn.disabled = creditBlocked || !input.value.trim();
    }

    /* ---- Voice input (edition "pro") ----
       The recording goes to the transcription endpoint; the text lands in the
       input field and the visitor checks and sends it. At most 60 seconds; the
       server keeps nothing. While recording the input row shows the stop button,
       a level wave and the time; after stopping a busy state until the text is
       back. The status line below announces start, stop and errors once (the
       running time is not announced every second). */
    var recorder = null;
    var recTimer = null;
    var recStart = 0;
    var recDiscard = false;
    var recLevels = [];
    var recAudio = null;
    var recFrame = 0;
    var micStatusText = micStatus ? micStatus.querySelector('[data-nsa-mic-text]') : null;
    var micLive = micStatus ? micStatus.querySelector('[data-nsa-mic-live]') : null;
    var recBox = root.querySelector('[data-nsa-rec]');
    var recTime = root.querySelector('[data-nsa-rec-time]');
    var busyBox = root.querySelector('[data-nsa-busy]');
    var wave = root.querySelector('[data-nsa-wave]');
    var BARS = 32;
    var bars = [];
    if (wave) {
      for (var w = 0; w < BARS; w++) bars.push(wave.appendChild(document.createElement('span')));
    }

    function micSupported() {
      return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia && window.MediaRecorder);
    }

    function micType() {
      var types = ['audio/webm;codecs=opus', 'audio/webm', 'audio/mp4', 'audio/ogg;codecs=opus', 'audio/ogg'];
      for (var i = 0; i < types.length; i++) {
        if (window.MediaRecorder.isTypeSupported && MediaRecorder.isTypeSupported(types[i])) return types[i];
      }
      return '';
    }

    function stillMotion() {
      return (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) || document.documentElement.hasAttribute('data-a11y-motion');
    }

    function mmss(sec) {
      return Math.floor(sec / 60) + ':' + (sec % 60 < 10 ? '0' : '') + (sec % 60);
    }

    // The icons are <svg> elements, which have no hidden property: the attribute is set.
    function micIcon(name) {
      if (!micBtn) return;
      [].slice.call(micBtn.querySelectorAll('[data-nsa-mic-icon]')).forEach(function (svg) {
        svg.toggleAttribute('hidden', svg.getAttribute('data-nsa-mic-icon') !== name);
      });
    }

    // idle | recording | busy: the row, the button (icon, name, pressed, busy) and the input.
    function micState(state) {
      if (!micBtn) return;
      var recording = state === 'recording';
      var busy = state === 'busy';
      micBtn.classList.toggle('is-recording', recording);
      micBtn.classList.toggle('is-busy', busy);
      micBtn.setAttribute('aria-pressed', recording ? 'true' : 'false');
      micBtn.setAttribute('aria-label', recording ? (t.micStop || '') : (busy ? (t.micBusy || '') : (t.mic || '')));
      micBtn.toggleAttribute('aria-disabled', busy);
      micBtn.disabled = creditBlocked;
      micIcon(recording ? 'stop' : (busy ? 'busy' : 'mic'));
      if (inputbar) {
        inputbar.setAttribute('data-nsa-voice', state);
        inputbar.toggleAttribute('aria-busy', busy);
      }
      if (input) input.hidden = recording || busy;
      if (sendBtn) sendBtn.hidden = recording || busy;
      if (recBox) recBox.hidden = !recording;
      if (busyBox) busyBox.hidden = !busy;
    }

    // Visible text of the status line and, separately, what is announced (once).
    function micSay(text, announce) {
      if (!micStatus) return;
      if (!micStatusText || !micLive) {
        // A template of an earlier version: one element with role=status.
        micStatus.textContent = text || announce || '';
        micStatus.hidden = !micStatus.textContent;
        return;
      }
      micStatusText.textContent = text || '';
      micStatus.classList.toggle('has-text', !!text);
      if (announce !== undefined) micLive.textContent = announce || '';
    }

    function recordingText(time) {
      return (t.micRecording || '%time%').replace('%time%', time);
    }

    function micTick() {
      var sec = Math.floor((Date.now() - recStart) / 1000);
      if (recTime) recTime.textContent = mmss(sec);
      micSay(recordingText(mmss(sec)));
      if (sec >= 60) stopRecording(false);
    }

    function setBars(levels) {
      bars.forEach(function (bar, i) { bar.style.height = Math.round(18 + (levels[i] || 0) * 82) + '%'; });
    }

    // Live level of the microphone (AnalyserNode); still bars with reduced motion.
    function levels(stream) {
      recLevels = [];
      setBars(bars.map(function () { return 0.08; }));
      if (!bars.length) return;
      try {
        var AC = window.AudioContext || window.webkitAudioContext;
        recAudio = new AC();
        var analyser = recAudio.createAnalyser();
        analyser.fftSize = 256;
        recAudio.createMediaStreamSource(stream).connect(analyser);
        var buf = new Uint8Array(analyser.fftSize);
        var last = 0;
        (function loop(ts) {
          if (!recorder) return;
          recFrame = requestAnimationFrame(loop);
          if (ts - last < 110) return;
          last = ts;
          analyser.getByteTimeDomainData(buf);
          var peak = 0;
          for (var i = 0; i < buf.length; i++) peak = Math.max(peak, Math.abs(buf[i] - 128));
          recLevels.push(Math.min(1, 0.08 + peak / 60));
          if (stillMotion()) { setBars(bars.map(function () { return 0.5; })); return; }
          setBars(bars.map(function (b, i) { return recLevels[recLevels.length - BARS + i] || 0.08; }));
        })(0);
      } catch (e) { /* no wave, the recording goes on */ }
    }

    function stopLevels() {
      cancelAnimationFrame(recFrame);
      if (recAudio) { try { recAudio.close(); } catch (e) { /* closed already */ } recAudio = null; }
    }

    function startRecording() {
      if (recorder || sending || creditBlocked || !allowed()) return;
      navigator.mediaDevices.getUserMedia({ audio: true }).then(function (stream) {
        var type = micType();
        var chunks = [];
        recorder = new MediaRecorder(stream, type ? { mimeType: type } : undefined);
        recDiscard = false;
        recorder.ondataavailable = function (e) { if (e.data && e.data.size) chunks.push(e.data); };
        recorder.onstop = function () {
          stream.getTracks().forEach(function (track) { track.stop(); });
          var mime = (recorder && recorder.mimeType) || type || 'audio/webm';
          recorder = null;
          stopLevels();
          if (recDiscard || !chunks.length) { micState('idle'); micSay('', ''); return; }
          transcribe(new Blob(chunks, { type: mime }));
        };
        recorder.start();
        recStart = Date.now();
        micState('recording');
        if (recTime) recTime.textContent = mmss(0);
        micSay(recordingText(mmss(0)), recordingText('').replace(/\s+/g, ' ').trim());
        recTimer = setInterval(micTick, 1000);
        levels(stream);
      }).catch(function () {
        micState('idle');
        micSay(t.micError, t.micError);
      });
    }

    function stopRecording(discard) {
      clearInterval(recTimer);
      if (!recorder || recorder.state === 'inactive') return;
      recDiscard = !!discard;
      recorder.stop();
    }

    /* Privacy mode: the server returns the transcript with markers such as
       "[removed: e-mail address]". The input shows them as a sent message does
       ("[removed]" in the page language); the message to the server gets the
       markers back (voiceMarks), so the model reads them as for a typed message. */
    var voiceMarks = [];
    var MARK = /\[removed: [^\]]*\]/g;
    function shownMark() { return '[' + (t.removed || '…') + ']'; }
    function withMarks(text) {
      if (!voiceMarks.length) return text;
      var marks = voiceMarks.slice();
      voiceMarks = [];
      return text.split(shownMark()).reduce(function (out, part, i) {
        return i === 0 ? part : out + (marks.length ? marks.shift() : shownMark()) + part;
      }, '');
    }

    function transcribe(blob) {
      micState('busy');
      micSay('', t.micBusy || '');
      var form = new FormData();
      form.append('audio', blob, 'recording');
      form.append('agent', String(cfg.agent || 0));
      form.append('lang', cfg.lang || '');
      fetch(cfg.endpoints.transcribe, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: form
      })
        .then(function (r) { return r.ok ? r.json() : Promise.reject(r); })
        .then(function (data) {
          var text = data && typeof data.text === 'string' ? data.text.trim() : '';
          if (!text) return Promise.reject();
          var marks = text.match(MARK) || [];
          if (marks.length) {
            voiceMarks = voiceMarks.concat(marks);
            text = text.replace(MARK, shownMark());
          }
          micState('idle');
          input.value = (input.value.trim() ? input.value.trim() + ' ' : '') + text;
          syncSend();
          micSay('', '');
          input.focus();
        })
        .catch(function () {
          micState('idle');
          micSay(t.micError, t.micError);
          if (input) input.focus();
        });
    }

    if (micBtn) {
      if (!micSupported() || !cfg.endpoints.transcribe) {
        micBtn.hidden = true;
      } else {
        micState('idle');
        micBtn.addEventListener('click', function () {
          if (micBtn.hasAttribute('aria-disabled')) return;
          if (recorder && recorder.state === 'recording') stopRecording(false);
          else startRecording();
        });
      }
    }

    /* ---- Escalation ---- */
    function setEscalating(on) {
      if (!escalate) return;
      escalate.hidden = !on;
      if (inputbar) inputbar.hidden = on;
      if (handoff) handoff.hidden = on;
      // "Please do not enter personal data" is about chat messages; the form asks for them on purpose.
      var privacyHint = root.querySelector('[data-nsa-privacy]');
      if (privacyHint) privacyHint.hidden = on;
      if (on && escEmail) setTimeout(function () { escEmail.focus(); }, 30);
    }

    if (handoff) handoff.addEventListener('click', function () { setEscalating(true); });
    if (escCancel) escCancel.addEventListener('click', function () { setEscalating(false); });

    /* Field errors: the field gets aria-invalid, the text goes to the status line. */
    function escFields() {
      return [].slice.call(escForm.querySelectorAll('input[required], textarea[required], [data-nsa-esc-field]'));
    }

    function showEscError(fields, text) {
      escFields().forEach(function (f) { f.removeAttribute('aria-invalid'); });
      if (!escError) return;
      escError.hidden = !text;
      escError.textContent = text || '';
      fields.forEach(function (f) { f.setAttribute('aria-invalid', 'true'); });
      if (fields[0]) fields[0].focus();
    }

    function byName(name) {
      if (name === 'email') return escEmail;
      if (name === 'message') return escMessage;
      if (name === 'consent') return escConsent;
      return escForm.querySelector('[data-nsa-esc-field="' + name + '"]');
    }

    if (escForm) {
      escForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!credit || creditBlocked) return;
        var email = (escEmail.value || '').trim();
        var message = (escMessage.value || '').trim();
        var missing = escFields().filter(function (f) { return f.required && (f.type === 'checkbox' ? !f.checked : !(f.value || '').trim()); });
        if (missing.length) { showEscError(missing, t.escMissing); return; }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showEscError([escEmail], t.escEmailInvalid || t.escMissing); return; }
        var phone = phoneE164();
        if (phoneInput && phoneInput.value.trim() && !/^\+[1-9]\d{6,14}$/.test(phone)) { showEscError([phoneInput], t.escPhoneInvalid); return; }
        showEscError([], '');

        var payload = { email: email, message: message, transcript: history, lang: cfg.lang, agent: cfg.agent, page: { url: location.pathname + location.search, title: document.title, root: cfg.root || 0 } };
        escForm.querySelectorAll('[data-nsa-esc-field]').forEach(function (f) { payload[f.getAttribute('data-nsa-esc-field')] = (f.value || '').trim(); });
        if (phoneInput) payload.phone = phone;
        if (escNewsletter) payload.newsletter = escNewsletter.checked;
        if (escConsent) payload.consent = escConsent.checked;
        var submit = escForm.querySelector('button[type="submit"]');
        if (submit) submit.disabled = true;

        fetch(cfg.endpoints.escalate, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          body: JSON.stringify(payload)
        })
          .then(function (r) {
            if (r.ok) return r.json();
            // The server checks the fields too: name them like the client check does.
            return r.json().catch(function () { return {}; }).then(function (data) {
              if (r.status === 400 && data && data.fields) {
                var fields = data.fields.map(byName).filter(Boolean);
                if (submit) submit.disabled = false;
                var text = t.escMissing;
                if (data.error === 'invalid' && data.fields.indexOf('phone') !== -1) text = t.escPhoneInvalid;
                if (data.error === 'invalid' && data.fields.indexOf('email') !== -1) text = t.escEmailInvalid || t.escMissing;
                showEscError(fields, text);
                return Promise.reject('fields');
              }
              return Promise.reject(r);
            });
          })
          .then(function () {
            if (escNewsletter) escNewsletter.checked = false;
            if (escConsent) escConsent.checked = false;
            // Back to the chat: the confirmation becomes a message in the log
            // (announced by its live region), the form closes and stays usable,
            // and the assistant learns that the request really was sent.
            if (submit) submit.disabled = false;
            escMessage.value = '';
            setEscalating(false);
            addMessage('bot', t.escDone);
            history.push({ role: 'assistant', content: t.escDone });
            if (history.length > HISTORY) history = history.slice(-HISTORY);
            if (input) setTimeout(function () { input.focus(); }, 30);
          })
          .catch(function (reason) {
            if (reason === 'fields') return;
            if (submit) submit.disabled = false;
            addNotice(t.error);
            setEscalating(false);
          });
      });
    }

    /* ---- Wiring ---- */
    var newChat = root.querySelector('[data-nsa-new]');
    if (newChat) {
      newChat.addEventListener('click', function () {
        try { sessionStorage.removeItem(STORE); } catch (e) { /* storage blocked */ }
        transcript = [];
        history = [];
        guest = '';
        memory = '';
        saved = null;
        chipsUsed = false;
        voiceMarks = [];
        log.textContent = '';
        setEscalating(false);
        seeded = false;
        render();
        if (input) input.focus();
      });
    }

    fab.addEventListener('click', function () { setOpen(!open); });
    if (closeBtn) closeBtn.addEventListener('click', function () { setOpen(false); });

    if (input) {
      input.addEventListener('input', function () {
        syncSend();
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 96) + 'px';
      });
      input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
      });
    }
    if (sendBtn) sendBtn.addEventListener('click', function () { send(); });

    // The consent button: NEO Cookie Consent opens its settings itself (data-ncc-open);
    // another consent tool listens to this event and opens its own.
    var consentBtn = root.querySelector('[data-nsa-consent]');
    if (consentBtn) {
      consentBtn.addEventListener('click', function () {
        try { window.dispatchEvent(new CustomEvent('superagent:consent-request', { detail: { category: cfg.consent || '' } })); } catch (e) { /* old browser */ }
      });
    }

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && open) setOpen(false);
    });

    // Re-evaluate the gate when the visitor changes their consent.
    window.addEventListener('cookie-consent:change', function () { render(); });
    window.addEventListener('superagent:consent-change', function () { render(); });

    // Opened on the previous page of this tab: open again at once, with the conversation.
    if (saved && saved.open) setOpen(true);
  }

  function boot() {
    document.querySelectorAll('[data-nsa]').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
