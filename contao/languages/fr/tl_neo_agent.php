<?php

declare(strict_types=1);

// Legends
$GLOBALS['TL_LANG']['tl_neo_agent']['title_legend'] = 'Général';
$GLOBALS['TL_LANG']['tl_neo_agent']['identity_legend'] = 'Identité';
$GLOBALS['TL_LANG']['tl_neo_agent']['provider_legend'] = 'Modèle';
$GLOBALS['TL_LANG']['tl_neo_agent']['prompt_legend'] = 'Prompt système';
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_legend'] = 'Base de connaissances';
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_legend'] = 'Transfert et consentement';

// Fields
$GLOBALS['TL_LANG']['tl_neo_agent']['title'] = ['Désignation', 'Nom interne de cet assistant (visible uniquement dans le back-office).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['published'] = ['Actif', 'Un seul assistant actif est affiché sur le site.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['assistant_name'] = ['Nom affiché', 'Nom sous lequel l’assistant se présente et qui apparaît dans l’en-tête du chat (p. ex. « Jean »). Commun à toutes les langues.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['model'] = ['Modèle', 'Le modèle de langage de cet assistant. La route et la clé se saisissent sous « Accès et licences » › IA ; dès qu’une clé y est enregistrée, les modèles de la route sont proposés ici.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature'] = ['Température', 'Liberté de formulation du modèle : plus bas = plus factuel et constant. Pour un chat, 0,2 à 0,5 convient.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens'] = ['Longueur de réponse (max. tokens)', 'Longueur maximale d’une réponse. Pour les modèles qui réfléchissent, de la place pour la réflexion s’ajoute.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['system_prompt'] = ['Prompt système', 'Règles de comportement de l’assistant. {name} est remplacé par le nom affiché. Commun à toutes les langues.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_source'] = ['Source de connaissances', 'Source des faits vérifiés sur lesquels l’assistant s’appuie. Commune à toutes les langues.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_text'] = ['Texte de connaissances', 'Faits vérifiés sous forme de texte, ajoutés au prompt système comme contexte.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_file'] = ['Fichier de connaissances', 'Un fichier (txt/md/json) du gestionnaire de fichiers, utilisé comme base de connaissances.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_email'] = ['E-mail de transfert', 'Adresse à laquelle sont envoyées les demandes « transmettre à l’équipe ». Laisser vide s’il n’y en a pas – l’assistant ne proposera alors jamais de transmission et renverra vers les possibilités de contact du site.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_name'] = ['Transmettre à (nom)', 'Affiché sur les boutons du chat, p. ex. « Transmettre à Jean ». Laissez vide pour « l’équipe ».'];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_category'] = ['Catégorie de consentement', 'Catégorie de la bannière de cookies après le consentement de laquelle le chat apparaît. L’extension de consentement aux cookies de la même gamme fonctionne sans autre réglage ; un autre outil de consentement a besoin d’un petit script (documentation, « Customising »). Sans outil de consentement sur la page, le chat est disponible tout de suite.'];

// Operations
$GLOBALS['TL_LANG']['tl_neo_agent']['languages'] = ['Langues', 'Gérer les variantes linguistiques (message d’accueil, suggestions, questions-réponses …) de cet assistant.'];

// Reference (option labels)
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_sources'] = [
    'none' => 'Aucune',
    'text' => 'Texte (saisi)',
    'file' => 'Fichier du gestionnaire de fichiers',
    'llms' => 'Le llms.txt du site (recommandé)',
    'llms_text' => 'llms.txt + texte complémentaire personnel',
];

// Personality, web search, referral
$GLOBALS['TL_LANG']['tl_neo_agent']['persona_legend'] = 'Personnalité et style de réponse';
$GLOBALS['TL_LANG']['tl_neo_agent']['expert_legend'] = 'Paramètres avancés';
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address'] = ['Forme d’adresse', 'Comment l’assistant doit-il s’adresser aux visiteurs ?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length'] = ['Longueur des réponses', 'Quel niveau de détail pour les réponses ?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style'] = ['Ton', 'Le ton général des réponses.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['research_legend'] = 'Recherche web';
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_legend'] = 'Orientation — vers qui les visiteurs sont orientés';
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search'] = ['Autoriser la recherche web', 'L’assistant peut décider de lui-même de chercher une information sur le web pour répondre à une question — mais uniquement parmi les sources autorisées ci-dessous. Sinon, il répond uniquement à partir de la base de connaissances.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode'] = ['Service de recherche', '« Lire uniquement les sources autorisées » consulte directement les adresses listées (gratuit, idéal pour les llms.txt des fabricants). « SerpAPI » effectue en plus une recherche via un moteur de recherche — les résultats restent strictement limités aux domaines autorisés.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_sources'] = ['Sources autorisées', 'Une adresse par ligne : le site d’un partenaire ou d’un fabricant, son llms.txt ou un point d’accès aux données (p. ex. le catalogue d’une place de marché). Si la page d’accueil ne répond pas à la question, l’assistant suit les sous-pages pertinentes. Vous pouvez ajouter des mots-clés — « adresse | mot-clé1 mot-clé2 » — pour indiquer à quoi sert une source ; pour les adresses d’API, c’est nécessaire, car l’adresse seule ne le dit pas. Cette liste sert aussi de verrou : RIEN n’est consulté en dehors de ces domaines, même si un visiteur le demande à l’assistant.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max'] = ['Sources par question', 'Nombre maximal de sources que l’assistant lit pour une question. Plus de sources = de meilleures réponses, mais plus lentes et plus coûteuses.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_company'] = ['Toujours orienter vers', 'Pour la mise en place, la réalisation, l’intégration, la migration, l’assistance et l’achat, l’assistant oriente TOUJOURS vers ce prestataire — jamais vers le fabricant ou le développeur d’une solution tierce. Il peut citer des produits tiers ; l’interlocuteur reste toujours vous. Vide = aucune règle d’orientation.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution'] = ['Solution maison privilégiée', 'Si cette solution correspond à la question, l’assistant la cite en premier — honnêtement et uniquement lorsqu’elle convient vraiment. Vide = aucune préférence.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution_note'] = ['À quoi sert la solution', 'Une ou deux phrases : que fait-elle, à qui s’adresse-t-elle ? L’assistant s’en sert pour ne la mentionner que lorsqu’elle convient vraiment.'];

// Options (key = stored value)
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address_options'] = ['sie' => 'Vouvoiement', 'du' => 'Tutoiement'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length_options'] = ['short' => 'Courte (2–3 phrases)', 'medium' => 'Moyenne (4–6 phrases)', 'long' => 'Détaillée (avec une liste si utile)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style_options'] = ['professional' => 'Professionnel et factuel', 'friendly' => 'Aimable et chaleureux', 'expert' => 'Technique et précis'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode_options'] = ['sources' => 'Lire uniquement les sources autorisées (gratuit)', 'serpapi' => 'Recherche via SerpAPI — limitée aux domaines autorisés'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max_options'] = ['1' => '1 source', '2' => '2 sources', '3' => '3 sources', '4' => '4 sources', '5' => '5 sources'];
$GLOBALS['TL_LANG']['tl_neo_agent']['settings'] = ['Accès et licences', 'Accès IA, recherche web et licence du SuperAgent'];
$GLOBALS['TL_LANG']['tl_neo_agent']['ai_missing'] = 'Aucune clé IA n’est encore enregistrée. D’ici là, le site n’affiche pas le chat.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_stored'] = 'La clé SerpAPI est enregistrée sous « Accès et licences ».';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_missing'] = 'Aucune clé SerpAPI n’est encore enregistrée. D’ici là, l’assistant lit uniquement les sources autorisées.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link'] = 'Ouvrir';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link_missing'] = 'L’enregistrer maintenant';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_missing'] = 'Aucun assistant n’a encore ses propres textes pour %1$s (page de départ « %2$s ») ; le chat y affiche les textes par défaut.';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_create'] = 'Créer avec des suggestions pour « %s »';
$GLOBALS['TL_LANG']['tl_neo_agent']['safety_legend'] = 'Sécurité';
$GLOBALS['TL_LANG']['tl_neo_agent']['no_medical'] = ['Pas d’avis médical', 'L’assistant ne donne ni diagnostic ni conseil de traitement, de médicament ou de santé et refuse ces questions avec ses propres mots, selon la consigne de l’enregistrement de langue (p. ex. le numéro d’urgence). Une règle donnée à un modèle de langage n’exclut pas toute erreur.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_mode'] = ['Mode confidentialité', 'Le chat demande de ne pas saisir de données personnelles. Le serveur retire du message les adresses e-mail, numéros de téléphone, IBAN, numéros de carte, numéros de sécurité sociale et dates de naissance avant qu’il ne parte vers le service d’IA ; le modèle veille aux données de santé et similaires. Les lieux, pays et adresses sans nom ne comptent pas comme données personnelles (p. ex. questions sur la météo). Les fonctions qui nécessitent des données personnelles (p. ex. réserver ou consulter une réservation) ne sont alors pas possibles.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['contact_page'] = ['Page de contact', 'L’assistant peut ouvrir cette page lorsqu’un visiteur veut joindre une personne (licence Basic), en plus du formulaire dans le chat.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['avatar'] = ['Image', 'Affichée à la place de l’icône du robot dans la fenêtre de chat (carrée, p. ex. 96 × 96 px).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side'] = ['Position', 'Coin de l’écran pour le bouton et la fenêtre de chat. Si des boutons d’autres extensions (paramètres des cookies, accessibilité) se trouvent dans le même coin, ils sont disposés ensemble comme réglé dans la mise en page : un bouton de paramètres avec un menu, empilés ou côte à côte.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side_options'] = [
    'right' => 'En bas à droite',
    'left' => 'En bas à gauche',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort'] = ['Effort de réflexion', 'Le degré de réflexion du modèle avant de répondre. Plus de réflexion = plus précis, mais plus lent et plus cher. Pour un chat sur un site web, « Faible » suffit en général.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort_options'] = ['off' => 'Désactivé', 'low' => 'Faible (recommandé)', 'medium' => 'Moyen', 'high' => 'Élevé'];
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_reasoning'] = 'peut réfléchir';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_tools'] = 'peut utiliser des outils';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_max'] = 'jusqu’à %s jetons par réponse';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_no_tools'] = 'Ce modèle ne peut pas utiliser d’outils. La recherche web ne fonctionne pas avec lui ; la navigation et le transfert passent par des lignes de repli.';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_untested'] = 'Non testé avec les outils des connexions. Testés : %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_legend'] = 'Saisie vocale';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_input'] = ['Autoriser la saisie vocale', 'Les visiteurs peuvent dicter un message (60 secondes au maximum). L’enregistrement passe par votre serveur vers le service d’IA pour la transcription, n’est pas conservé, et le texte apparaît dans le champ de saisie pour être vérifié. Avec le mode confidentialité activé, uniquement avec « Saisie vocale aussi en mode confidentialité ».'];
$GLOBALS['TL_LANG']['tl_neo_agent']['transcription_model'] = ['Modèle de transcription', 'Modèle pour la transcription, issu de la liste de la route (chez Requesty, à approuver sous « Transcriptions »). Les modèles de Mistral (Voxtral) et de Scaleway, par exemple, traitent dans l’UE.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_privacy_off'] = 'Tant que le mode confidentialité est activé, il n’y a pas de saisie vocale : un enregistrement partirait non filtré vers le service de transcription. Activez « Saisie vocale aussi en mode confidentialité » ci-dessous pour l’autoriser.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_in_privacy'] = ['Saisie vocale aussi en mode confidentialité', 'Le micro reste disponible quand le mode confidentialité est activé. L’enregistrement part non filtré vers le service de transcription ; seul le texte est filtré : les adresses e-mail, numéros de téléphone et données semblables sont retirés avant qu’il n’apparaisse dans le champ de saisie et avant que quoi que ce soit n’atteigne le modèle du chat.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['forwarding_locked'] = 'Ne s’applique qu’avec une adresse e-mail de transfert ci-dessus : sans elle, le chat n’a pas de formulaire de transfert.';

// Publishing ("preview only") and the terms of the extension
$GLOBALS['TL_LANG']['tl_neo_agent']['publish_legend'] = 'Publication';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility'] = ['Visible pour', 'Les nouveaux assistants commencent en « Aperçu uniquement » : seuls les administrateurs connectés voient alors le chat, dans l’aperçu du front office. Testez-le là avant de le rendre visible à tous les visiteurs.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_options'] = [
    'preview' => 'Aperçu uniquement – seuls les administrateurs connectés voient le chat dans l’aperçu',
    'public' => 'Tous les visiteurs',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_note'] = '« Tous les visiteurs » peut être choisi dès que les conditions de l’extension sont confirmées.';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_locked'] = 'Veuillez d’abord confirmer les conditions sous « Accès et licences ». D’ici là, l’assistant reste en aperçu.';
