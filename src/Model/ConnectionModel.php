<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Model;

use Contao\Model;

/**
 * A connection to a MCP server (tl_neo_agent_connection, edition Plus): address,
 * authentication, the tools last read from the server and which of them are
 * locked. Edited by administrators only; the assistants choose among the tools
 * that are not locked.
 *
 * @property int    $id
 * @property int    $tstamp
 * @property string $name
 * @property string $url
 * @property string $template      id of the template the connection was made from ('' = none)
 * @property string $auth          none | key | headers | oauth
 * @property string $personal_data mask | block | send (both directions, see Mcp\McpToolbox)
 * @property string $api_key       encrypted
 * @property string $headers       serialized list of [key => name, value => encrypted value]
 * @property string $oauth_client_id
 * @property string $oauth_client_secret encrypted
 * @property string $oauth_data    encrypted JSON: metadata, registered client, tokens
 * @property string $tools         JSON: read_at, server, tools, new
 * @property string $locked        serialized list of tool names
 * @property string $unlocks       JSON: tool => {user, time} of each confirmed unlock
 * @property int    $confirmed_at
 * @property string $confirmed_by
 */
final class ConnectionModel extends Model
{
    public const AUTH_NONE = 'none';

    public const AUTH_KEY = 'key';

    public const AUTH_HEADERS = 'headers';

    public const AUTH_OAUTH = 'oauth';

    public const DATA_MASK = 'mask';

    public const DATA_BLOCK = 'block';

    public const DATA_SEND = 'send';

    protected static $strTable = 'tl_neo_agent_connection';
}
