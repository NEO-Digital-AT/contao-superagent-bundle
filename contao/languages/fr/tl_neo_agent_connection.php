<?php

declare(strict_types=1);

// Connections to MCP servers (edition Plus), administrators only.
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['new'] = ['Nouvelle connexion', 'Connecter un serveur MCP.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['edit'] = ['Modifier', 'Modifier la connexion.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['delete'] = ['Supprimer', 'Supprimer la connexion.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['show'] = ['Détails', 'Afficher les détails.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['back_to_agents'] = ['Assistants', 'Retour à la liste des assistants.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['title_legend'] = 'Connexion';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_legend'] = 'Authentification';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['data_legend'] = 'Données personnelles';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_legend'] = 'Outils';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_legend'] = 'Confirmation';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['name'] = ['Nom', 'Nom de la connexion dans la liste et dans le choix de l’assistant, p. ex. « Prise de rendez-vous ».'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url'] = ['Adresse du serveur MCP', 'L’adresse que le fournisseur indique pour son serveur MCP, p. ex. https://mcp.example.com/mcp. HTTPS et adresses publiques uniquement.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth'] = ['Authentification', 'Comment le site s’authentifie auprès du serveur. Le guide du fournisseur l’indique.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_options'] = [
    'none' => 'Sans authentification',
    'key' => 'Clé (Authorization: Bearer)',
    'headers' => 'En-têtes propres',
    'oauth' => 'OAuth (connexion au service)',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data'] = ['Données personnelles', 'S’applique dans les deux sens : à ce que l’assistant envoie au service et aux résultats transmis au service d’IA. Sont reconnus les adresses e-mail, numéros de téléphone, IBAN, numéros de carte, numéros de sécurité sociale et dates de naissance, pas les noms ni les adresses.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data_options'] = [
    'mask' => 'Masquer : remplacer par un espace réservé',
    'block' => 'Ne pas envoyer : retenir l’appel ou le résultat',
    'send' => 'Tout envoyer',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['api_key'] = ['Clé', 'Envoyée sous la forme « Authorization: Bearer … ». Enregistrée chiffrée et jamais réaffichée. Laisser vide conserve la clé enregistrée.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['headers'] = ['En-têtes propres', 'Nom et valeur par ligne, p. ex. « X-Api-Key ». Les valeurs sont enregistrées chiffrées et jamais réaffichées ; une valeur vide conserve celle enregistrée.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_id'] = ['ID client (application propre uniquement)', 'Nécessaire seulement si le service n’offre pas d’enregistrement automatique : créez-y une application, saisissez-y l’adresse de redirection ci-dessus et ici l’ID client. Vide = enregistrement automatique.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_secret'] = ['Secret client (application propre uniquement)', 'Le secret de l’application, si le service en attribue un. Enregistré chiffré et jamais réaffiché.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['warning'] = 'Attention : via un serveur MCP, l’assistant peut appeler des fonctions d’un autre système – y compris des fonctions qui y modifient ou suppriment des données ou affichent des données clients. Chaque visiteur du chat utilise alors l’accès de cette connexion. N’autorisez que des outils présentant peu de risques ou limités par un skill, et testez l’assistant avant de le rendre visible aux visiteurs. Les outils que le serveur signale comme modifiant ou supprimant des données, ou qu’il ne signale pas, sont verrouillés par défaut. Un risque résiduel subsiste, car un modèle de langage peut se tromper ou être induit en erreur par des messages habilement formulés. Le fabricant de cette extension décline toute responsabilité pour les dommages causés par les outils autorisés. Ce qui s’applique exactement et les exceptions figurent dans les conditions de licence.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_active'] = 'active · %d outils sur %d autorisés';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unread'] = 'outils non lus';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unconfirmed'] = 'non confirmée';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url_https'] = 'Veuillez saisir une adresse commençant par https://.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['header_name'] = 'Le nom d’en-tête « %s » n’est pas valide. Lettres, chiffres et traits d’union sont autorisés.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_heading'] = 'Connexion au service';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connected'] = 'Connecté par %s le %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_not_connected'] = 'Pas encore connecté. Enregistrez l’adresse, puis « Connecter » : vous êtes dirigé vers la page de connexion du service et revenez ici ensuite.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connect'] = 'Connecter';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_reconnect'] = 'Reconnecter';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_disconnect'] = 'Se déconnecter';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_redirect'] = 'Adresse de redirection de ce site (nécessaire uniquement pour une application propre auprès du service) :';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_forgotten'] = 'La connexion au service a été supprimée.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_failed'] = 'La connexion au service a échoué : %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_done'] = 'La connexion au service a été enregistrée.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_unread'] = 'Les outils n’ont pas encore été lus. Ils sont lus après l’enregistrement, dès que l’authentification est complète.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_url_changed'] = 'L’adresse a changé. Les outils sont relus lors de l’enregistrement ; d’ici là, la connexion n’est pas active.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_read'] = '%d outils de %s, lus le %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_reread'] = 'Relire les outils';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_done'] = '%d outils lus, dont %d verrouillés.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_failed'] = 'Les outils n’ont pas pu être lus : %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_heading'] = 'Outils et verrouillages';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['lock_all'] = 'Verrouiller tous les outils que le serveur signale comme modifiant ou supprimant des données, ou qu’il ne signale pas';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_locked'] = 'Verrou';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_tool'] = 'Outil';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_kind'] = 'Type selon le serveur';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_note'] = 'Remarque';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_locked'] = 'verrouillé';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_new'] = 'NOUVEAU';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_personal'] = 'nécessite des données personnelles';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_help'] = 'Aucun assistant ne peut utiliser un outil verrouillé. Qui lève un verrouillage le confirme ci-dessous ; qui l’a fait et quand est conservé. Les nouveaux outils du serveur ne sont cochés pour aucun assistant.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['kind_options'] = [
    'read' => 'lecture seule',
    'change' => 'modifie des données',
    'delete' => 'supprime des données',
    'unmarked' => 'non signalé',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['unlock_confirm'] = 'Verrouillage non levé : %s. Pour le lever, confirmez ci-dessous que vous avez vérifié les outils.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirmed'] = 'Confirmé par %s le %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['not_confirmed'] = 'Pas encore confirmée. Ce n’est qu’ensuite que les assistants peuvent utiliser les outils de cette connexion.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_check'] = 'J’ai vérifié les outils autorisés et je comprends le risque résiduel.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_help'] = 'Nécessaire pour la première autorisation de la connexion et chaque fois que vous levez un verrouillage.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template'] = ['Modèle', 'Remplit le nom, l’adresse et la connexion tant que ces champs sont vides, et définit comment les outils de ce service sont classés et verrouillés. Un skill du modèle est installé à l’enregistrement. Laisser vide pour un serveur MCP quelconque.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_help'] = 'Guide du fournisseur :';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_recommended'] = 'recommandé';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_customer_data'] = 'affiche des données clients';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_own_app'] = 'Ce service nécessite une application propre : créez une application auprès du service et indiquez-y l’adresse de redirection affichée ci-dessous. L’ID client et le secret client de l’application vont dans les champs en dessous. Ensuite « Connecter ».';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_installed'] = 'Le skill « %s » du modèle est installé. Cochez-le pour les assistants qui doivent l’utiliser.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_failed'] = 'Le skill du modèle « %s » n’a pas pu être installé : %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup'] = 'Configuration chez %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copy'] = 'Copier';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copied'] = 'Copié';