$GLOBALS['TL_LANG']['tl_neo_agent']['preview_badge'] = 'Aperçu';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_missing'] = 'Avant qu’un assistant ne devienne visible pour les visiteurs, les conditions doivent être confirmées.';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_edition_missing'] = 'Les fonctions de l’édition %s sont verrouillées jusqu’à ce que ses conditions soient confirmées.';

// Forwarding form: optional fields and the newsletter checkbox
$GLOBALS['TL_LANG']['tl_neo_agent']['form_fields'] = ['Champs supplémentaires du formulaire', 'L’adresse e-mail et le message figurent toujours dans le formulaire et sont obligatoires. Ces champs s’ajoutent sur demande ; les données saisies partent uniquement par e-mail à l’adresse de transfert, jamais à l’IA.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_required'] = ['Dont obligatoires', 'Seuls les champs choisis à gauche peuvent être obligatoires.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_field_options'] = [
    'name' => 'Nom',
    'phone' => 'Téléphone',
    'company' => 'Entreprise',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_module'] = ['Case newsletter', 'Propose « S’abonner à la newsletter » dans le formulaire, jamais cochée d’avance. Choisissez un module de newsletter de ce site ; ses listes ou canaux et son e-mail de confirmation s’appliquent (double opt-in).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_off'] = 'Désactivé';
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_kinds'] = [
    'brevo' => 'Brevo',
    'contao' => 'Newsletter Contao',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_problem'] = [
    'missing' => 'Le module de newsletter choisi n’existe plus. Tant qu’aucun autre n’est choisi, le formulaire n’affiche pas de case newsletter.',
    'brevo_incomplete' => 'Il manque une liste, un modèle de confirmation ou une page de confirmation dans le module Brevo choisi. D’ici là, le formulaire n’affiche pas de case newsletter.',
    'brevo_key' => 'Aucune clé API Brevo n’est encore enregistrée (« Accès et licences »). D’ici là, le formulaire n’affiche pas de case newsletter.',
    'contao_channels' => 'Aucun canal n’est choisi dans le module « S’abonner » choisi. D’ici là, le formulaire n’affiche pas de case newsletter.',
    'contao_page' => 'Le module « S’abonner » choisi ne figure sur aucune page publiée. Le lien de confirmation mène à cette page ; tant que le module n’y est pas, le formulaire n’affiche pas de case newsletter.',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_none'] = 'Il n’existe pas encore de module de newsletter (Brevo ou « S’abonner » de Contao). Créez-en un, il pourra ensuite être choisi ici.';

// Choices instead of free text
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_options'] = [
    'none' => 'Aucun consentement nécessaire (chat disponible tout de suite)',
    'functional' => 'Fonctionnel',
    'statistics' => 'Statistiques',
    'marketing' => 'Marketing',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_custom'] = '%s (catégorie propre)';
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature_notes'] = [
    '0' => 'factuel, toujours formulé de la même façon',
    '0.4' => 'par défaut',
    '1' => 'varié',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens_option'] = '%1$s tokens (environ %2$s mots)';
$GLOBALS['TL_LANG']['tl_neo_agent']['form_consent'] = ['Demander le consentement au contact', 'Affiche une case obligatoire dans le formulaire : le visiteur accepte que ses données servent à la demande et qu’on le contacte. L’e-mail à votre équipe mentionne le consentement.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_page'] = ['Politique de confidentialité', 'Page vers laquelle renvoie la case. Vide = sans lien.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country'] = ['Indicatif présélectionné', 'Pour le champ téléphone. Vide = selon la langue de la page (p. ex. allemand → Allemagne). Les visiteurs peuvent choisir n’importe quel indicatif.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country_auto'] = 'Selon la langue de la page';

// Tools of the connections to MCP servers (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['connections'] = ['Connexions', 'Connecter des serveurs MCP, lire et verrouiller leurs outils (administrateurs uniquement).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tool_log'] = ['Journal', 'Appels d’outils des 30 derniers jours (administrateurs uniquement).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_legend'] = 'Outils des connexions';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_tools'] = ['Outils', 'Ce que cet assistant peut utiliser. Rien n’est coché par défaut, les outils verrouillés ne sont pas affichés. L’assistant décide lui-même quand appeler un outil.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_inactive'] = 'inactive';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_locked_tool'] = 'verrouillé';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_gone_group'] = 'N’existe plus';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_none'] = 'Il n’existe encore aucune connexion à un serveur MCP. Les administrateurs en créent une via « Connexions » au-dessus de la liste des assistants.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_open_connections'] = 'Ouvrir les connexions';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_privacy'] = 'Mode protection des données activé : ces outils demandent des données sur une personne (%s). Le serveur retire des messages les adresses e-mail et les numéros de téléphone, et l’assistant ne demande pas de données personnelles ; les outils ne reçoivent donc pas ces données.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model'] = 'Selon le service d’IA, le modèle choisi ne peut pas appeler d’outils. Choisissez un autre modèle, sinon les outils restent inutilisés.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_models'] = 'Les outils des connexions (p. ex. des réservations) demandent un modèle de langage puissant : il doit planifier plusieurs étapes, reprendre les informations de la conversation et appeler les outils l’un après l’autre. Les modèles plus faibles redemandent, sautent des étapes ou abandonnent. Testés avec le parcours de réservation du modèle de connexion apaleo : %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_tested'] = 'Le modèle choisi en fait partie.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_untested'] = 'Le modèle choisi n’en fait pas partie : vérifiez les parcours de vos connexions dans le chat avant que les visiteurs les utilisent.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_unusable'] = 'Cochés mais inutilisables : %d (verrouillés, n’existent plus ou connexion inactive). Le chat ne les utilise pas.';

// Installed skills (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['skill_list'] = ['Skills', 'Installer des skills depuis GitHub ou un fichier (administrateurs uniquement).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['skills_legend'] = 'Skills installés';
$GLOBALS['TL_LANG']['tl_neo_agent']['skills'] = ['Skills', 'Quels skills installés cet assistant peut utiliser. Rien n’est coché par défaut. Le modèle les voit dans son catalogue et charge lui-même un skill lorsqu’une question relève de son domaine.'];

// Consent category the cookie consent extension never asks for (reason at the field).
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_unoffered'] = 'Le module « %s » ne contient aucun service actif dans cette catégorie ; sa bannière ne demande donc jamais aux visiteurs leur consentement pour le chat : là où elle apparaît, le chat reste indisponible ; sur les pages sans bannière, il démarre sans demander. Ajoutez le service « Assistant de chat IA » dans cette catégorie ou choisissez une autre catégorie.';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_add'] = 'Ajouter le service maintenant';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_ask_admin'] = 'Un administrateur peut l’ajouter dans le module « %s ».';

// Languages and starter questions in the assistant's form (LanguageCallbacks::overview).
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_legend'] = 'Langues et questions de départ';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_intro'] = 'Un assistant a une seule langue : accueil, questions de départ, questions et réponses et textes de la fenêtre de chat sont dans cette langue. Pour chaque autre langue du site, créez un assistant distinct (copiez-le, changez la langue) et choisissez-le dans le module de chat de cette langue.';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_none'] = 'Aucune langue pour l’instant : le chat affiche les textes par défaut et aucune question de départ.';
$GLOBALS['TL_LANG']['tl_neo_agent']['starters_none'] = 'Aucune question de départ';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_edit'] = 'Modifier les textes et les questions de départ';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_add'] = 'Ajouter une langue';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_answer'] = 'Réponse :';
