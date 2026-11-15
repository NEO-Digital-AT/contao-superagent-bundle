<?php

declare(strict_types=1);

// Connections to MCP servers (edition Plus), administrators only.
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['new'] = ['New connection', 'Connect a MCP server.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['edit'] = ['Edit', 'Edit the connection.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['delete'] = ['Delete', 'Delete the connection.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['show'] = ['Details', 'Show the details.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['back_to_agents'] = ['Assistants', 'Back to the list of assistants.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['title_legend'] = 'Connection';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_legend'] = 'Sign-in';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['data_legend'] = 'Personal data';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_legend'] = 'Tools';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_legend'] = 'Confirmation';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['name'] = ['Name', 'The name of the connection in the list and in the choice at the assistant, e.g. "Appointments".'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url'] = ['Address of the MCP server', 'The address the provider gives for its MCP server, e.g. https://mcp.example.com/mcp. HTTPS and public addresses only.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth'] = ['Sign-in', 'How the website signs in at the server. The provider\'s guide says which one.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_options'] = [
    'none' => 'Without sign-in',
    'key' => 'Key (Authorization: Bearer)',
    'headers' => 'Own headers',
    'oauth' => 'OAuth (sign in at the service)',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data'] = ['Personal data', 'Applies in both directions: to what the assistant sends to the service and to the results that go to the AI service. Recognised are e-mail addresses, phone numbers, IBANs, card numbers, social security numbers and dates of birth, not names and addresses.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data_options'] = [
    'mask' => 'Mask: replace with a placeholder',
    'block' => 'Do not send: hold back the call or the result',
    'send' => 'Send everything',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['api_key'] = ['Key', 'Sent as "Authorization: Bearer …". Stored encrypted and never shown again. Leaving it empty keeps the stored key.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['headers'] = ['Own headers', 'Name and value per row, e.g. "X-Api-Key". The values are stored encrypted and never shown again; an empty value keeps the stored one.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_id'] = ['Client ID (own app only)', 'Only needed when the service offers no automatic registration: create an app there, enter the redirect address above, and the client ID here. Empty = automatic registration.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_secret'] = ['Client secret (own app only)', 'The secret of the app, if the service issues one. Stored encrypted and never shown again.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['warning'] = 'Warning: through a MCP server the assistant can call functions of another system – including functions that change or delete data there or show customer data. Every visitor of the chat works with the access of this connection. Only release tools with little risk or tools a skill limits, and test the assistant before you make it visible to visitors. Tools the server marks as changing or deleting data, or does not mark, are locked by default. A residual risk remains, because a language model can be wrong or be misled by cleverly worded messages. The manufacturer of this extension accepts no liability for damage caused by released tools. What exactly applies and which exceptions exist is set out in the licence terms.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_active'] = 'active · %d of %d tools released';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unread'] = 'tools not read';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unconfirmed'] = 'not confirmed';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url_https'] = 'Please enter an address starting with https://.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['header_name'] = 'The header name "%s" is invalid. Letters, digits and hyphens are allowed.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_heading'] = 'Sign-in at the service';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connected'] = 'Signed in by %s on %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_not_connected'] = 'Not signed in yet. Save the address, then "Connect": you are taken to the sign-in page of the service and come back here afterwards.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connect'] = 'Connect';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_reconnect'] = 'Connect again';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_disconnect'] = 'Sign out';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_redirect'] = 'Redirect address of this website (only needed for an own app at the service):';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_forgotten'] = 'The sign-in at the service has been removed.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_failed'] = 'The sign-in at the service failed: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_done'] = 'The sign-in at the service has been saved.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_unread'] = 'The tools have not been read yet. They are read after saving, once the sign-in is complete.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_url_changed'] = 'The address has changed. The tools are read again when you save; until then the connection is not active.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_read'] = '%d tools of %s, read on %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_reread'] = 'Read tools again';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_done'] = '%d tools read, %d of them locked.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_failed'] = 'The tools could not be read: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_heading'] = 'Tools and locks';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['lock_all'] = 'Lock all tools the server marks as changing or deleting data, or does not mark';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_locked'] = 'Lock';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_tool'] = 'Tool';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_kind'] = 'Kind according to the server';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_note'] = 'Note';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_locked'] = 'locked';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_new'] = 'NEW';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_personal'] = 'needs personal data';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_help'] = 'No assistant can use a locked tool. Whoever lifts a lock confirms it below; who did it and when is kept. New tools of the server are not ticked at any assistant.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['kind_options'] = [
    'read' => 'reads only',
    'change' => 'changes data',
    'delete' => 'deletes data',
    'unmarked' => 'not marked',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['unlock_confirm'] = 'Lock not lifted: %s. To lift it, confirm below that you have checked the tools.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirmed'] = 'Confirmed by %s on %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['not_confirmed'] = 'Not confirmed yet. Only then can assistants use the tools of this connection.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_check'] = 'I have checked the released tools and understand the residual risk.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_help'] = 'Needed for the first release of the connection and every time you lift a lock.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template'] = ['Template', 'Fills in name, address and sign-in while these fields are empty, and sets how the tools of this service are classified and locked. A skill of the template is installed on saving. Leave empty for any MCP server.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_help'] = 'Provider\'s guide:';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_recommended'] = 'recommended';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_customer_data'] = 'shows customer data';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_own_app'] = 'This service needs an app of your own: create an app at the service and enter the redirect address shown below there. The client ID and client secret of the app go into the fields underneath. Then "Connect".';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_installed'] = 'The skill "%s" of the template is installed. Tick it at the assistants that are to use it.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_failed'] = 'The skill of the template "%s" could not be installed: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup'] = 'Setup at %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copy'] = 'Copy';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copied'] = 'Copied';
