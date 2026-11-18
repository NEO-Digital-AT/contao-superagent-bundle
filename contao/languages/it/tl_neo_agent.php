<?php

declare(strict_types=1);

// Legends
$GLOBALS['TL_LANG']['tl_neo_agent']['title_legend'] = 'Generale';
$GLOBALS['TL_LANG']['tl_neo_agent']['identity_legend'] = 'Identità';
$GLOBALS['TL_LANG']['tl_neo_agent']['provider_legend'] = 'Modello';
$GLOBALS['TL_LANG']['tl_neo_agent']['prompt_legend'] = 'Prompt di sistema';
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_legend'] = 'Base di conoscenza';
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_legend'] = 'Inoltro e consenso';

// Fields
$GLOBALS['TL_LANG']['tl_neo_agent']['title'] = ['Denominazione', 'Nome interno di questo assistente (visibile solo nel back end).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['published'] = ['Attivo', 'Sul sito viene mostrato un solo assistente attivo.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['assistant_name'] = ['Nome visualizzato', 'Come l’assistente si presenta e come appare nell’intestazione della chat (ad es. «Mario»). Vale per tutte le lingue.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['model'] = ['Modello', 'Il modello linguistico di questo assistente. Percorso e chiave si inseriscono in «Accessi e licenze» › IA; appena lì è salvata una chiave, qui vengono proposti i modelli del percorso.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature'] = ['Temperatura', 'Quanto liberamente formula il modello: più basso = più oggettivo e costante. Per una chat va bene da 0,2 a 0,5.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens'] = ['Lunghezza della risposta (max. token)', 'Lunghezza massima di una risposta. Per i modelli che ragionano si aggiunge spazio per il ragionamento.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['system_prompt'] = ['Prompt di sistema', 'Regole di comportamento per l’assistente. {name} viene sostituito dal nome visualizzato. Vale per tutte le lingue.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_source'] = ['Fonte di conoscenza', 'Origine dei fatti verificati su cui si basa l’assistente. Vale per tutte le lingue.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_text'] = ['Testo di conoscenza', 'Fatti verificati in forma di testo, aggiunti al prompt di sistema come contesto.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_file'] = ['File di conoscenza', 'Un file (txt/md/json) della gestione file usato come base di conoscenza.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_email'] = ['E-mail per l’inoltro', 'Indirizzo a cui vengono inviate le richieste «inoltra al team». Lasciare vuoto se non c’è – l’assistente non proporrà mai l’inoltro e rimanderà ai contatti del sito.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_name'] = ['Inoltra a (nome)', 'Mostrato sui pulsanti della chat, ad es. «Inoltra a Mario». Lasci vuoto per «il team».'];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_category'] = ['Categoria di consenso', 'Categoria del banner dei cookie dopo il cui consenso compare la chat. L’estensione per il consenso ai cookie della stessa serie funziona senza altre impostazioni; un altro strumento di consenso richiede un piccolo script (documentazione, «Customising»). Senza strumento di consenso sulla pagina la chat è subito disponibile.'];

// Operations
$GLOBALS['TL_LANG']['tl_neo_agent']['languages'] = ['Lingue', 'Gestisca le varianti linguistiche (saluto, suggerimenti, domande e risposte …) di questo assistente.'];

// Reference (option labels)
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_sources'] = [
    'none' => 'Nessuna',
    'text' => 'Testo (inserito qui)',
    'file' => 'File dalla gestione file',
    'llms' => 'Il file llms.txt del sito (consigliato)',
    'llms_text' => 'llms.txt + testo aggiuntivo proprio',
];

// Personality, web search, referral
$GLOBALS['TL_LANG']['tl_neo_agent']['persona_legend'] = 'Personalità e stile delle risposte';
$GLOBALS['TL_LANG']['tl_neo_agent']['expert_legend'] = 'Impostazioni avanzate';
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address'] = ['Forma di cortesia', 'Come deve rivolgersi l’assistente ai visitatori?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length'] = ['Lunghezza delle risposte', 'Quanto devono essere dettagliate le risposte?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style'] = ['Tono', 'Il tono di base delle risposte.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['research_legend'] = 'Ricerca sul web';
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_legend'] = 'Rimando — a chi vengono indirizzati i visitatori';
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search'] = ['Consenti la ricerca sul web', 'L’assistente può decidere autonomamente di cercare informazioni sul web per una domanda — ma solo nelle fonti approvate qui sotto. Senza questa opzione risponde solo in base alla base di conoscenza.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode'] = ['Servizio di ricerca', '«Leggi solo le fonti approvate» recupera direttamente gli indirizzi elencati (gratuito, ideale per il file llms.txt dei produttori). «SerpAPI» cerca in più tramite un motore di ricerca — i risultati restano comunque rigorosamente limitati ai domini approvati.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_sources'] = ['Fonti approvate', 'Un indirizzo per riga: il sito di un partner o di un produttore, il suo llms.txt o un endpoint di dati (ad es. il catalogo di un marketplace). Se la pagina iniziale non risponde alla domanda, l’assistente segue le sottopagine pertinenti. Facoltativamente aggiunga parole chiave — «indirizzo | parola1 parola2» — perché sappia a cosa serve una fonte; per gli indirizzi API è necessario, perché l’indirizzo stesso non lo rivela. Questo elenco è anche il blocco di sicurezza: NIENTE al di fuori di questi domini viene recuperato, nemmeno se un visitatore lo chiede all’assistente.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max'] = ['Fonti per domanda', 'Quante fonti al massimo l’assistente legge per una domanda. Più fonti = risposte migliori, ma più lente e più costose.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_company'] = ['Rimanda sempre a', 'Per configurazione, implementazione, integrazione, migrazione, assistenza e acquisto l’assistente rimanda SEMPRE a questo fornitore — mai al produttore o allo sviluppatore di una soluzione di terzi. Può citare prodotti di terzi; il referente resta sempre Lei. Vuoto = nessuna regola di rimando.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution'] = ['Soluzione propria preferita', 'Se questa soluzione è adatta alla domanda, l’assistente la cita per prima — in modo onesto e solo quando è davvero pertinente. Vuoto = nessuna preferenza.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution_note'] = ['A cosa serve la soluzione', 'Una o due frasi: che cosa fa, a chi si rivolge? L’assistente lo usa per citarla solo quando è davvero pertinente.'];

// Options (key = stored value)
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address_options'] = ['sie' => 'Formale (Lei)', 'du' => 'Informale (tu)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length_options'] = ['short' => 'Breve (2–3 frasi)', 'medium' => 'Media (4–6 frasi)', 'long' => 'Dettagliata (con un elenco, se utile)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style_options'] = ['professional' => 'Professionale e oggettivo', 'friendly' => 'Cordiale e caloroso', 'expert' => 'Tecnico e preciso'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode_options'] = ['sources' => 'Leggi solo le fonti approvate (gratuito)', 'serpapi' => 'Ricerca tramite SerpAPI — limitata ai domini approvati'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max_options'] = ['1' => '1 fonte', '2' => '2 fonti', '3' => '3 fonti', '4' => '4 fonti', '5' => '5 fonti'];
$GLOBALS['TL_LANG']['tl_neo_agent']['settings'] = ['Accessi e licenze', 'Accesso IA, ricerca web e licenza del SuperAgent'];
$GLOBALS['TL_LANG']['tl_neo_agent']['ai_missing'] = 'Non è ancora salvata una chiave IA. Fino ad allora il sito non mostra la chat.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_stored'] = 'La chiave SerpAPI è salvata in «Accessi e licenze».';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_missing'] = 'Non è ancora salvata una chiave SerpAPI. Fino ad allora l’assistente legge solo le fonti approvate.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link'] = 'Apri';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link_missing'] = 'Inserirla ora';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_missing'] = 'Nessun assistente ha ancora testi propri per %1$s (pagina iniziale «%2$s»); lì la chat mostra i testi predefiniti.';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_create'] = 'Crea con suggerimenti per «%s»';
$GLOBALS['TL_LANG']['tl_neo_agent']['safety_legend'] = 'Sicurezza';
$GLOBALS['TL_LANG']['tl_neo_agent']['no_medical'] = ['Nessun consiglio medico', 'L’assistente non fornisce diagnosi né consigli su terapie, farmaci o salute e rifiuta tali domande con parole sue, secondo l’indicazione del record della lingua (p. es. il numero di emergenza). Una regola per un modello linguistico non esclude ogni errore.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_mode'] = ['Modalità privacy', 'La chat chiede di non inserire dati personali. Il server rimuove dal messaggio indirizzi e-mail, numeri di telefono, IBAN, numeri di carta, codici di previdenza sociale e date di nascita prima che arrivi al servizio di IA; ai dati sanitari e simili presta attenzione il modello. Luoghi, paesi e indirizzi senza un nome non contano come dati personali (p. es. domande sul meteo). Le funzioni che richiedono dati personali (p. es. prenotare o consultare una prenotazione) non sono quindi possibili.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['contact_page'] = ['Pagina di contatto', 'L’assistente può aprire questa pagina quando un visitatore vuole contattare una persona (licenza Basic), oltre al modulo nella chat.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['avatar'] = ['Immagine', 'Mostrata al posto dell’icona del robot nella finestra di chat (quadrata, ad es. 96 × 96 px).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side'] = ['Posizione', 'Angolo dello schermo per il pulsante e la finestra della chat. Se nello stesso angolo ci sono pulsanti di altre estensioni (impostazioni dei cookie, accessibilità), vengono disposti insieme come impostato nel layout di pagina: un pulsante impostazioni con menu, impilati o uno accanto all’altro.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side_options'] = [
    'right' => 'In basso a destra',
    'left' => 'In basso a sinistra',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort'] = ['Sforzo di ragionamento', 'Quanto a fondo il modello riflette prima di rispondere. Più ragionamento = più preciso, ma più lento e più costoso. Per una chat su un sito web di solito basta «Basso».'];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort_options'] = ['off' => 'Disattivato', 'low' => 'Basso (consigliato)', 'medium' => 'Medio', 'high' => 'Alto'];
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_reasoning'] = 'può ragionare';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_tools'] = 'può usare strumenti';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_max'] = 'fino a %s token per risposta';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_no_tools'] = 'Questo modello non può usare strumenti. La ricerca web non funziona; navigazione e inoltro avvengono tramite righe sostitutive.';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_untested'] = 'Non testato con strumenti di connessioni. Testati: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_legend'] = 'Input vocale';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_input'] = ['Consenti input vocale', 'I visitatori possono dettare un messaggio (al massimo 60 secondi). La registrazione passa dal Suo server al servizio di IA per la trascrizione, non viene conservata e il testo compare nel campo di inserimento per essere controllato. Con la modalità privacy attiva, solo insieme a «Input vocale anche in modalità privacy».'];
$GLOBALS['TL_LANG']['tl_neo_agent']['transcription_model'] = ['Modello di trascrizione', 'Modello per la trascrizione, dall’elenco della route (in Requesty da approvare sotto «Transcriptions»). Nell’UE elaborano ad es. i modelli di Mistral (Voxtral) e Scaleway.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_privacy_off'] = 'Finché la modalità privacy è attiva non c’è input vocale: una registrazione andrebbe al servizio di trascrizione senza filtri. Attivi qui sotto «Input vocale anche in modalità privacy» per consentirlo.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_in_privacy'] = ['Input vocale anche in modalità privacy', 'Il microfono resta disponibile quando la modalità privacy è attiva. La registrazione va al servizio di trascrizione senza filtri; viene filtrato solo il testo: indirizzi e-mail, numeri di telefono e dati simili vengono rimossi prima che compaia nel campo di inserimento e prima che qualcosa arrivi al modello della chat.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['forwarding_locked'] = 'Vale solo con un indirizzo e-mail per l’inoltro qui sopra: senza, la chat non ha un modulo di inoltro.';

// Publishing ("preview only") and the terms of the extension
$GLOBALS['TL_LANG']['tl_neo_agent']['publish_legend'] = 'Pubblicazione';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility'] = ['Visibile a', 'I nuovi assistenti partono da «Solo anteprima»: solo gli amministratori connessi vedono la chat, nell’anteprima del front end. La provi lì prima di renderla visibile a tutti i visitatori.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_options'] = [
    'preview' => 'Solo anteprima – solo gli amministratori connessi vedono la chat nell’anteprima',
    'public' => 'Tutti i visitatori',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_note'] = '«Tutti i visitatori» si può scegliere appena le condizioni dell’estensione sono confermate.';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_locked'] = 'Confermi prima le condizioni in «Accessi e licenze». Fino ad allora l’assistente resta in anteprima.';
