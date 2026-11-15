<?php

declare(strict_types=1);

// Palette legend + field for the "neo_agent" front-end module.
$GLOBALS['TL_LANG']['tl_module']['config_legend'] = 'Configuration';
$GLOBALS['TL_LANG']['tl_module']['agent'] = ['Assistant', 'Which assistant the widget shows. Empty = first active assistant. The language (greeting, Q&A …) is matched automatically to the page language.'];
$GLOBALS['TL_LANG']['tl_module']['nsa_ga_event'] = ['GA4 event "chat started"', 'Name of the Google Analytics 4 event (e.g. "chat_ai") fired when the first chat message is sent — only after statistics consent. Leave empty to send no event.'];
