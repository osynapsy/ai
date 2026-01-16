<?php
namespace Osynapsy\AI\Ollama\Model;

/**
 * Description of CustomModel
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class CustomModel extends Qwen_2_5
{
    protected $id;
    
    public function __construct(string $id)
    {
        $arr = explode(':', $id, 2);
        $this->id = $arr[0];
        $this->tag = $arr[1] ?? null;
    }
    
    public function getId() : string
    {
        return $this->id;
    }
}
