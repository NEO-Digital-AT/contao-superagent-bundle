<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Mcp;

/**
 * Where a MCP server is reached and with which headers (the authentication of
 * the connection, already resolved: a bearer token, a key header or own
 * headers). The values never reach the model.
 */
final class McpEndpoint
{
    /** @param array<string, string> $headers */
    public function __construct(
        public readonly string $url,
        public readonly array $headers = [],
    ) {
    }

    /** Key of the endpoint within one request (session cache of the client). */
    public function key(): string
    {
        return hash('sha256', $this->url."\n".json_encode($this->headers));
    }
}
