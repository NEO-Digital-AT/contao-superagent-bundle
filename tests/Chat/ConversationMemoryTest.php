<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Chat;

use Neo\BasicBundle\Security\SecretCipher;
use Neo\SuperAgentBundle\Chat\ConversationMemory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConversationMemory::class)]
final class ConversationMemoryTest extends TestCase
{
    private SecretCipher $cipher;

    protected function setUp(): void
    {
        $this->cipher = new SecretCipher('test-secret');
    }

    public function testResultsAndLoadedSkillsTravelWithTheConversation(): void
    {
        $memory = new ConversationMemory($this->cipher);
        $memory->load(3, '');
        $memory->record('mcp1_CreateBooking', ['body' => ['booker' => ['lastName' => 'Muster']]], '{"id":"ABCD-1"}');
        $memory->remember(['hotel-booking', 'hotel-payment'], ['mcp1_ListFolios']);
        $token = $memory->token();

        $this->assertStringNotContainsString('ABCD', $token);
        $this->assertStringNotContainsString('Muster', $this->cipher->decrypt($token), 'only readable with the key of the memory');

        $next = new ConversationMemory($this->cipher);
        $next->load(3, $token);

        $this->assertSame([['tool' => 'mcp1_CreateBooking', 'arguments' => '{"body":{"booker":{"lastName":"Muster"}}}', 'result' => '{"id":"ABCD-1"}']], $next->entries());
        $this->assertSame(['hotel-booking', 'hotel-payment'], $next->skills());
        $this->assertSame(['mcp1_ListFolios'], $next->tools());
    }

    public function testAForeignChangedOrOldTokenCountsAsEmpty(): void
    {
        $memory = new ConversationMemory($this->cipher);
        $memory->load(3, '');
        $memory->record('mcp1_ListOffers', [], 'offers');
        $token = $memory->token();

        $other = new ConversationMemory($this->cipher);
        $other->load(4, $token);
        $this->assertSame([], $other->entries(), 'another assistant');

        $other->load(3, substr($token, 0, -6).'AAAAAA');
        $this->assertSame([], $other->entries(), 'changed in the browser');

        $other->load(3, (new SecretCipher('other-secret'))->encrypt('j:'.json_encode(['agent' => 3, 'at' => time(), 'entries' => [['tool' => 'x', 'arguments' => '{}', 'result' => 'forged']]]), ConversationMemory::SCOPE));
        $this->assertSame([], $other->entries(), 'made without the secret of the website');

        $other->load(3, $this->cipher->encrypt('j:'.json_encode(['agent' => 3, 'at' => time() - 7201, 'entries' => [['tool' => 'x', 'arguments' => '{}', 'result' => 'old']]]), ConversationMemory::SCOPE));
        $this->assertSame([], $other->entries(), 'silent for more than two hours');

        $other->load(3, 'enc:'.str_repeat('A', ConversationMemory::MAX_TOKEN));
        $this->assertSame([], $other->entries(), 'too long');
        $this->assertSame('', $other->token(), 'nothing to remember');
    }

    public function testOnlyTheNewestResultsAreKeptAndLongResultsAreCut(): void
    {
        $memory = new ConversationMemory($this->cipher);
        $memory->load(3, '');

        for ($i = 1; $i <= 14; ++$i) {
            $memory->record('mcp1_Tool'.$i, ['n' => $i], 14 === $i ? str_repeat('x', 5000) : 'result '.$i);
        }

        $entries = $memory->entries();

        $this->assertCount(12, $entries);
        $this->assertSame('mcp1_Tool3', $entries[0]['tool'], 'the oldest go first');
        $this->assertSame(4000 + mb_strlen(' …[cut]'), mb_strlen($entries[11]['result']));
        $this->assertStringEndsWith(' …[cut]', $entries[11]['result']);

        // Large results: the newest fit into the budget of all results together.
        $memory->load(3, '');

        for ($i = 1; $i <= 8; ++$i) {
            $memory->record('mcp1_Big'.$i, [], str_repeat('y', 3900));
        }

        $this->assertSame(['mcp1_Big3', 'mcp1_Big4', 'mcp1_Big5', 'mcp1_Big6', 'mcp1_Big7', 'mcp1_Big8'], array_column($memory->entries(), 'tool'));

        $next = new ConversationMemory($this->cipher);
        $next->load(3, $memory->token());
        $this->assertCount(6, $next->entries());
    }

    public function testNamesFromTheTokenAreChecked(): void
    {
        $memory = new ConversationMemory($this->cipher);
        $memory->load(3, $this->cipher->encrypt('j:'.json_encode(['agent' => 3, 'at' => time(), 'entries' => [['tool' => 'x', 'arguments' => '{}']], 'skills' => ['hotel-booking', 'bad name', 7], 'tools' => ['mcp1_ListOffers']]), ConversationMemory::SCOPE));

        $this->assertSame([], $memory->entries(), 'an entry without result');
        $this->assertSame(['hotel-booking'], $memory->skills());
        $this->assertSame(['mcp1_ListOffers'], $memory->tools());
    }
}
