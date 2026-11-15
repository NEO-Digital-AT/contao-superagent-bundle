<?php

declare(strict_types=1);

// Legends
$GLOBALS['TL_LANG']['tl_neo_agent']['title_legend'] = 'General';
$GLOBALS['TL_LANG']['tl_neo_agent']['identity_legend'] = 'Identidad';
$GLOBALS['TL_LANG']['tl_neo_agent']['provider_legend'] = 'Modelo';
$GLOBALS['TL_LANG']['tl_neo_agent']['prompt_legend'] = 'Instrucciones del sistema';
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_legend'] = 'Base de conocimiento';
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_legend'] = 'Derivación y consentimiento';

// Fields
$GLOBALS['TL_LANG']['tl_neo_agent']['title'] = ['Denominación', 'Nombre interno de este asistente (solo en el back end).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['published'] = ['Activo', 'En el front end solo se muestra un asistente activo.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['assistant_name'] = ['Nombre visible', 'Cómo se presenta el asistente y qué nombre aparece en la cabecera del chat (p. ej. “Juan”). Común a todos los idiomas.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['model'] = ['Modelo', 'El modelo de lenguaje de este asistente. La ruta y la clave se introducen en «Accesos y licencias» › IA; en cuanto se guarda allí una clave, aquí se ofrecen los modelos de la ruta.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature'] = ['Temperatura', 'Con cuánta libertad formula el modelo: más bajo = más objetivo y constante. Para un chat va bien de 0,2 a 0,5.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens'] = ['Longitud de respuesta (máx. tokens)', 'Longitud máxima de una respuesta. En los modelos que razonan se añade espacio para razonar.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['system_prompt'] = ['Instrucciones del sistema', 'Reglas de comportamiento del asistente. {name} se sustituye por el nombre visible. Comunes a todos los idiomas.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_source'] = ['Fuente de conocimiento', 'Origen de los datos verificados en los que se basa el asistente. Común a todos los idiomas.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_text'] = ['Texto de conocimiento', 'Datos verificados en forma de texto, que se añaden como contexto a las instrucciones del sistema.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_file'] = ['Archivo de conocimiento', 'Un archivo (txt/md/json) del gestor de archivos que se usa como base de conocimiento.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_email'] = ['Correo de derivación', 'Dirección a la que se envían las solicitudes “reenviar al equipo”. Déjelo vacío si no hay ninguna: el asistente no ofrecerá nunca el reenvío y remitirá a las opciones de contacto del sitio web.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_name'] = ['Reenviar a (nombre)', 'Aparece en los botones del chat, p. ej. “Reenviar a Juan”. Déjelo vacío para “el equipo”.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_category'] = ['Categoría de consentimiento', 'Categoría del banner de cookies tras cuyo consentimiento aparece el chat. La extensión de consentimiento de cookies de la misma serie funciona sin más configuración; otra herramienta de consentimiento necesita un pequeño script (documentación, «Customising»). Sin herramienta de consentimiento en la página, el chat está disponible de inmediato.'];

// Operations
$GLOBALS['TL_LANG']['tl_neo_agent']['languages'] = ['Idiomas', 'Gestionar las variantes de idioma (saludo, sugerencias, preguntas y respuestas …) de este asistente.'];

// Reference (option labels)
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_sources'] = [
    'none' => 'Ninguna',
    'text' => 'Texto (introducido)',
    'file' => 'Archivo del gestor de archivos',
    'llms' => 'El llms.txt del sitio web (recomendado)',
    'llms_text' => 'llms.txt + texto adicional propio',
];

// Personality, web search, referral
$GLOBALS['TL_LANG']['tl_neo_agent']['persona_legend'] = 'Personalidad y estilo de respuesta';
$GLOBALS['TL_LANG']['tl_neo_agent']['expert_legend'] = 'Ajustes avanzados';
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address'] = ['Tratamiento', '¿Cómo debe dirigirse el asistente a los visitantes?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length'] = ['Longitud de las respuestas', '¿Cuán detalladas deben ser las respuestas?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style'] = ['Tono', 'El tono básico de las respuestas.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['research_legend'] = 'Búsqueda web';
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_legend'] = 'Derivación: a quién se remite a los visitantes';
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search'] = ['Permitir búsqueda web', 'El asistente puede decidir por sí mismo consultar algo en internet para responder una pregunta, pero solo en las fuentes autorizadas más abajo. Sin esta opción responde únicamente con la base de conocimiento.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode'] = ['Servicio de búsqueda', '“Leer solo fuentes autorizadas” consulta directamente las direcciones indicadas (gratis, ideal para el llms.txt de los fabricantes). “SerpAPI” busca además mediante un buscador; los resultados se siguen filtrando estrictamente a los dominios autorizados.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_sources'] = ['Fuentes autorizadas', 'Una dirección por línea: el sitio de un socio o fabricante, su llms.txt o un punto de datos (p. ej. el catálogo de un marketplace). Si la página de inicio no responde a la pregunta, el asistente sigue las subpáginas adecuadas. Opcionalmente puede añadir palabras clave —“dirección | palabra1 palabra2”— para que sepa para qué sirve cada fuente; en las direcciones de API es necesario, porque la dirección no lo indica. Esta lista es también el candado: NO se consulta NADA fuera de estos dominios, aunque un visitante se lo pida al asistente.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max'] = ['Fuentes por pregunta', 'Cuántas fuentes lee como máximo el asistente para una pregunta. Más fuentes = mejores respuestas, pero más lentas y más caras.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_company'] = ['Remitir siempre a', 'Para configuración, implantación, integración, migración, soporte y compra, el asistente remite SIEMPRE a este proveedor, nunca al fabricante o desarrollador de una solución de terceros. Puede mencionar productos de terceros; el contacto sigue siendo siempre usted. Vacío = sin regla de derivación.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution'] = ['Solución propia preferida', 'Si esta solución encaja con la pregunta, el asistente la menciona en primer lugar, con honestidad y solo cuando de verdad encaja. Vacío = sin preferencia.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution_note'] = ['Para qué sirve la solución', 'Una o dos frases: ¿qué hace y a quién va dirigida? El asistente lo usa para mencionarla solo cuando de verdad encaja.'];

// Options (key = stored value)
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address_options'] = ['sie' => 'Formal (usted)', 'du' => 'Informal (tú)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length_options'] = ['short' => 'Breve (2–3 frases)', 'medium' => 'Media (4–6 frases)', 'long' => 'Detallada (con lista cuando ayuda)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style_options'] = ['professional' => 'Profesional y objetivo', 'friendly' => 'Amable y cercano', 'expert' => 'Técnico y preciso'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode_options'] = ['sources' => 'Leer solo fuentes autorizadas (gratis)', 'serpapi' => 'Buscar con SerpAPI, limitado a los dominios autorizados'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max_options'] = ['1' => '1 fuente', '2' => '2 fuentes', '3' => '3 fuentes', '4' => '4 fuentes', '5' => '5 fuentes'];
$GLOBALS['TL_LANG']['tl_neo_agent']['settings'] = ['Accesos y licencias', 'Acceso IA, búsqueda web y licencia del SuperAgent'];
$GLOBALS['TL_LANG']['tl_neo_agent']['ai_missing'] = 'Todavía no hay ninguna clave de IA guardada. Hasta entonces, el sitio no muestra el chat.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_stored'] = 'La clave de SerpAPI está guardada en «Accesos y licencias».';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_missing'] = 'Todavía no hay ninguna clave de SerpAPI guardada. Hasta entonces, el asistente solo lee las fuentes autorizadas.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link'] = 'Abrir';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link_missing'] = 'Introducirla ahora';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_missing'] = 'Ningún asistente tiene todavía textos propios para %1$s (página de inicio «%2$s»); el chat muestra allí los textos predeterminados.';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_create'] = 'Crear con sugerencias para «%s»';
$GLOBALS['TL_LANG']['tl_neo_agent']['safety_legend'] = 'Seguridad';
$GLOBALS['TL_LANG']['tl_neo_agent']['no_medical'] = ['Sin consejo médico', 'El asistente no da diagnósticos ni consejos sobre tratamientos, medicamentos o salud y rechaza esas preguntas con sus propias palabras, según la indicación del registro de idioma (p. ej. el número de emergencias). Una regla para un modelo de lenguaje no descarta todo error.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_mode'] = ['Modo de privacidad', 'El chat pide no introducir datos personales. El servidor elimina del mensaje direcciones de correo, números de teléfono, IBAN, números de tarjeta, números de la seguridad social y fechas de nacimiento antes de que llegue al servicio de IA; el modelo vigila los datos de salud y similares. Lugares, países y direcciones sin un nombre no cuentan como datos personales (p. ej. preguntas sobre el tiempo). Las funciones que necesitan datos personales (p. ej. reservar o consultar una reserva) no son posibles entonces.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['contact_page'] = ['Página de contacto', 'El asistente puede abrir esta página cuando un visitante quiere contactar con una persona (licencia Basic), además del formulario del chat.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['avatar'] = ['Imagen', 'Se muestra en la ventana del chat en lugar del icono del robot (cuadrada, p. ej. 96 × 96 px).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side'] = ['Posición', 'Esquina de la pantalla para el botón y la ventana del chat. Si en la misma esquina hay botones de otras extensiones (configuración de cookies, accesibilidad), se ordenan juntos según el diseño de página: un botón de configuración con menú, apilados o uno al lado del otro.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side_options'] = [
    'right' => 'Abajo a la derecha',
    'left' => 'Abajo a la izquierda',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort'] = ['Nivel de razonamiento', 'Cuánto reflexiona el modelo antes de responder. Más razonamiento = más preciso, pero más lento y más caro. Para un chat en un sitio web suele bastar «Bajo».'];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort_options'] = ['off' => 'Desactivado', 'low' => 'Bajo (recomendado)', 'medium' => 'Medio', 'high' => 'Alto'];
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_reasoning'] = 'puede razonar';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_tools'] = 'puede usar herramientas';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_max'] = 'hasta %s tokens por respuesta';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_no_tools'] = 'Este modelo no puede usar herramientas. La búsqueda web no funciona con él; la navegación y el reenvío funcionan mediante líneas sustitutivas.';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_untested'] = 'No probado con herramientas de conexiones. Probados: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_legend'] = 'Entrada de voz';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_input'] = ['Permitir entrada de voz', 'Los visitantes pueden dictar un mensaje (60 segundos como máximo). La grabación pasa por su servidor al servicio de IA para la transcripción, no se guarda, y el texto aparece en el campo de entrada para revisarlo. Con el modo de privacidad activado, solo junto con «Entrada de voz también en modo de privacidad».'];
$GLOBALS['TL_LANG']['tl_neo_agent']['transcription_model'] = ['Modelo de transcripción', 'Modelo para la transcripción, de la lista de la ruta (en Requesty, apruébelo en «Transcriptions»). Procesan en la UE, por ejemplo, los modelos de Mistral (Voxtral) y Scaleway.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_privacy_off'] = 'Mientras el modo de privacidad esté activado, no hay entrada de voz: una grabación llegaría sin filtrar al servicio de transcripción. Para permitirla, active abajo «Entrada de voz también en modo de privacidad».';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_in_privacy'] = ['Entrada de voz también en modo de privacidad', 'El micrófono sigue disponible con el modo de privacidad activado. La grabación llega sin filtrar al servicio de transcripción; solo se filtra el texto: las direcciones de correo electrónico, los números de teléfono y datos similares se eliminan antes de que aparezca en el campo de entrada y antes de que nada llegue al modelo del chat.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['forwarding_locked'] = 'Solo tiene efecto con una dirección de correo electrónico para reenvíos arriba: sin ella, el chat no tiene formulario de reenvío.';

