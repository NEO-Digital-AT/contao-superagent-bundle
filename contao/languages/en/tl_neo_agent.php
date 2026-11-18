<?php

declare(strict_types=1);

// Legends
$GLOBALS['TL_LANG']['tl_neo_agent']['title_legend'] = 'General';
$GLOBALS['TL_LANG']['tl_neo_agent']['identity_legend'] = 'Identity';
$GLOBALS['TL_LANG']['tl_neo_agent']['provider_legend'] = 'Model';
$GLOBALS['TL_LANG']['tl_neo_agent']['prompt_legend'] = 'System prompt';
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_legend'] = 'Knowledge base (grounding)';
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_legend'] = 'Escalation & consent';

// Fields
$GLOBALS['TL_LANG']['tl_neo_agent']['title'] = ['Label', 'Internal name of this assistant (back end only).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['published'] = ['Active', 'Only one active assistant is shown on the front end.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['assistant_name'] = ['Display name', 'How the assistant introduces itself and appears in the chat header (e.g. “John”). Shared across all languages.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['model'] = ['Model', 'The language model of this assistant. Route and key are entered under "Access keys and licences" › AI; once a key is stored there, the models of the route are offered here.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature'] = ['Temperature', 'How freely the model words its answers: lower = more factual and consistent. 0.2 to 0.5 suits a chat.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens'] = ['Answer length (max tokens)', 'Longest answer. For models that reason, room for reasoning is added.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['system_prompt'] = ['System prompt', 'Behaviour rules for the assistant. {name} is replaced by the display name. Shared across all languages.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_source'] = ['Knowledge source', 'Source of the verified facts the assistant relies on. Shared across all languages.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_text'] = ['Knowledge text', 'Verified facts as text, appended to the system prompt as context.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_file'] = ['Knowledge file', 'A file (txt/md/json) from the file manager used as the knowledge base.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_email'] = ['Escalation email', 'Address that “forward to the team” requests are sent to. Leave empty if there is none – the assistant then never offers forwarding and points to the website’s contact options instead.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_name'] = ['Forward to (name)', 'Shown on the chat buttons, e.g. “Forward to John”. Leave empty for “the team”.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_category'] = ['Consent category', 'Category of the cookie banner after whose consent the chat appears. The cookie consent extension of the same family works without further setup; another consent tool needs a small script (documentation, “Customising”). Without a consent tool on the page the chat is available at once.'];

// Operations
$GLOBALS['TL_LANG']['tl_neo_agent']['languages'] = ['Languages', 'Manage the language variants (greeting, suggestions, Q&A …) of this assistant.'];

// Reference (option labels)
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_sources'] = [
    'none' => 'None',
    'text' => 'Text (entered)',
    'file' => 'File from the file manager',
    'llms' => 'The website’s llms.txt (recommended)',
    'llms_text' => 'llms.txt + additional text of your own',
];

// Personality, web search, referral
$GLOBALS['TL_LANG']['tl_neo_agent']['persona_legend'] = 'Personality & answer style';
$GLOBALS['TL_LANG']['tl_neo_agent']['expert_legend'] = 'Expert settings';
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address'] = ['Form of address', 'How should the assistant address visitors?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length'] = ['Answer length', 'How detailed should the answers be?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style'] = ['Tone', 'The basic tone of the answers.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['research_legend'] = 'Web search';
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_legend'] = 'Referral — who visitors are referred to';
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search'] = ['Allow web search', 'The assistant may decide on its own to look something up on the web for a question — but only on the sources approved below. Without this it answers from the knowledge base only.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode'] = ['Search service', '“Read approved sources only” fetches the listed addresses directly (free, ideal for manufacturers’ llms.txt). “SerpAPI” additionally searches through a search engine — the results are still strictly filtered to the approved domains.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_sources'] = ['Approved sources', 'One address per line: a partner or manufacturer site, its llms.txt or a data endpoint (e.g. a marketplace catalogue). If the start page does not answer the question, the assistant follows the matching sub-pages. Optionally add keywords — “address | keyword1 keyword2” — so it knows what a source is for; for API addresses this is needed because the address does not tell. This list is also the lock: NOTHING outside these domains is fetched, even if a visitor asks the assistant to.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max'] = ['Sources per question', 'How many sources the assistant reads at most for one question. More sources = better answers, but slower and more expensive.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_company'] = ['Always refer to', 'For setup, implementation, integration, migration, support and purchase the assistant ALWAYS refers to this provider — never to the manufacturer or developer of a third-party solution. It may name third-party products; the contact always remains you. Leave empty = no referral rule.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution'] = ['Preferred own solution', 'If this solution fits the question, the assistant names it first — honestly and only where it really fits. Leave empty = no preference.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution_note'] = ['What the solution is for', 'One or two sentences: what can it do, who is it for? The assistant uses this to mention it only when it really fits.'];

// Options (key = stored value)
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address_options'] = ['sie' => 'Formal', 'du' => 'Informal (first-name basis)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length_options'] = ['short' => 'Short (2–3 sentences)', 'medium' => 'Medium (4–6 sentences)', 'long' => 'Detailed (with a list when it helps)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style_options'] = ['professional' => 'Professional & factual', 'friendly' => 'Friendly & warm', 'expert' => 'Technical & precise'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode_options'] = ['sources' => 'Read approved sources only (free)', 'serpapi' => 'Search via SerpAPI — limited to the approved domains'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max_options'] = ['1' => '1 source', '2' => '2 sources', '3' => '3 sources', '4' => '4 sources', '5' => '5 sources'];
$GLOBALS['TL_LANG']['tl_neo_agent']['settings'] = ['Access keys and licences', 'AI access, web search and licence of the SuperAgent'];
$GLOBALS['TL_LANG']['tl_neo_agent']['ai_missing'] = 'No AI key is stored yet. Until then the website does not show the chat.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_stored'] = 'The SerpAPI key is stored under "Access keys and licences".';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_missing'] = 'No SerpAPI key is stored yet. Until then the assistant only reads the approved sources.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link'] = 'Open';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link_missing'] = 'Enter it now';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_missing'] = 'No assistant has its own texts for %1$s (start page "%2$s") yet; the chat shows the default texts there.';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_create'] = 'Create with suggestions for "%s"';
$GLOBALS['TL_LANG']['tl_neo_agent']['safety_legend'] = 'Safety';
$GLOBALS['TL_LANG']['tl_neo_agent']['no_medical'] = ['No medical advice', 'The assistant gives no diagnoses, treatment, medication or health tips and declines such questions in its own words, following the guidance in the language record (e.g. the emergency number). A rule for a language model cannot rule out every slip.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_mode'] = ['Privacy mode', 'The chat asks visitors not to enter personal data. The server removes e-mail addresses, phone numbers, IBANs, card numbers, social security numbers and dates of birth from the message before it goes to the AI service; the model watches for health information and the like. Places, countries and addresses without a name do not count as personal data (e.g. questions about the weather). Functions that need personal data (e.g. booking or looking up a reservation) are then not possible.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['contact_page'] = ['Contact page', 'The assistant can open this page when a visitor wants to reach a person (Basic licence), besides the form in the chat.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['avatar'] = ['Image', 'Shown instead of the bot icon in the chat window (square, e.g. 96 × 96 px).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side'] = ['Position', 'Corner of the screen for the chat button and window. When buttons of other extensions (cookie settings, accessibility) sit in the same corner, they are arranged together as set in the page layout: one settings button with a menu, stacked or side by side.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side_options'] = [
    'right' => 'Bottom right',
    'left' => 'Bottom left',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort'] = ['Reasoning effort', 'How thoroughly the model thinks before it answers. More reasoning = more accurate, but slower and more expensive. For a chat on a website, “Low” is usually enough.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort_options'] = ['off' => 'Off', 'low' => 'Low (recommended)', 'medium' => 'Medium', 'high' => 'High'];
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_reasoning'] = 'can reason';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_tools'] = 'can use tools';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_max'] = 'up to %s tokens per answer';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_no_tools'] = 'This model cannot use tools. Web search does not work with it; navigation and forwarding work through marker lines.';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_untested'] = 'Not tested with tools of connections. Tested: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_legend'] = 'Voice input';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_input'] = ['Allow voice input', 'Visitors can speak a message (at most 60 seconds). The recording goes through your server to the AI service for transcription, is not stored, and the text appears in the input field to be checked. With privacy mode on, only together with “Voice input also in privacy mode”.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['transcription_model'] = ['Transcription model', 'Model for the transcription, from the list of the route (with Requesty, approve it under “Transcriptions”). Models processing in the EU are e.g. those of Mistral (Voxtral) and Scaleway.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_privacy_off'] = 'While privacy mode is on, there is no voice input: a recording would go to the transcription service unfiltered. Switch on “Voice input also in privacy mode” below to allow it.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_in_privacy'] = ['Voice input also in privacy mode', 'The microphone stays available while privacy mode is on. The recording goes to the transcription service unfiltered; only the text is filtered: e-mail addresses, phone numbers and the like are removed before it appears in the input field and before anything reaches the chat model.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['forwarding_locked'] = 'Only used with an escalation e-mail address above: without one, the chat has no forwarding form.';

// Publishing ("preview only") and the terms of the extension
$GLOBALS['TL_LANG']['tl_neo_agent']['publish_legend'] = 'Publishing';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility'] = ['Visible to', 'New assistants start in “Preview only”: then only logged-in administrators see the chat, in the front-end preview. Test it there before you make it visible to all visitors.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_options'] = [
    'preview' => 'Preview only – only logged-in administrators see the chat in the preview',
    'public' => 'All visitors',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_note'] = '“All visitors” can be chosen once the terms of the extension are confirmed.';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_locked'] = 'Please confirm the terms on “Access keys and licences” first. Until then the assistant stays in the preview.';
