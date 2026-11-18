<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model\Registry;
use Neo\SuperAgentBundle\Chat\ChatRateLimiter;
use Neo\SuperAgentBundle\Chat\PersonalDataGuard;
use Neo\SuperAgentBundle\Controller\ChatController;
use Neo\SuperAgentBundle\Licence\FeatureResolver;
use Neo\SuperAgentBundle\Mail\EscalationMailer;
use Neo\SuperAgentBundle\Mail\SenderAddress;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Newsletter\NewsletterSignup;
use Neo\SuperAgentBundle\Security\PreviewAccess;
use Neo\SuperAgentBundle\Tests\Support\ChatStack;
use Neo\SuperAgentBundle\Tests\Support\Licences;
use Neo\SuperAgentBundle\Tests\Support\Models;
use Neo\SuperAgentBundle\Tests\Support\TestDatabase;
use Neo\SuperAgentBundle\Tests\Support\Translations;
use Neo\SuperAgentBundle\Voice\Transcriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/** The JSON endpoints of the chat widget: what a visitor can send and what comes back. */
#[CoversClass(ChatController::class)]
final class ChatControllerTest extends TestCase
{
    private ChatStack $chat;

    private ArrayAdapter $cache;

    private string $edition = 'pro';

    protected function setUp(): void
    {
        $this->chat = new ChatStack();
        $this->cache = new ArrayAdapter();
        Registry::getInstance()->reset();
        Models::register(AgentModel::class, ['id' => 3, 'published' => '1', 'visibility' => 'public', 'assistant_name' => 'Bot', 'model' => 'provider/model-a', 'temperature' => '0.3', 'escalation_email' => 'team@example.com', 'form_fields' => serialize(['name', 'phone']), 'form_required' => serialize(['name']), 'form_consent' => '1', 'voice_input' => '1', 'privacy_mode' => '', 'transcription_model' => 'audio-model']);
    }

    protected function tearDown(): void
    {
        Registry::getInstance()->reset();
    }

    public function testTheVisitorCannotWriteAsSystemOrSendMarkup(): void
    {
        $this->chat->answers = ['Hello!'];
        $history = [];

        for ($i = 0; $i < 34; ++$i) {
            $history[] = ['role' => 0 === $i % 2 ? 'system' : 'assistant', 'content' => 'turn '.$i];
        }

        // An answer with several offers is longer than a message of the visitor.
        $history[33]['content'] .= ' '.str_repeat('b', 5000);

        $response = $this->controller()->chat($this->post(['agent' => 3, 'message' => '<script>x()</script>Hi '.str_repeat('a', 3000), 'lang' => 'de-AT', 'history' => [...$history, 'no turn', ['role' => 'user', 'content' => '<b></b>']]]));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['reply' => 'Hello!', 'handoff' => null, 'navigate' => null], json_decode((string) $response->getContent(), true));

