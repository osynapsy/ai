<?php
namespace Osynapsy\Ai\Tools;

/**
 * Description of ToolInterface
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */

interface ToolInterface
{
    public function execute(array $arguments, $databaseService = null): mixed;
}
