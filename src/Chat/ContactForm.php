<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Chat;

use Contao\StringUtil;
use Neo\SuperAgentBundle\Model\AgentModel;

/**
 * The fields of the forwarding form in the chat. E-mail address and message are
 * always there and always required; name, phone and company are switched on
 * per assistant, each optionally required.
 */
final class ContactForm
{
    /** Optional fields in their order in the form, with their longest value. */
    public const OPTIONAL = ['name' => 120, 'phone' => 40, 'company' => 120];

    /** @return list<string> optional fields shown */
    public static function fields(AgentModel $agent): array
    {
        $chosen = StringUtil::deserialize($agent->form_fields, true);

        return array_values(array_filter(array_keys(self::OPTIONAL), static fn (string $f): bool => \in_array($f, $chosen, true)));
    }

    /** @return list<string> optional fields shown and required */
    public static function required(AgentModel $agent): array
    {
        $required = StringUtil::deserialize($agent->form_required, true);

        return array_values(array_filter(self::fields($agent), static fn (string $f): bool => \in_array($f, $required, true)));
    }

    /** Required checkbox: the visitor agrees to be contacted about the request. */
    public static function consent(AgentModel $agent): bool
    {
        return '' !== (string) $agent->form_consent;
    }

    /** Whether the domain of an e-mail address can receive mail: MX record, else A or AAAA (RFC 5321). */
    public static function domainExists(string $domain): bool
    {
        $domain = trim($domain, " .\t\n\r\0\x0B");

        // Checked first: idn_to_ascii() throws on an empty string from PHP 8.4 on.
        if ('' === $domain) {
            return false;
        }

        $domain = rtrim(idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46) ?: $domain, '.');

        return checkdnsrr($domain.'.', 'MX') || checkdnsrr($domain.'.', 'A') || checkdnsrr($domain.'.', 'AAAA');
    }
}
