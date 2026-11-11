<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Osynapsy\Ai\Payload\OpenAi\ResponseStreamDecoder;

/**
 * Description of ResponseStreamDecoderTest
 *
 * @author peter
 */
class ResponseStreamDecoderTest extends TestCase
{
    public function testDecodesBothFormats(): void
    {
        $d = new ResponseStreamDecoder();
        $body = "data: {\"a\":1}\n\n: keep-alive\nevent: x\n{\"b\":2}\nnon json\ndata: [DONE]\n";

        $this->assertSame([['a' => 1], ['b' => 2]], $d->decodeBody($body));
    }
    
    public function testMergeOpenAiSse(): void
    {
        $d = new ResponseStreamDecoder();
        $body = implode("\n\n", [
            'data: {"model":"m","choices":[{"index":0,"delta":{"content":"","reasoning":"pen"},"finish_reason":null}]}',
            'data: {"model":"m","choices":[{"index":0,"delta":{"content":"Ci"},"finish_reason":null}]}',
            'data: {"model":"m","choices":[{"index":0,"delta":{"content":"ao"},"finish_reason":"stop"}]}',
            'data: [DONE]',
        ]) . "\n\n";

        $r = $d->merge($d->decodeBody($body));

        $this->assertSame('Ciao', $r['choices'][0]['message']['content']);
        $this->assertSame('pen', $r['choices'][0]['message']['reasoning']);
        $this->assertSame('stop', $r['choices'][0]['finish_reason']);
    }

    public function testMergeFragmentedToolCall(): void
    {
        $d = new ResponseStreamDecoder();
        $rows = [
            ['choices' => [['delta' => ['tool_calls' => [['index' => 0, 'id' => 'c1', 'function' => ['name' => 'cerca', 'arguments' => '{"q":']]]]]]],
            ['choices' => [['delta' => ['tool_calls' => [['index' => 0, 'function' => ['arguments' => '"x"}']]]], 'finish_reason' => 'tool_calls']]],
        ];

        $call = $d->merge($rows)['choices'][0]['message']['tool_calls'][0];

        $this->assertSame('c1', $call['id']);
        $this->assertSame('{"q":"x"}', $call['function']['arguments']);
    }

    public function testMergeOllamaNativeAndStringError(): void
    {
        $d = new ResponseStreamDecoder();
        $r = $d->merge([
            ['model' => 'm', 'message' => ['content' => 'Ci']],
            ['model' => 'm', 'message' => ['content' => 'ao'], 'done' => true, 'done_reason' => 'stop'],
        ]);
        $this->assertSame('Ciao', $r['choices'][0]['message']['content']);
        $this->assertSame('stop', $r['choices'][0]['finish_reason']);

        $this->assertSame(['error' => ['message' => 'model not found']], $d->merge([['error' => 'model not found']]));
    }
}
