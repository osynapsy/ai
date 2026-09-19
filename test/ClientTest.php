<?php
declare(strict_types=1);

namespace Osynapsy\Ai\Tests;

use PHPUnit\Framework\TestCase;
use Osynapsy\Ai\Client;
use Osynapsy\Ai\Model\ModelInterface;
use Osynapsy\Ai\Prompt\PromptInterface;
use Osynapsy\Ai\Response\ChatResponse;
use Osynapsy\Rest\Request\Request;
use Osynapsy\Rest\Response\Response;

class ClientTest extends TestCase
{
    private $modelMock;
    private $promptMock;

    protected function setUp(): void
    {
        // Mock delle dipendenze per isolare la logica del Client
        $this->modelMock = $this->createMock(ModelInterface::class);
        $this->promptMock = $this->createMock(PromptInterface::class);
    }

    public function testGetModelReturnsInjectedModel(): void
    {
        $client = new Client('dummy_key', $this->modelMock);

        $this->assertSame($this->modelMock, $client->getModel());
    }

    public function testPromptFactoryReturnsPromptInterface(): void
    {
        $client = new Client('dummy_key', $this->modelMock);
        $prompt = $client->promptFactory();

        $this->assertInstanceOf(PromptInterface::class, $prompt);
    }

    public function testSendExecutesRequestAndReturnsParsedResponse(): void
    {
        $apiKey = 'test_api_key_123';
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $requestPayload = ['model' => 'gpt-4', 'messages' => []];
        $rawResponseBody = ['choices' => [['message' => ['content' => 'Hello World']]]];
        $expectedResult = new ChatResponse(['content' => 'Hello World']);

        // 1. Configurazione dei comportamenti dei Mock
        $this->modelMock
            ->expects($this->once())
            ->method('buildRequest')
            ->with($this->promptMock, 1024)
            ->willReturn($requestPayload);

        $this->modelMock
            ->expects($this->once())
            ->method('getEndpoint')
            ->willReturn($endpoint);

        $this->modelMock
            ->expects($this->once())
            ->method('getResponse')
            ->with($rawResponseBody)
            ->willReturn($expectedResult);

        // Mock della risposta REST
        $responseMock = $this->createMock(Response::class);
        $responseMock
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($rawResponseBody);

        // 2. Mock parziale di Client per intercettare restClientFactory ed evitare chiamate di rete reali
        $client = $this->getMockBuilder(Client::class)
            ->setConstructorArgs([$apiKey, $this->modelMock])
            ->onlyMethods(['restClientFactory'])
            ->getMock();

        $client->expects($this->once())
            ->method('restClientFactory')
            ->with($this->callback(function (Request $request) use ($endpoint, $requestPayload, $apiKey) {
                // Verifichiamo che la Request sia stata costruita correttamente
                return $request->getUrl() === $endpoint
                    && $request->getData() === $requestPayload
                    && $request->getAuthorizationToken() === $apiKey;
            }))
            ->willReturn($responseMock);

        // 3. Esecuzione e Assertion
        $result = $client->send($this->promptMock, 1024);

        $this->assertEquals($expectedResult, $result);
    }
}
