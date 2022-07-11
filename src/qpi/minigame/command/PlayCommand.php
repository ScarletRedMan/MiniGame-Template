<?php

namespace qpi\minigame\command;

use form\CustomForm;
use form\ModalForm;
use form\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use qpi\minigame\game\GameManager;
use qpi\minigame\game\Lobby;

class PlayCommand extends Command {

    public function __construct() {
        parent::__construct("play", "Играть", "/play", []);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$sender instanceof Player) return;

        $gameManager = GameManager::getInstance();
        if ($gameManager->isInGame($sender)) {
            $sender->sendMessage("§cВы уже в игре!");
            return;
        }

        if ($gameManager->isInLobby($sender)) {
            $this->sendLeaveForm($sender);
            return;
        }

        $form = new SimpleForm();
        $form->setTitle("Играть");

        $form->addButton("Подбор игры", SimpleForm::IMAGE_TYPE_PATH, "", function(Player $player) {
            GameManager::getInstance()->pickLobby($player);
        });

        $form->addButton("Создать приватное лобби", SimpleForm::IMAGE_TYPE_PATH, "", function(Player $player) {
            GameManager::getInstance()->createNewLobby($player, true);
        });

        $form->addButton("Присоедениться к лобби", SimpleForm::IMAGE_TYPE_PATH, "", function(Player $player) {
            $this->sendConnectToSomeLobby($player);
        });

        $form->sendToPlayer($sender);
    }

    private function sendLeaveForm(Player $player): void {
        $form = new ModalForm(function(Player $player, $data) {
            if (!$data) return;

            GameManager::getInstance()->leaveFromLobby($player);
            $player->sendMessage("§eВы покинули лобби.");
        });

        $form->setTitle("Ошибка");
        $form->setContent("Вы уже находитесь в лобби. Но если вы хотите выйти из данного лобби, то нажмите кнопку §3'Выйти из лобби'§f. В случае, если вы хотите присоединиться к другой игре, то после выхода введите команду §2/play§f.");
        $form->setPositiveButton("§l§4Выйти из лобби");
        $form->setNegativeButton("Отмена");
        $form->sendToPlayer($player);
    }

    private function sendConnectToSomeLobby(Player $player): void {
        $form = new CustomForm(function(Player $player, array $data) {
            $lobby = GameManager::getInstance()->findLobby(trim($data['lobby']));

            if ($lobby === null) {
                $player->sendMessage("§cЛобби с таким ID не найден.");
                return;
            }

            if ($lobby->getStatus() !== Lobby::STATUS_WAITING) {
                $player->sendMessage("§cЛидер лобби уже начал игру.");
                return;
            }

            GameManager::getInstance()->joinToLobby($player, $lobby->getId());
        });

        $form->setTitle("Играть");
        $form->addLabel("Введите ID лобби чтобы присоедениться.");
        $form->addInput("ID лобби", "§8Пример: 2281337", key: "lobby");

        $form->sendToPlayer($player);
    }
}