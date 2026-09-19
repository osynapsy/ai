<?php
namespace Osynapsy\Ai\Prompt\Section;

use Osynapsy\Ai\Prompt\PromptSection;

/**
 * Description of Dataset
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class Dataset extends PromptSection
{
    protected $content = [];
    
    public function add(mixed $dataset)
    {
        $this->content = is_array($dataset) ? $dataset : [$dataset];
    }
    
    public function getContent(): string 
    {
        return json_encode($this->getDataset(), JSON_PRETTY_PRINT);
    }
    
    public function getDataset(): array 
    {
        return $this->content;
    }
    
    public function __toString(): string
    {        
        return sprintf("### %s\n%s", $this->getLabel() , $this->getContent());
    }
}
