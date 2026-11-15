<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Model;

use Contao\Model;

/**
 * Per-language conversational settings of an agent (tl_neo_agent_language): the
 * greeting and other UI texts, the starter questions, the canned Q&A and the
 * fallback text — everything that must read naturally in the visitor's language.
 * The knowledge base, system prompt, model and key stay on the agent itself.
 *
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property int    $sorting
 * @property string $language
 * @property string $subtitle
 * @property string $greeting
 * @property string $fact_note
 * @property string $input_placeholder
 * @property string|null $suggestions former suggested questions (StarterQuestionsMigration)
 * @property string|null $starter_questions
 * @property string $canned
 */
final class AgentLanguageModel extends Model
{
    /** Fixed widget texts editable per language (column ui_<key>, catalogue key widget.<key>). */
    public const UI_KEYS = ['ai_notice', 'privacy_hint', 'privacy_reject', 'medical_refusal', 'open_page', 'new_chat', 'suggestions', 'fab', 'open', 'close', 'gate_title', 'gate_body', 'gate_button', 'gate_unavailable', 'send', 'handoff', 'esc_email', 'esc_message', 'esc_email_placeholder', 'esc_message_placeholder', 'esc_name', 'esc_phone', 'esc_country_code', 'esc_country_search', 'esc_company', 'esc_consent', 'esc_privacy_link', 'esc_newsletter', 'esc_missing', 'esc_phone_invalid', 'esc_email_invalid', 'esc_email_suggest', 'esc_submit', 'esc_back', 'esc_done', 'error', 'status_thinking', 'status_pages', 'status_form', 'status_search', 'mic', 'mic_stop', 'mic_recording', 'mic_error', 'mic_busy', 'pii_title', 'pii_found', 'pii_body', 'pii_send', 'pii_edit', 'pii_email', 'pii_phone', 'pii_iban', 'pii_card', 'pii_svnr', 'pii_birthdate', 'file_download', 'file_used'];

    protected static $strTable = 'tl_neo_agent_language';

    /**
     * The language record for an agent: the record of the page's primary language
     * ("pt" for pt-BR, "fil" for fil-PH), or null. Without a record the chat uses
     * the default texts of the page language (English for languages the bundle
     * does not ship), never the texts of another language.
     */
    public static function findForAgent(int $agentId, string $language): ?self
    {
        $code = strtolower((string) strtok(str_replace('_', '-', trim($language)), '-'));

        return self::findOneBy(
            ['tl_neo_agent_language.pid = ?', 'tl_neo_agent_language.language = ?'],
            [$agentId, $code],
        );
    }
}
