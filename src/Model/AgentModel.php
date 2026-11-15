<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Model;

use Contao\Model;

/**
 * Model for tl_neo_agent — one configurable assistant per row. The shared,
 * language-neutral configuration (identity, provider/model, system prompt,
 * knowledge base, escalation) lives here; the per-language conversational texts
 * (greeting, canned Q&A, …) live in tl_neo_agent_language (AgentLanguageModel).
 *
 * @property int    $id
 * @property int    $tstamp
 * @property string $title
 * @property bool   $published
 * @property string $assistant_name
 * @property string $reasoning_effort
 * @property string $voice_input
 * @property string $transcription_model
 * @property string $visibility
 * @property string|null $form_fields
 * @property string|null $form_required
 * @property string $newsletter_module
 * @property string $form_consent
 * @property string $phone_country
 * @property int    $privacy_page
 * @property string $provider
 * @property string $base_url
 * @property string $model
 * @property string $api_key
 * @property string $temperature
 * @property string $max_tokens
 * @property bool   $web_search
 * @property string $web_search_mode
 * @property string $web_search_sources
 * @property string $web_search_max
 * @property string $web_search_key
 * @property string $no_medical
 * @property int    $contact_page
 * @property string|null $avatar
 * @property string $widget_side
 * @property string $privacy_mode
 * @property string $referral_company
 * @property string $preferred_solution
 * @property string $preferred_solution_note
 * @property string $tone_address
 * @property string $answer_length
 * @property string $tone_style
 * @property string $system_prompt
 * @property string $grounding_source
 * @property string $grounding_text
 * @property string $grounding_file
 * @property string $escalation_email
 * @property string $escalation_name
 * @property string $consent_category
 * @property string $mcp_tools       serialized list of "<connection id>:<tool name>" (edition Plus)
 * @property string $skills          serialized list of names of installed skills (edition Plus)
 */
final class AgentModel extends Model
{
    protected static $strTable = 'tl_neo_agent';

    /** The first active assistant; those in "preview only" only when $withPreview. */
    public static function findPublishedAgent(bool $withPreview = false): ?self
    {
        $columns = ["tl_neo_agent.published = '1'"];

        if (!$withPreview) {
            $columns[] = "tl_neo_agent.visibility != 'preview'";
        }

        return self::findOneBy($columns, [], ['order' => 'tl_neo_agent.id ASC']);
    }
}