$GLOBALS['TL_LANG']['tl_neo_agent']['preview_badge'] = 'Anteprima';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_missing'] = 'Prima che un assistente diventi visibile ai visitatori, le condizioni devono essere confermate.';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_edition_missing'] = 'Le funzioni dell’edizione %s sono bloccate finché le sue condizioni non sono confermate.';

// Forwarding form: optional fields and the newsletter checkbox
$GLOBALS['TL_LANG']['tl_neo_agent']['form_fields'] = ['Campi aggiuntivi del modulo', 'Indirizzo e-mail e messaggio sono sempre nel modulo e obbligatori. Questi campi si aggiungono su richiesta; i dati inseriti vanno solo via e-mail all’indirizzo di inoltro, mai all’IA.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_required'] = ['Di cui obbligatori', 'Possono essere obbligatori solo i campi scelti a sinistra.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_field_options'] = [
    'name' => 'Nome',
    'phone' => 'Telefono',
    'company' => 'Azienda',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_module'] = ['Casella newsletter', 'Offre «Iscriversi alla newsletter» nel modulo, mai preselezionata. Scelga un modulo newsletter di questo sito; valgono le sue liste o i suoi canali e la sua e-mail di conferma (double opt-in).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_off'] = 'Disattivato';
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_kinds'] = [
    'brevo' => 'Brevo',
    'contao' => 'Newsletter Contao',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_problem'] = [
    'missing' => 'Il modulo newsletter scelto non esiste più. Finché non ne viene scelto un altro, il modulo non mostra la casella newsletter.',
    'brevo_incomplete' => 'Nel modulo Brevo scelto mancano una lista, un modello di conferma o una pagina di conferma. Fino ad allora il modulo non mostra la casella newsletter.',
    'brevo_key' => 'Non è ancora salvata una chiave API di Brevo («Accessi e licenze»). Fino ad allora il modulo non mostra la casella newsletter.',
    'contao_channels' => 'Nel modulo «Iscriversi» scelto non è selezionato alcun canale. Fino ad allora il modulo non mostra la casella newsletter.',
    'contao_page' => 'Il modulo «Iscriversi» scelto non si trova su nessuna pagina pubblicata. Il link di conferma porta a quella pagina; finché il modulo non vi si trova, il modulo non mostra la casella newsletter.',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_none'] = 'Non esiste ancora un modulo newsletter (Brevo o «Iscriversi» di Contao). Ne crei uno, poi potrà sceglierlo qui.';
$GLOBALS['TL_LANG']['tl_neo_agent']['sender_missing'] = 'Le richieste inoltrate da %s non possono essere inviate: manca un indirizzo mittente. Inserisci l’«Indirizzo e-mail dell’amministratore del sito» nella pagina radice, oppure l’«Indirizzo e-mail dell’amministratore di sistema» in Sistema › Impostazioni generali, oppure imposta MAILER_FROM sul server. Fino ad allora la chat mostra un errore invece di inviare.';

// Choices instead of free text
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_options'] = [
    'none' => 'Nessun consenso necessario (chat subito disponibile)',
    'functional' => 'Funzionale',
    'statistics' => 'Statistica',
    'marketing' => 'Marketing',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_custom'] = '%s (categoria propria)';
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature_notes'] = [
    '0' => 'oggettivo, sempre formulato allo stesso modo',
    '0.4' => 'predefinito',
    '1' => 'vario',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens_option'] = '%1$s token (circa %2$s parole)';
$GLOBALS['TL_LANG']['tl_neo_agent']['form_consent'] = ['Chiedere il consenso al contatto', 'Mostra nel modulo una casella obbligatoria: il visitatore acconsente all’uso dei dati per la richiesta e a essere contattato. L’e-mail al Suo team riporta il consenso.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_page'] = ['Informativa sulla privacy', 'Pagina a cui rimanda la casella. Vuoto = senza link.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country'] = ['Prefisso preselezionato', 'Per il campo telefono. Vuoto = secondo la lingua della pagina (ad es. tedesco → Germania). I visitatori possono scegliere qualsiasi prefisso.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country_auto'] = 'Secondo la lingua della pagina';

// Tools of the connections to MCP servers (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['connections'] = ['Connessioni', 'Collegare server MCP, leggere e bloccare i loro strumenti (solo amministratori).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tool_log'] = ['Registro', 'Chiamate agli strumenti degli ultimi 30 giorni (solo amministratori).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_legend'] = 'Strumenti delle connessioni';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_tools'] = ['Strumenti', 'Ciò che questo assistente può usare. Di base nulla è selezionato, gli strumenti bloccati non vengono mostrati. L’assistente decide da sé quando richiamare uno strumento.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_inactive'] = 'non attiva';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_locked_tool'] = 'bloccato';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_gone_group'] = 'Non più disponibile';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_none'] = 'Non esiste ancora una connessione a un server MCP. Gli amministratori la creano tramite «Connessioni» sopra l’elenco degli assistenti.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_open_connections'] = 'Apri le connessioni';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_privacy'] = 'Modalità privacy attiva: questi strumenti chiedono dati di una persona (%s). Il server rimuove dai messaggi indirizzi e-mail e numeri di telefono e l’assistente non chiede dati personali, quindi gli strumenti non ricevono questi dati.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model'] = 'Secondo il servizio di IA, il modello scelto non può richiamare strumenti. Scelga un altro modello, altrimenti gli strumenti restano inutilizzati.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_models'] = 'Gli strumenti delle connessioni (p. es. prenotazioni) richiedono un modello linguistico potente: deve pianificare più passaggi, riprendere i dati dalla conversazione e richiamare gli strumenti uno dopo l’altro. I modelli più deboli richiedono di nuovo i dati, saltano passaggi o si arrendono. Testati con il processo di prenotazione del modello apaleo: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_tested'] = 'Il modello scelto è tra questi.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_untested'] = 'Il modello scelto non è tra questi: verifichi i processi delle Sue connessioni nella chat prima che li usino i visitatori.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_unusable'] = 'Selezionati ma non utilizzabili: %d (bloccati, non più disponibili o connessione non attiva). La chat non li usa.';

// Installed skills (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['skill_list'] = ['Skills', 'Installare skill da GitHub o come file (solo amministratori).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['skills_legend'] = 'Skill installati';
$GLOBALS['TL_LANG']['tl_neo_agent']['skills'] = ['Skills', 'Quali skill installati questo assistente può usare. Di base nulla è selezionato. Il modello li vede nel suo catalogo e carica da sé uno skill quando una domanda rientra nel suo ambito.'];

// Consent category the cookie consent extension never asks for (reason at the field).
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_unoffered'] = 'Il modulo «%s» non contiene alcun servizio attivo in questa categoria, quindi il banner non chiede mai ai visitatori il consenso per la chat: dove compare il banner, la chat resta non disponibile; nelle pagine senza banner parte senza chiedere. Aggiunga il servizio «Assistente chat con IA» in questa categoria o scelga un’altra categoria.';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_add'] = 'Aggiungi ora il servizio';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_ask_admin'] = 'Un amministratore può aggiungerlo nel modulo «%s».';

// Languages and starter questions in the assistant's form (LanguageCallbacks::overview).
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_legend'] = 'Lingue e domande iniziali';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_intro'] = 'Un assistente ha una sola lingua: saluto, domande iniziali, domande e risposte e testi della finestra di chat sono in questa lingua. Per ogni altra lingua del sito create un assistente a sé (copiatelo e cambiate la lingua) e sceglietelo nel modulo di chat di quella lingua.';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_none'] = 'Ancora nessuna lingua: la chat mostra i testi predefiniti e nessuna domanda iniziale.';
$GLOBALS['TL_LANG']['tl_neo_agent']['starters_none'] = 'Nessuna domanda iniziale';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_edit'] = 'Modifica testi e domande iniziali';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_add'] = 'Aggiungi lingua';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_answer'] = 'Risposta:';
