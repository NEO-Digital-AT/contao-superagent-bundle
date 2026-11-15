<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Mcp;

/**
 * A MCP server could not be reached or answered with an error. The message is
 * English and technical; it goes to the log, the back end and, for a tool call,
 * to the model as a fact. It never contains a header value.
 */
class McpException extends \RuntimeException
{
}
