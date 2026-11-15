<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Mcp;

/**
 * The server answered 401: the connection needs (new) credentials. For OAuth
 * the address of the protected-resource metadata (RFC 9728) comes from the
 * WWW-Authenticate header, when the server sends it.
 */
final class McpAuthRequiredException extends McpException
{
    public function __construct(
        string $message,
        public readonly string $resourceMetadata = '',
        public readonly string $scope = '',
    ) {
        parent::__construct($message);
    }
}
