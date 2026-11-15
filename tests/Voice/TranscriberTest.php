<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Voice;

use Neo\BasicBundle\Access\AccessSettings;
use Neo\BasicBundle\Ai\AiConnection;
use Neo\BasicBundle\Security\SecretCipher;
use Neo\SuperAgentBundle\Tests\Support\TestDatabase;
use Neo\SuperAgentBundle\Voice\Transcriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(Transcriber::class)]
final class TranscriberTest extends TestCase
{
    private string $recording = '';

    protected function setUp(): void
    {
        $this->recording = (string) tempnam(sys_get_temp_dir(), 'neo-rec');
        file_put_contents($this->recording, 'OggS fake audio');
    }

    protected function tearDown(): void
    {
        @unlink($this->recording);
    }

    public function testTheRecordingGoesToTheTranscriptionEndpoint(): void
    {
        $sent = [];
        $client = new MockHttpClient(static function (string $method, string $url, array $options) use (&$sent): MockResponse {
            $body = '';

            foreach ($options['body'] instanceof \Closure ? [] : (is_iterable($options['body']) ? $options['body'] : [$options['body']]) as $chunk) {
                $body .= $chunk;
            }

            if ($options['body'] instanceof \Closure) {
                while ('' !== ($chunk = ($options['body'])(8192))) {
                    $body .= $chunk;
                }
            }

            $sent = [$method, $url, $options['headers'], $body];

            return new MockResponse('{"text":" Hello, I need a room. "}');
        });

        $text = (new Transcriber($client, $this->connection('sk-test')))->transcribe('audio-model', $this->recording, 'audio/ogg;codecs=opus', 'de');

        $this->assertSame('Hello, I need a room.', $text);
        $this->assertSame(['POST', 'https://router.example.com/v1/audio/transcriptions'], [$sent[0], $sent[1]]);
        $this->assertContains('Authorization: Bearer sk-test', $sent[2]);
        $this->assertStringContainsString('name="model"', $sent[3]);
        $this->assertStringContainsString('audio-model', $sent[3]);
        $this->assertStringContainsString('filename="recording.ogg"', $sent[3]);
        $this->assertStringContainsString('OggS fake audio', $sent[3]);
        $this->assertStringContainsString('name="language"', $sent[3]);
    }

    public function testWithoutConnectionOrModelNothingIsSent(): void
    {
        $client = new MockHttpClient(function (): MockResponse {
            $this->fail('No request expected');
        });

        foreach ([['', 'audio-model'], ['sk-test', '']] as [$key, $model]) {
            try {
                (new Transcriber($client, $this->connection($key)))->transcribe($model, $this->recording, 'audio/webm', 'en');
                $this->fail('No exception');
            } catch (\RuntimeException $e) {
                $this->assertSame('No AI connection or transcription model.', $e->getMessage());
            }
        }
    }

    private function connection(string $key): AiConnection
    {
        $db = TestDatabase::create('tl_neo_access');
        $connection = new AiConnection(new AccessSettings($db, new SecretCipher('test-secret')));
        $connection->store(AiConnection::CUSTOM, 'https://router.example.com/v1/', $key);

        return new AiConnection(new AccessSettings($db, new SecretCipher('test-secret')));
    }
}
