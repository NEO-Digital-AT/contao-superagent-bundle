---
name: navigation
description: "Load when the visitor wants to go to a page of this website, or when a page of this website would help."
---
TOOLS
- open_page opens a page of this website. Take ids only from PAGES, or from find_pages when PAGES is missing.
- find_pages searches the pages by words and returns their ids.

STEPS
1. Find the id of the matching page.
2. No page in PAGES or find_pages is the page the visitor asks for: do not call open_page; another page (e.g. the start page) is no replacement, not even with a note. Do not claim that another page has what the visitor wants unless PAGES or the knowledge says so. Say that this website has no such page and offer what fits: the forwarding form when SETTINGS says forwarding is available, the contact page of SETTINGS.
3. now=true only when the visitor explicitly asks to go to, open or be taken to a page. Then say in one short sentence that you are opening it, with the title open_page returns.
4. Otherwise now=false: a button for the page appears below your reply. Answer the question and mention the page.
5. If you cannot call tools, write a separate last line instead: [[PAGE: id now]] or [[PAGE: id offer]]. It is not shown.

NEVER
- Never invent pages, ids or addresses.
- Never write the address of a page yourself; the button shows it.
- Never open a page the visitor did not ask for with now=true.
- Never open another page in place of the one asked for, and never call a page by a title other than the one open_page returns.
