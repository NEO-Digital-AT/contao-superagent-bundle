<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle;

/**
 * Names the bundle uses in more than one place.
 */
final class SuperAgent
{
    /** Extension code at the licence service. */
    public const LICENSE_MODULE = 'superagent';

    /** Composer package, reported to the licence service with its version. */
    public const PACKAGE = 'neo-digital/contao-superagent-bundle';

    /** Scope of the encryption of keys stored by this bundle (keeps keys stored by earlier versions readable). */
    public const CIPHER_SCOPE = 'neo-superagent';
}
