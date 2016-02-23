<?php

namespace _64FF00\PureChat\factions;

use pocketmine\Player;

class XeviousPE_Factions implements FactionsInterface
{
    /*
        PureChat by 64FF00 (Twitter: @64FF00)

          888  888    .d8888b.      d8888  8888888888 8888888888 .d8888b.   .d8888b.
          888  888   d88P  Y88b    d8P888  888        888       d88P  Y88b d88P  Y88b
        888888888888 888          d8P 888  888        888       888    888 888    888
          888  888   888d888b.   d8P  888  8888888    8888888   888    888 888    888
          888  888   888P "Y88b d88   888  888        888       888    888 888    888
        888888888888 888    888 8888888888 888        888       888    888 888    888
          888  888   Y88b  d88P       888  888        888       Y88b  d88P Y88b  d88P
          888  888    "Y8888P"        888  888        888        "Y8888P"   "Y8888P"
    */

    private $plugin;

    /**
     * XeviousPE_Factions constructor.
     * @param \_64FF00\XeviousPE_Factions\Tribble $plugin
     */
    public function __construct(\_64FF00\XeviousPE_Factions\Tribble $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param Player $player
     * @return mixed
     */
    public function getPlayerFaction(Player $player)
    {
        return $this->plugin->getProvider()->getPlayerFaction($player->getName());
    }

    public function getPlayerRank(Player $player)
    {

    }
}