$GLOBALS['TL_LANG']['tl_neo_agent']['preview_badge'] = 'Preview';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_missing'] = 'Before an assistant becomes visible to visitors, the terms must be confirmed.';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_edition_missing'] = 'The features of the edition %s are locked until its terms are confirmed.';

// Forwarding form: optional fields and the newsletter checkbox
$GLOBALS['TL_LANG']['tl_neo_agent']['form_fields'] = ['Additional fields in the form', 'E-mail address and message are always in the form and required. These fields are added on request; what visitors enter goes only by e-mail to the escalation address, never to the AI.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_required'] = ['Of these, required', 'Only fields chosen on the left can be required.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_field_options'] = [
    'name' => 'Name',
    'phone' => 'Phone',
    'company' => 'Company',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_module'] = ['Newsletter checkbox', 'Offers “Subscribe to the newsletter” in the form, never preselected. Choose a newsletter module of this website; its lists or channels and its confirmation e-mail apply (double opt-in).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_off'] = 'Off';
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_kinds'] = [
    'brevo' => 'Brevo',
    'contao' => 'Contao newsletter',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_problem'] = [
    'missing' => 'The chosen newsletter module no longer exists. Until another one is chosen, the form shows no newsletter checkbox.',
    'brevo_incomplete' => 'The chosen Brevo module lacks a list, a confirmation template or a confirmation page. Until then the form shows no newsletter checkbox.',
    'brevo_key' => 'No Brevo API key is stored yet ("Access keys and licences"). Until then the form shows no newsletter checkbox.',
    'contao_channels' => 'No channel is chosen in the chosen module “Subscribe”. Until then the form shows no newsletter checkbox.',
    'contao_page' => 'The chosen module “Subscribe” is not on any published page. The confirmation link leads to that page; until the module is there, the form shows no newsletter checkbox.',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_none'] = 'There is no newsletter module yet (Brevo or Contao’s “Subscribe”). Create one, then it can be chosen here.';
$GLOBALS['TL_LANG']['tl_neo_agent']['sender_missing'] = 'Forwarded requests from %s cannot be sent: there is no sender address. Enter the “E-mail address of the website administrator” at the start point, or the “E-mail address of the system administrator” under System › Settings, or set MAILER_FROM on the server. Until then the chat shows an error instead of sending.';

// Choices instead of free text
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_options'] = [
    'none' => 'No consent needed (chat available at once)',
    'functional' => 'Functional',
    'statistics' => 'Statistics',
    'marketing' => 'Marketing',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_custom'] = '%s (own category)';
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature_notes'] = [
    '0' => 'factual, always worded the same',
    '0.4' => 'default',
    '1' => 'varied',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens_option'] = '%1$s tokens (about %2$s words)';
$GLOBALS['TL_LANG']['tl_neo_agent']['form_consent'] = ['Ask for consent to be contacted', 'Shows a required checkbox in the form: the visitor agrees that their details are used for the request and that they are contacted. The e-mail to your team notes the consent.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_page'] = ['Privacy policy', 'Page the checkbox links to. Empty = no link.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country'] = ['Preselected country code', 'For the phone field. Empty = by the language of the page (e.g. German → Germany). Visitors can choose any country code.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country_auto'] = 'By the language of the page';

// Tools of the connections to MCP servers (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['connections'] = ['Connections', 'Connect MCP servers, read and lock their tools (administrators only).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tool_log'] = ['Tool log', 'Tool calls of the last 30 days (administrators only).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_legend'] = 'Tools of the connections';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_tools'] = ['Tools', 'What this assistant may use. Nothing is ticked by default, locked tools are not shown. The assistant decides itself when to call a tool.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_inactive'] = 'not active';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_locked_tool'] = 'locked';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_gone_group'] = 'No longer available';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_none'] = 'There is no connection to a MCP server yet. Administrators add one with "Connections" above the list of assistants.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_open_connections'] = 'Open the connections';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_privacy'] = 'Privacy mode is on: these tools ask for details of a person (%s). The server removes e-mail addresses and phone numbers from the messages and the assistant does not ask for personal data, so the tools do not get these details.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model'] = 'According to the AI service the chosen model cannot call tools. Choose another model, otherwise the tools stay unused.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_models'] = 'Tools of connections (e.g. bookings) need a strong language model: it has to plan several steps, take details from the conversation and call tools one after another. Weaker models ask again, skip steps or give up. Tested with the booking flow of the apaleo template: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_tested'] = 'The chosen model is one of them.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_untested'] = 'The chosen model is not one of them: check the flows of your connections in the chat before visitors use them.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_unusable'] = 'Ticked but not usable: %d (locked, no longer available, or connection not active). The chat does not use them.';

// Installed skills (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['skill_list'] = ['Skills', 'Install skills from GitHub or as a file (administrators only).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['skills_legend'] = 'Installed skills';
$GLOBALS['TL_LANG']['tl_neo_agent']['skills'] = ['Skills', 'Which installed skills this assistant may use. Nothing is ticked by default. The model sees them in its catalogue and loads a skill itself when a question falls into its area.'];

// Consent category the cookie consent extension never asks for (reason at the field).
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_unoffered'] = 'The module “%s” lists no active service in this category, so its banner never asks visitors for consent to the chat: where the banner appears, the chat stays unavailable; on pages without it the chat starts without asking. Add the service “AI chat assistant” in this category or choose another category.';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_add'] = 'Add the service now';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_ask_admin'] = 'An administrator can add it in the module “%s”.';

// Languages and starter questions in the assistant's form (LanguageCallbacks::overview).
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_legend'] = 'Languages and starter questions';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_intro'] = 'An assistant has one language: greeting, starter questions, questions & answers and the texts of the chat window are in this language. For every further language of the website, create an assistant of its own (copy it, change the language) and choose it in the chat module of that language.';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_none'] = 'No language yet: the chat shows the default texts and no starter questions.';
$GLOBALS['TL_LANG']['tl_neo_agent']['starters_none'] = 'No starter questions';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_edit'] = 'Edit texts and starter questions';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_add'] = 'Add language';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_answer'] = 'Answer:';
