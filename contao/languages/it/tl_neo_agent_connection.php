<?php

declare(strict_types=1);

// Connections to MCP servers (edition Plus), administrators only.
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['new'] = ['Nuova connessione', 'Collegare un server MCP.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['edit'] = ['Modifica', 'Modifica la connessione.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['delete'] = ['Elimina', 'Elimina la connessione.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['show'] = ['Dettagli', 'Mostra i dettagli.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['back_to_agents'] = ['Assistenti', 'Torna all’elenco degli assistenti.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['title_legend'] = 'Connessione';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_legend'] = 'Accesso';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['data_legend'] = 'Dati personali';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_legend'] = 'Strumenti';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_legend'] = 'Conferma';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['name'] = ['Nome', 'Il nome della connessione nell’elenco e nella scelta dell’assistente, ad es. «Prenotazione appuntamenti».'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url'] = ['Indirizzo del server MCP', 'L’indirizzo che il fornitore indica per il suo server MCP, ad es. https://mcp.example.com/mcp. Solo HTTPS e solo indirizzi pubblici.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth'] = ['Accesso', 'Come il sito accede al server. Lo indica la guida del fornitore.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_options'] = [
    'none' => 'Senza accesso',
    'key' => 'Chiave (Authorization: Bearer)',
    'headers' => 'Header propri',
    'oauth' => 'OAuth (accesso al servizio)',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data'] = ['Dati personali', 'Vale in entrambe le direzioni: per ciò che l’assistente invia al servizio e per i risultati inviati al servizio di IA. Vengono riconosciuti indirizzi e-mail, numeri di telefono, IBAN, numeri di carta, codici di previdenza sociale e date di nascita, non nomi e indirizzi.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data_options'] = [
    'mask' => 'Mascherare: sostituire con un segnaposto',
    'block' => 'Non inviare: trattenere la chiamata o il risultato',
    'send' => 'Inviare tutto',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['api_key'] = ['Chiave', 'Inviata come «Authorization: Bearer …». Salvata cifrata e mai più mostrata. Lasciare vuoto mantiene la chiave salvata.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['headers'] = ['Header propri', 'Nome e valore per riga, ad es. «X-Api-Key». I valori vengono salvati cifrati e non più mostrati; un valore vuoto mantiene quello salvato.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_id'] = ['ID client (solo per app propria)', 'Necessario solo se il servizio non offre la registrazione automatica: crei lì un’app, vi inserisca l’indirizzo di reindirizzamento qui sopra e qui l’ID client. Vuoto = registrazione automatica.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_secret'] = ['Secret client (solo per app propria)', 'Il secret dell’app, se il servizio ne rilascia uno. Salvato cifrato e mai più mostrato.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['warning'] = 'Attenzione: tramite un server MCP l’assistente può richiamare funzioni di un altro sistema – anche funzioni che lì modificano o eliminano dati o mostrano dati dei clienti. Ogni visitatore della chat lavora con l’accesso di questa connessione. Abiliti solo strumenti a basso rischio o limitati da uno skill e provi l’assistente prima di renderlo visibile ai visitatori. Gli strumenti che secondo il server modificano o eliminano dati, o che non sono contrassegnati, sono bloccati per impostazione predefinita. Resta un rischio residuo, perché un modello linguistico può sbagliare o essere ingannato da messaggi formulati ad arte. Il produttore di questa estensione non si assume alcuna responsabilità per danni causati dagli strumenti abilitati. Cosa vale esattamente e quali eccezioni esistono è indicato nelle condizioni di licenza.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_active'] = 'attiva · %d di %d strumenti abilitati';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unread'] = 'strumenti non letti';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unconfirmed'] = 'non confermata';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url_https'] = 'Inserisca un indirizzo che inizia con https://.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['header_name'] = 'Il nome header «%s» non è valido. Sono ammessi lettere, cifre e trattini.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_heading'] = 'Accesso al servizio';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connected'] = 'Accesso effettuato da %s il %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_not_connected'] = 'Accesso non ancora effettuato. Salvi l’indirizzo, poi «Collega»: verrà portato alla pagina di accesso del servizio e tornerà qui al termine.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connect'] = 'Collega';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_reconnect'] = 'Ricollega';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_disconnect'] = 'Disconnetti';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_redirect'] = 'Indirizzo di reindirizzamento di questo sito (necessario solo per un’app propria presso il servizio):';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_forgotten'] = 'L’accesso al servizio è stato rimosso.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_failed'] = 'L’accesso al servizio non è riuscito: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_done'] = 'L’accesso al servizio è stato salvato.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_unread'] = 'Gli strumenti non sono ancora stati letti. Vengono letti dopo il salvataggio, non appena l’accesso è completo.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_url_changed'] = 'L’indirizzo è cambiato. Gli strumenti vengono riletti al salvataggio; fino ad allora la connessione non è attiva.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_read'] = '%d strumenti di %s, letti il %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_reread'] = 'Rileggi gli strumenti';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_done'] = '%d strumenti letti, di cui %d bloccati.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_failed'] = 'Non è stato possibile leggere gli strumenti: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_heading'] = 'Strumenti e blocchi';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['lock_all'] = 'Bloccare tutti gli strumenti che secondo il server modificano o eliminano dati, o che non sono contrassegnati';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_locked'] = 'Blocco';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_tool'] = 'Strumento';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_kind'] = 'Tipo secondo il server';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_note'] = 'Nota';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_locked'] = 'bloccato';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_new'] = 'NUOVO';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_personal'] = 'richiede dati personali';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_help'] = 'Nessun assistente può usare uno strumento bloccato. Chi rimuove un blocco lo conferma qui sotto; chi l’ha fatto e quando viene registrato. I nuovi strumenti del server non sono selezionati per nessun assistente.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['kind_options'] = [
    'read' => 'solo lettura',
    'change' => 'modifica dati',
    'delete' => 'elimina dati',
    'unmarked' => 'non contrassegnato',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['unlock_confirm'] = 'Blocco non rimosso: %s. Per rimuoverlo confermi qui sotto di aver verificato gli strumenti.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirmed'] = 'Confermata da %s il %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['not_confirmed'] = 'Non ancora confermata. Solo dopo gli assistenti possono usare gli strumenti di questa connessione.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_check'] = 'Ho verificato gli strumenti abilitati e comprendo il rischio residuo.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_help'] = 'Necessario per la prima abilitazione della connessione e ogni volta che rimuove un blocco.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template'] = ['Modello', 'Compila nome, indirizzo e accesso finché questi campi sono vuoti e stabilisce come gli strumenti di questo servizio vengono classificati e bloccati. Uno skill del modello viene installato al salvataggio. Lasciare vuoto per un server MCP qualsiasi.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_help'] = 'Guida del fornitore:';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_recommended'] = 'consigliato';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_customer_data'] = 'mostra dati dei clienti';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_own_app'] = 'Questo servizio richiede un’app propria: crei un’app presso il servizio e vi inserisca l’indirizzo di reindirizzamento indicato qui sotto. ID client e secret client dell’app vanno nei campi sottostanti. Poi «Collega».';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_installed'] = 'Lo skill «%s» del modello è installato. Lo spunti per gli assistenti che devono usarlo.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_failed'] = 'Non è stato possibile installare lo skill del modello «%s»: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup'] = 'Configurazione presso %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copy'] = 'Copia';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copied'] = 'Copiato';
