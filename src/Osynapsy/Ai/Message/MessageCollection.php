<?php
namespace Osynapsy\Ai\Message;

use Osynapsy\Ai\Tools\ToolCall;

/**
 * Description of MessageCollection
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class MessageCollection implements MessageCollectionInterface
{
    protected $id;
    protected array $systemPrompts = [];
    protected array $messages = [];
    protected $onMessageSave;

    public function __construct($id)
    {
        $this->id = $id;
    }

   public function addSystem(string $rawcontent): self
    {
        $content = trim($rawcontent);
        if (empty($content)) {
            return $this;
        }

        $hash = md5($content);

        // Se il frammento non esiste già, lo memorizziamo
        if (!isset($this->systemPrompts[$hash])) {
            $this->systemPrompts[$hash] = $content;
        }

        return $this;
    }

   public function addUser(string $content): self
    {
        $this->messages[] = [
            'role' => 'user',
            'content' => $content
        ];
        return $this;
    }

    public function addAssistant(?string $content, ?string $reasoning, ?ToolCall $toolCall = null): self
    {
        $msg = [
            'role' => 'assistant',
            'content' => $content,
            'reasoning' => $reasoning
        ];
        if ($toolCall) {
            $msg['tool_calls'] = $toolCall;
        }
        $this->messages[] = $msg;
        return $this;
    }

    public function addToolResult(ToolCall $toolCall, mixed $result): self
    {
        $this->messages[] = [
            'role' => 'tool',
            'name' => $toolCall->getTool(),
            'content' => is_string($result) ? $result : json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'tool_call_id' => $toolCall->getId()
        ];
        return $this;
    }

    public function all(): array
    {
        $result = [];

        if (!empty($this->systemPrompts)) {
            // Unisce i frammenti unici in un unico blocco di sistema
            $result[] = [
                'role' => 'system',
                'content' => implode("\n\n---\n\n", array_values($this->systemPrompts))
            ];
        }

        return array_merge($result, $this->messages);
    }

    public function loadHistory(array $messages)
    {
        foreach($messages as $m) {
            $images = $m['images'] ?? $m[4];
            $this->addMessage(
                $m['role'] ?? $m[0],
                $m['content'] ?? $m[1],
                $m['tool_calls'] ?? $m[2],
                $m['tool_call_id'] ?? $m[3],
                !empty($images) ? json_decode($images) : []
            );
        }
    }

    public function setOnMessageSave(callable $fnc)
    {
        $this->onMessageSave = $fnc;
    }

    public function __toString(): string
    {
        return json_encode($this->messages, JSON_PRETTY_PRINT);
    }
}