// Publishing ("preview only") and the terms of the extension
$GLOBALS['TL_LANG']['tl_neo_agent']['publish_legend'] = 'Publicación';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility'] = ['Visible para', 'Los asistentes nuevos empiezan en «Solo vista previa»: entonces solo los administradores con sesión iniciada ven el chat, en la vista previa del front end. Pruébelo allí antes de hacerlo visible para todos los visitantes.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_options'] = [
    'preview' => 'Solo vista previa – solo los administradores con sesión iniciada ven el chat en la vista previa',
    'public' => 'Todos los visitantes',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_note'] = '«Todos los visitantes» se puede elegir en cuanto se confirmen las condiciones de la extensión.';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_locked'] = 'Confirme primero las condiciones en «Accesos y licencias». Hasta entonces, el asistente sigue en vista previa.';
$GLOBALS['TL_LANG']['tl_neo_agent']['preview_badge'] = 'Vista previa';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_missing'] = 'Antes de que un asistente sea visible para los visitantes, deben confirmarse las condiciones.';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_edition_missing'] = 'Las funciones de la edición %s están bloqueadas hasta que se confirmen sus condiciones.';

// Forwarding form: optional fields and the newsletter checkbox
$GLOBALS['TL_LANG']['tl_neo_agent']['form_fields'] = ['Campos adicionales del formulario', 'La dirección de correo electrónico y el mensaje están siempre en el formulario y son obligatorios. Estos campos se añaden si se desea; los datos introducidos solo se envían por correo electrónico a la dirección de reenvío, nunca a la IA.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_required'] = ['De ellos, obligatorios', 'Solo pueden ser obligatorios los campos elegidos a la izquierda.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_field_options'] = [
    'name' => 'Nombre',
    'phone' => 'Teléfono',
    'company' => 'Empresa',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_module'] = ['Casilla del boletín', 'Ofrece «Suscribirse al boletín» en el formulario, nunca marcada de antemano. Se elige un módulo de boletín de este sitio; se aplican sus listas o canales y su correo de confirmación (doble opt-in).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_off'] = 'Desactivado';
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_kinds'] = [
    'brevo' => 'Brevo',
    'contao' => 'Boletín de Contao',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_problem'] = [
    'missing' => 'El módulo de boletín elegido ya no existe. Hasta que se elija otro, el formulario no muestra la casilla del boletín.',
    'brevo_incomplete' => 'En el módulo de Brevo elegido falta una lista, una plantilla de confirmación o una página de confirmación. Hasta entonces, el formulario no muestra la casilla del boletín.',
    'brevo_key' => 'Todavía no hay ninguna clave API de Brevo guardada («Accesos y licencias»). Hasta entonces, el formulario no muestra la casilla del boletín.',
    'contao_channels' => 'En el módulo «Suscribirse» elegido no hay ningún canal seleccionado. Hasta entonces, el formulario no muestra la casilla del boletín.',
    'contao_page' => 'El módulo «Suscribirse» elegido no está en ninguna página publicada. El enlace de confirmación lleva a esa página; mientras el módulo no esté allí, el formulario no muestra la casilla del boletín.',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_none'] = 'Todavía no hay ningún módulo de boletín (Brevo o «Suscribirse» de Contao). Cree uno y podrá elegirlo aquí.';

// Choices instead of free text
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_options'] = [
    'none' => 'No se necesita consentimiento (chat disponible de inmediato)',
    'functional' => 'Funcional',
    'statistics' => 'Estadística',
    'marketing' => 'Marketing',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_custom'] = '%s (categoría propia)';
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature_notes'] = [
    '0' => 'objetivo, siempre igual',
    '0.4' => 'predeterminado',
    '1' => 'variado',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens_option'] = '%1$s tokens (unas %2$s palabras)';
$GLOBALS['TL_LANG']['tl_neo_agent']['form_consent'] = ['Pedir consentimiento para el contacto', 'Muestra en el formulario una casilla obligatoria: el visitante acepta que sus datos se usen para la consulta y que se le contacte. El correo a su equipo indica el consentimiento.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_page'] = ['Política de privacidad', 'Página a la que enlaza la casilla. Vacío = sin enlace.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country'] = ['Prefijo preseleccionado', 'Para el campo de teléfono. Vacío = según el idioma de la página (p. ej. alemán → Alemania). Los visitantes pueden elegir cualquier prefijo.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country_auto'] = 'Según el idioma de la página';

// Tools of the connections to MCP servers (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['connections'] = ['Conexiones', 'Conectar servidores MCP, leer y bloquear sus herramientas (solo administradores).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tool_log'] = ['Registro', 'Llamadas a herramientas de los últimos 30 días (solo administradores).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_legend'] = 'Herramientas de las conexiones';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_tools'] = ['Herramientas', 'Lo que este asistente puede usar. Por defecto no hay nada marcado y las herramientas bloqueadas no se muestran. El asistente decide por sí mismo cuándo llamar a una herramienta.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_inactive'] = 'no activa';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_locked_tool'] = 'bloqueada';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_gone_group'] = 'Ya no disponible';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_none'] = 'Todavía no hay ninguna conexión con un servidor MCP. Los administradores la crean con «Conexiones» encima de la lista de asistentes.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_open_connections'] = 'Abrir las conexiones';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_privacy'] = 'Modo de protección de datos activado: estas herramientas piden datos de una persona (%s). El servidor elimina de los mensajes las direcciones de correo electrónico y los números de teléfono, y el asistente no pide datos personales, así que las herramientas no reciben estos datos.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model'] = 'Según el servicio de IA, el modelo elegido no puede llamar a herramientas. Elija otro modelo; de lo contrario, las herramientas quedan sin usar.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_models'] = 'Las herramientas de las conexiones (p. ej. reservas) necesitan un modelo de lenguaje potente: debe planificar varios pasos, tomar los datos de la conversación y llamar a las herramientas una tras otra. Los modelos más débiles vuelven a preguntar, se saltan pasos o se rinden. Probados con el proceso de reserva de la plantilla apaleo: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_tested'] = 'El modelo elegido está entre ellos.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_untested'] = 'El modelo elegido no está entre ellos: compruebe los procesos de sus conexiones en el chat antes de que los usen los visitantes.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_unusable'] = 'Marcadas pero no utilizables: %d (bloqueadas, ya no disponibles o conexión no activa). El chat no las usa.';

// Installed skills (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['skill_list'] = ['Skills', 'Instalar skills desde GitHub o como archivo (solo administradores).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['skills_legend'] = 'Skills instalados';
$GLOBALS['TL_LANG']['tl_neo_agent']['skills'] = ['Skills', 'Qué skills instalados puede usar este asistente. Por defecto no hay nada marcado. El modelo los ve en su catálogo y carga un skill por sí mismo cuando una pregunta entra en su ámbito.'];

// Consent category the cookie consent extension never asks for (reason at the field).
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_unoffered'] = 'El módulo «%s» no tiene ningún servicio activo en esta categoría, por lo que el banner nunca pide a los visitantes su consentimiento para el chat: donde aparece el banner, el chat no está disponible; en las páginas sin banner se inicia sin preguntar. Añada el servicio «Asistente de chat con IA» en esta categoría o elija otra categoría.';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_add'] = 'Añadir el servicio ahora';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_ask_admin'] = 'Un administrador puede añadirlo en el módulo «%s».';

// Languages and starter questions in the assistant's form (LanguageCallbacks::overview).
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_legend'] = 'Idiomas y preguntas iniciales';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_intro'] = 'Un asistente tiene un solo idioma: el saludo, las preguntas iniciales, las preguntas y respuestas y los textos de la ventana del chat están en ese idioma. Para cada idioma adicional del sitio, cree un asistente propio (cópielo y cambie el idioma) y elíjalo en el módulo de chat de ese idioma.';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_none'] = 'Todavía no hay ningún idioma: el chat muestra los textos predeterminados y ninguna pregunta inicial.';
$GLOBALS['TL_LANG']['tl_neo_agent']['starters_none'] = 'Ninguna pregunta inicial';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_edit'] = 'Editar textos y preguntas iniciales';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_add'] = 'Añadir idioma';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_answer'] = 'Respuesta:';
