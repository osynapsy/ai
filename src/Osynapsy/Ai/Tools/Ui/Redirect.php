<?php

namespace Osynapsy\Ai\Tools\Ui;

use Osynapsy\Ai\Tools\ToolInterface;
use Osynapsy\Controller\ControllerInterface;

class Redirect implements ToolInterface
{
    public function execute(array $arguments, ?ControllerInterface $controller = null): mixed
    {
        $url    = trim($arguments['url'] ?? '');
        $target = ($arguments['target'] ?? '_blank') === '_self' ? '_self' : '_blank';

        if ($url === '') {
            return ['error' => true, 'message' => 'URL di destinazione mancante.'];
        }

        if ($controller !== null) {
            $controller->remoteCall('myAiChat.redirect', $url, $target);
        }

        return ['output' => "Apertura pagina '{$url}' ({$target}) avviata nel browser."];
    }
}