        $messages = $this->chat->sent[0]['messages'];
        $this->assertSame(['system', ...array_merge(...array_fill(0, 14, ['user', 'assistant'])), 'user'], array_column($messages, 'role'), 'of the last 30 entries the 28 turns, "system" from the browser counts as the visitor');
        $this->assertSame('turn 6', $messages[1]['content']);
        $this->assertSame('turn 32', $messages[27]['content']);
        $this->assertStringStartsWith('turn 33 bbb', $messages[28]['content']);
        $this->assertSame(4000, mb_strlen($messages[28]['content']), 'an answer is cut at 4,000 characters');
        $this->assertSame(2000, mb_strlen($messages[29]['content']));
        $this->assertStringStartsWith('x()Hi aaa', $messages[29]['content']);
        $this->assertStringContainsString('Page language: German (Austria)', $messages[0]['content']);
    }

    public function testEmptyMessagesAndTooManyRequests(): void
    {
        $controller = $this->controller();

        $this->assertSame(400, $controller->chat($this->post(['agent' => 3, 'message' => '  <p> </p> ']))->getStatusCode());
        $this->assertSame(400, $controller->chat($this->post('not json'))->getStatusCode());

        for ($i = 0; $i < 18; ++$i) {
            $controller->chat($this->post(['agent' => 3, 'message' => '']));
        }

        $this->assertSame(429, $controller->chat($this->post(['agent' => 3, 'message' => 'Hi']))->getStatusCode());
        $this->assertSame([], $this->chat->sent);
    }

    public function testPersonalDataNeedsTheVisitorsConfirmationBeforeTheAiServiceGetsIt(): void
    {
        $this->chat->answers = ['Thanks, noted.'];
        $message = 'My booking is under max@example.com, call +43 660 1234567';

        $response = $this->controller()->chat($this->post(['agent' => 3, 'message' => $message, 'lang' => 'en']));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['confirm' => ['email', 'phone']], json_decode((string) $response->getContent(), true));
        $this->assertSame([], $this->chat->sent, 'no AI call before the confirmation');

        $response = $this->controller()->chat($this->post(['agent' => 3, 'message' => $message, 'confirmed' => true, 'lang' => 'en']));

        $this->assertSame('Thanks, noted.', json_decode((string) $response->getContent(), true)['reply']);
        $this->assertStringContainsString('max@example.com', end($this->chat->sent[0]['messages'])['content'], 'confirmed: the message goes as written');
    }

    public function testAMessageWithoutPersonalDataNeedsNoConfirmation(): void
    {
        $this->chat->answers = ['Hello!'];

        $response = $this->controller()->chat($this->post(['agent' => 3, 'message' => 'When is check-in?', 'lang' => 'en']));

        $this->assertSame('Hello!', json_decode((string) $response->getContent(), true)['reply']);
    }

    public function testInPrivacyModeTheDataIsRemovedWithoutAsking(): void
    {
        Models::register(AgentModel::class, ['id' => 4, 'published' => '1', 'visibility' => 'public', 'assistant_name' => 'Bot', 'model' => 'provider/model-a', 'temperature' => '0.3', 'privacy_mode' => '1']);
        $this->chat->answers = ['Noted.'];

        $response = $this->controller()->chat($this->post(['agent' => 4, 'message' => 'Write to max@example.com', 'lang' => 'en']));
        $data = json_decode((string) $response->getContent(), true);

        $this->assertArrayNotHasKey('confirm', $data);
        $this->assertSame('Noted.', $data['reply']);
        $this->assertStringNotContainsString('max@example.com', end($this->chat->sent[0]['messages'])['content']);
    }

    public function testTheStreamSendsTheStepsAndTheAnswerLast(): void
    {
        $this->chat->answers = ['Hello!'];
        $request = $this->post(['agent' => 3, 'message' => 'Hi', 'lang' => 'en', 'guest' => 'not-a-token']);
        $request->headers->set('Accept', 'text/event-stream, application/json');
        $controller = $this->controller();

        $this->assertInstanceOf(StreamedResponse::class, $controller->chat($request));

        // The events as the stream sends them (it closes every output buffer, so
        // they are collected through its sender).
        $events = [];
        $stream = new \ReflectionMethod(ChatController::class, 'events');
        $args = [Models::make(AgentModel::class, ['id' => 3, 'model' => 'provider/model-a', 'temperature' => '0.3', 'assistant_name' => 'Bot']), 'Hi', [], 'en', null, (new FeatureResolver(Licences::service('pro'), Licences::terms()))->for('www.example.com')];
        $stream->invoke($controller, $args, 'not-a-token', '', 'en', 'www.example.com', static function (string $event, array $data) use (&$events): void {
            $events[] = [$event, $data];
        });

        $this->assertSame([['step', ['step' => 'thinking']], ['done', ['reply' => 'Hello!', 'handoff' => null, 'navigate' => null]]], $events);
    }

    public function testAFileOfTheChatIsDownloadedOnceThenANoticeInTheLanguageOfTheChat(): void
    {
        $token = $this->chat->downloads->put('Rechnung März.pdf', 'application/pdf', '%PDF-1.7');
        $controller = $this->controller();

        $response = $controller->download(Request::create('/api/superagent/file/'.$token.'?lang=de'), $token);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('%PDF-1.7', $response->getContent());
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('attachment; filename=Rechnung-Marz.pdf; filename*=utf-8\'\'Rechnung%20M%C3%A4rz.pdf', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));

        $again = $controller->download(Request::create('/api/superagent/file/'.$token.'?lang=de-AT'), $token);
        $this->assertSame(410, $again->getStatusCode());
        $this->assertStringStartsWith('Dieser Download ist nicht mehr verfügbar.', (string) $again->getContent());
        $this->assertStringStartsWith('This download is no longer available.', (string) $controller->download(Request::create('/x?lang=<b>'), $token)->getContent());
    }

    public function testTheFilesOfAReplyGoToTheWidgetAsAddresses(): void
    {
        $method = new \ReflectionMethod(ChatController::class, 'withDownloads');
        $result = $method->invoke($this->controller(), ['reply' => 'Ready below.', 'files' => [['token' => str_repeat('a', 48), 'name' => 'INV-1.pdf', 'size' => 1200]]], 'de-at');

        $this->assertSame([['url' => '/api/superagent/file/'.str_repeat('a', 48).'?lang=de-at', 'name' => 'INV-1.pdf', 'size' => 1200]], $result['files']);
        $this->assertSame(['reply' => 'x'], $method->invoke($this->controller(), ['reply' => 'x'], 'de'));
    }

    public function testNoAnswerOfTheServiceIs503(): void
    {
        $this->chat->key = '';

        $response = $this->controller()->chat($this->post(['agent' => 3, 'message' => 'Hi']));

        $this->assertSame(503, $response->getStatusCode());
        $this->assertSame(['error' => 'unavailable'], json_decode((string) $response->getContent(), true));
    }

    public function testTheForwardingFormIsChecked(): void
    {
        $controller = $this->controller();

        $this->assertSame([400, ['error' => 'invalid']], $this->escalate($controller, ['email' => 'not an address', 'message' => 'Call me']));
        $this->assertSame([400, ['error' => 'invalid']], $this->escalate($controller, ['email' => 'max.mustermann@example.com', 'message' => '<b></b>']));
        $this->assertSame([400, ['error' => 'missing', 'fields' => ['name', 'consent']]], $this->escalate($controller, ['email' => 'max.mustermann@example.com', 'message' => 'Call me', 'consent' => 'yes']), 'only true counts as consent');
        $this->assertSame([400, ['error' => 'invalid', 'fields' => ['phone']]], $this->escalate($controller, ['email' => 'max.mustermann@example.com', 'message' => 'Call me', 'name' => 'Max Mustermann', 'phone' => '0664 123', 'consent' => true]));
        $this->assertSame([400, ['error' => 'invalid', 'fields' => ['email']]], $this->escalate($controller, ['email' => 'max.mustermann@mail.invalid', 'message' => 'Call me', 'name' => 'Max Mustermann', 'phone' => '0043 664 1234567', 'consent' => true]), 'a domain that cannot receive mail');
    }

    public function testAnAssistantWithoutAddressForwardsNothing(): void
    {
        Registry::getInstance()->reset();
        Models::register(AgentModel::class, ['id' => 3, 'published' => '1', 'visibility' => 'public', 'escalation_email' => '']);

        $this->assertSame([503, ['error' => 'no_escalation']], $this->escalate($this->controller(), ['agent' => 3, 'email' => 'max.mustermann@example.com', 'message' => 'Call me']));
    }

    public function testVoiceInputNeedsAValidRecordingTheSettingAndTheEdition(): void
    {
        $this->assertSame([400, ['error' => 'invalid']], $this->transcribe($this->controller(), 'text/plain'));

        $this->edition = 'plus';
        $this->assertSame([403, ['error' => 'licence']], $this->transcribe($this->controller(), 'audio/webm;codecs=opus'));

        Registry::getInstance()->reset();
        Models::register(AgentModel::class, ['id' => 3, 'published' => '1', 'visibility' => 'public', 'voice_input' => '1', 'privacy_mode' => '1', 'voice_in_privacy' => '']);
        $this->edition = 'pro';
        $this->assertSame([403, ['error' => 'disabled']], $this->transcribe($this->controller(), 'audio/webm'), 'privacy mode without "voice input also in privacy mode"');
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{int, array<string, mixed>}
     */
    private function escalate(ChatController $controller, array $payload): array
    {
        $response = $controller->escalate($this->post($payload + ['agent' => 3]));

        return [$response->getStatusCode(), json_decode((string) $response->getContent(), true)];
    }

    /** @return array{int, array<string, mixed>} */
    private function transcribe(ChatController $controller, string $mimeType): array
    {
        $file = (string) tempnam(sys_get_temp_dir(), 'neo-rec');
        file_put_contents($file, 'audio');

        try {
            $request = Request::create('https://www.example.com/api/superagent/transcribe', 'POST', ['agent' => '3', 'lang' => 'en'], [], ['audio' => new UploadedFile($file, 'rec.webm', $mimeType, null, true)], ['REMOTE_ADDR' => '203.0.113.7']);
            $response = $controller->transcribe($request);

            return [$response->getStatusCode(), json_decode((string) $response->getContent(), true)];
        } finally {
            @unlink($file);
        }
    }

    /** @param array<string, mixed>|string $body */
    private function post(array|string $body): Request
    {
        return Request::create('https://www.example.com/api/superagent/chat', 'POST', [], [], [], ['REMOTE_ADDR' => '203.0.113.7', 'CONTENT_TYPE' => 'application/json'], \is_array($body) ? json_encode($body) : $body);
    }

    private function controller(): ChatController
    {
        $framework = $this->createMock(ContaoFramework::class);
        $tokens = $this->createMock(TokenChecker::class);
        $tokens->method('getBackendUsername')->willReturn(null);
        $features = new FeatureResolver(Licences::service($this->edition), Licences::terms());

        $controller = new ChatController(
            $framework,
            $this->chat->handler($framework, $this->createMock(ContentUrlGenerator::class)),
            new ChatRateLimiter($this->cache),
            new EscalationMailer($this->createMock(MailerInterface::class), Translations::translator(), new SenderAddress(TestDatabase::create(), 'noreply@example.com')),
            $features,
            new Transcriber($this->chat->http(), $this->chat->connection()),
            new PreviewAccess($tokens, new RequestStack()),
            new NewsletterSignup(),
            new NullLogger(),
            new PersonalDataGuard(),
            $this->chat->downloads,
            Translations::translator(),
        );
        $router = $this->createStub(UrlGeneratorInterface::class);
        $router->method('generate')->willReturnCallback(static fn (string $route, array $parameters): string => '/api/superagent/file/'.$parameters['token'].'?lang='.$parameters['lang']);
        $container = new Container();
        $container->set('router', $router);
        $controller->setContainer($container);

        return $controller;
    }
}
