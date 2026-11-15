<?php

declare(strict_types=1);

// Connections to MCP servers (edition Plus), administrators only.
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['new'] = ['Nueva conexión', 'Conectar un servidor MCP.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['edit'] = ['Editar', 'Editar la conexión.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['delete'] = ['Eliminar', 'Eliminar la conexión.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['show'] = ['Detalles', 'Mostrar los detalles.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['back_to_agents'] = ['Asistentes', 'Volver a la lista de asistentes.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['title_legend'] = 'Conexión';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_legend'] = 'Acceso';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['data_legend'] = 'Datos personales';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_legend'] = 'Herramientas';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_legend'] = 'Confirmación';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['name'] = ['Nombre', 'El nombre de la conexión en la lista y en la elección del asistente, p. ej. «Reserva de citas».'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url'] = ['Dirección del servidor MCP', 'La dirección que el proveedor indica para su servidor MCP, p. ej. https://mcp.example.com/mcp. Solo HTTPS y solo direcciones públicas.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth'] = ['Acceso', 'Cómo se identifica el sitio web ante el servidor. Lo indica la guía del proveedor.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_options'] = [
    'none' => 'Sin acceso',
    'key' => 'Clave (Authorization: Bearer)',
    'headers' => 'Cabeceras propias',
    'oauth' => 'OAuth (acceso en el servicio)',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data'] = ['Datos personales', 'Se aplica en ambos sentidos: a lo que el asistente envía al servicio y a los resultados que van al servicio de IA. Se reconocen direcciones de correo electrónico, números de teléfono, IBAN, números de tarjeta, números de la seguridad social y fechas de nacimiento, no nombres ni direcciones.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data_options'] = [
    'mask' => 'Enmascarar: sustituir por un marcador',
    'block' => 'No enviar: retener la llamada o el resultado',
    'send' => 'Enviar todo',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['api_key'] = ['Clave', 'Se envía como «Authorization: Bearer …». Se guarda cifrada y no se vuelve a mostrar. Dejarla vacía conserva la clave guardada.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['headers'] = ['Cabeceras propias', 'Nombre y valor por fila, p. ej. «X-Api-Key». Los valores se guardan cifrados y no se vuelven a mostrar; un valor vacío conserva el guardado.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_id'] = ['ID de cliente (solo app propia)', 'Solo es necesario si el servicio no ofrece registro automático: cree allí una app, introduzca allí la dirección de redirección de arriba y aquí el ID de cliente. Vacío = registro automático.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_secret'] = ['Secreto de cliente (solo app propia)', 'El secreto de la app, si el servicio emite uno. Se guarda cifrado y no se vuelve a mostrar.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['warning'] = 'Atención: a través de un servidor MCP, el asistente puede llamar a funciones de otro sistema, incluidas funciones que allí modifican o eliminan datos o muestran datos de clientes. Cada visitante del chat trabaja con el acceso de esta conexión. Habilite solo herramientas de bajo riesgo o limitadas por un skill, y pruebe el asistente antes de hacerlo visible para los visitantes. Las herramientas que según el servidor modifican o eliminan datos, o que no están marcadas, están bloqueadas por defecto. Aun así queda un riesgo residual, porque un modelo de lenguaje puede equivocarse o ser engañado por mensajes hábilmente redactados. El fabricante de esta extensión no asume ninguna responsabilidad por los daños causados por las herramientas habilitadas. Qué se aplica exactamente y qué excepciones existen figura en las condiciones de licencia.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_active'] = 'activa · %d de %d herramientas habilitadas';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unread'] = 'herramientas no leídas';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unconfirmed'] = 'no confirmada';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url_https'] = 'Introduzca una dirección que empiece por https://.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['header_name'] = 'El nombre de cabecera «%s» no es válido. Se permiten letras, cifras y guiones.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_heading'] = 'Acceso en el servicio';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connected'] = 'Acceso realizado por %s el %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_not_connected'] = 'Todavía sin acceso. Guarde la dirección y luego «Conectar»: irá a la página de acceso del servicio y después volverá aquí.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connect'] = 'Conectar';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_reconnect'] = 'Volver a conectar';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_disconnect'] = 'Cerrar sesión';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_redirect'] = 'Dirección de redirección de este sitio web (solo necesaria para una app propia en el servicio):';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_forgotten'] = 'Se ha eliminado el acceso en el servicio.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_failed'] = 'El acceso en el servicio ha fallado: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_done'] = 'Se ha guardado el acceso en el servicio.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_unread'] = 'Las herramientas aún no se han leído. Se leen después de guardar, en cuanto el acceso esté completo.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_url_changed'] = 'La dirección ha cambiado. Las herramientas se vuelven a leer al guardar; hasta entonces la conexión no está activa.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_read'] = '%d herramientas de %s, leídas el %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_reread'] = 'Volver a leer las herramientas';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_done'] = 'Herramientas leídas: %d, de ellas bloqueadas: %d.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_failed'] = 'No se han podido leer las herramientas: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_heading'] = 'Herramientas y bloqueos';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['lock_all'] = 'Bloquear todas las herramientas que según el servidor modifican o eliminan datos, o que no están marcadas';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_locked'] = 'Bloqueo';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_tool'] = 'Herramienta';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_kind'] = 'Tipo según el servidor';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_note'] = 'Nota';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_locked'] = 'bloqueada';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_new'] = 'NUEVA';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_personal'] = 'necesita datos personales';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_help'] = 'Ningún asistente puede usar una herramienta bloqueada. Quien levanta un bloqueo lo confirma abajo; se guarda quién lo hizo y cuándo. Las herramientas nuevas del servidor no están marcadas en ningún asistente.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['kind_options'] = [
    'read' => 'solo lee',
    'change' => 'modifica datos',
    'delete' => 'elimina datos',
    'unmarked' => 'sin marcar',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['unlock_confirm'] = 'Bloqueo no levantado: %s. Para levantarlo, confirme abajo que ha revisado las herramientas.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirmed'] = 'Confirmada por %s el %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['not_confirmed'] = 'Todavía no confirmada. Solo después pueden los asistentes usar las herramientas de esta conexión.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_check'] = 'He revisado las herramientas habilitadas y entiendo el riesgo residual.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_help'] = 'Necesario para la primera habilitación de la conexión y cada vez que levante un bloqueo.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template'] = ['Plantilla', 'Rellena el nombre, la dirección y el acceso mientras estos campos estén vacíos, y define cómo se clasifican y bloquean las herramientas de este servicio. Un skill de la plantilla se instala al guardar. Dejar vacío para cualquier servidor MCP.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_help'] = 'Guía del proveedor:';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_recommended'] = 'recomendado';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_customer_data'] = 'muestra datos de clientes';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_own_app'] = 'Este servicio necesita una app propia: cree una app en el servicio e introduzca allí la dirección de redirección que figura abajo. El ID de cliente y el secreto de cliente de la app van en los campos de debajo. Después «Conectar».';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_installed'] = 'El skill «%s» de la plantilla está instalado. Márquelo en los asistentes que deban usarlo.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_failed'] = 'No se pudo instalar el skill de la plantilla «%s»: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup'] = 'Configuración en %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copy'] = 'Copiar';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copied'] = 'Copiado';
