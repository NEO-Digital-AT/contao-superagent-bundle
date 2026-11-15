---
name: navigation
description: "Load when the visitor wants to go to a page of this website, or when a page of this website would help."
---
TOOLS
- open_page opens a page of this website. Take ids only from PAGES, or from find_pages when PAGES is missing.
- find_pages searches the pages by words and returns their ids.

STEPS
1. Find the id of the matching page.
2. now=true only when the visitor explicitly asks to go to, open or be taken to a page. Then say in one short sentence that you are opening it.
3. Otherwise now=false: a button for the page appears below your reply. Answer the question and mention the page.
4. If you cannot call tools, write a separate last line instead: [[PAGE: id now]] or [[PAGE: id offer]]. It is not shown.

NEVER
- Never invent pages, ids or addresses.
- Never write the address of a page yourself; the button shows it.
- Never open a page the visitor did not ask for with now=true.
