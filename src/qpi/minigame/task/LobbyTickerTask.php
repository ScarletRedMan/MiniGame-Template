<?php

namespace qpi\minigame\task;

use pocketmine\scheduler\Task;
use qpi\minigame\game\GameManager;

class LobbyTickerTask extends Task {

    public function onRun(): void {
        GameManager::getInstance()->tickLobbies();
    }
}