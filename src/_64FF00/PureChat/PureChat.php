<?php

namespace _64FF00\PureChat;

use _64FF00\PureChat\factions\FactionsInterface;
use _64FF00\PureChat\factions\FactionsProNew;
use _64FF00\PureChat\factions\FactionsProOld;
use _64FF00\PureChat\factions\XeviousPE_Factions;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\TextFormat;

class PureChat extends PluginBase
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

    const MAIN_PREFIX = "\x5b\x50\x75\x72\x65\x43\x68\x61\x74\x3a\x36\x34\x46\x46\x30\x30\x5d";

    /** @var FactionsInterface $factionsAPI */
    private $factionsAPI;

    /** @var \_64FF00\PurePerms\PurePerms $purePerms */
    private $purePerms;

    public function onLoad()
    {
        $this->saveDefaultConfig();

        $this->purePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
    }
    
    public function onEnable()
    {
        $this->loadFactionsPlugin();

        $this->getServer()->getPluginManager()->registerEvents(new PCListener($this), $this);
    }

    /**
     * @param CommandSender $sender
     * @param Command $cmd
     * @param string $label
     * @param array $args
     */
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args)
    {
        switch(strtolower($cmd->getName()))
        {
            case "setprefix":

                if(!$sender instanceof Player)
                {
                    $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " This command can be only used in-game.");

                    return true;
                }

                if(!isset($args[0]))
                {
                    $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " Usage: /setprefix <prefix>");

                    return true;
                }

                $levelName = $this->getConfig()->get("enable-multiworld-chat") ? $sender->getLevel()->getName() : null;

                $prefix = str_replace("{BLANK}", ' ', implode('', $args));

                $this->setPrefix($prefix, $sender, $levelName);

                $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " You set your prefix to " . $prefix . ".");

                break;

            case "setsuffix":

                if(!$sender instanceof Player)
                {
                    $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " This command can be only used in-game.");

                    return true;
                }

                if(!isset($args[0]))
                {
                    $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " Usage: /setsuffix <suffix>");

                    return true;
                }

                $levelName = $this->getConfig()->get("enable-multiworld-chat") ? $sender->getLevel()->getName() : null;

                $suffix = str_replace("{BLANK}", ' ', implode('', $args));

                $this->setSuffix($suffix, $sender, $levelName);

                $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " You set your suffix to " . $suffix . ".");

                break;
        }

        return true;
    }

    private function loadFactionsPlugin()
    {
        $factionsPluginName = $this->getConfig()->get("default-factions-plugin");

        if($factionsPluginName === null)
        {
            $this->getLogger()->notice("No valid factions plugin in default-factions-plugin node was found. Disabling factions plugin support.");
        }
        else
        {
            switch(strtolower($factionsPluginName))
            {
                case "factionspro":

                    $factionsPro = $this->getServer()->getPluginManager()->getPlugin("FactionsPro");

                    if($factionsPro !== null)
                    {
                        if(version_compare($factionsPro->getDescription()->getVersion(), "1.5b1") === -1)
                        {
                            $this->factionsAPI = new FactionsProOld();

                            $this->getLogger()->notice("FactionsPro-OLD support enabled.");

                            break;
                        }
                        else
                        {
                            $this->factionsAPI = new FactionsProNew();

                            $this->getLogger()->notice("FactionsPro-NEW support enabled.");

                            break;
                        }
                    }

                    $this->getLogger()->notice("No valid factions plugin in default-factions-plugin node was found. Disabling factions plugin support.");

                    break;

                case "xeviouspe-factions":

                    if($this->getServer()->getPluginManager()->getPlugin("XeviousPE-Factions") !== null)
                    {
                        $this->factionsAPI = new XeviousPE_Factions();

                        $this->getLogger()->notice("XeviousPE-Factions support enabled.");

                        break;
                    }

                    $this->getLogger()->notice("No valid factions plugin in default-factions-plugin node was found. Disabling factions plugin support.");

                    break;

                default:

                    $this->getLogger()->notice("No valid factions plugin in default-factions-plugin node was found. Disabling factions plugin support.");

                    break;
            }
        }
    }

    /*
          888  888          d8888 8888888b. 8888888
          888  888         d88888 888   Y88b  888
        888888888888      d88P888 888    888  888
          888  888       d88P 888 888   d88P  888
          888  888      d88P  888 8888888P"   888
        888888888888   d88P   888 888         888
          888  888    d8888888888 888         888
          888  888   d88P     888 888       8888888
    */

    /**
     * @param $string
     * @return mixed
     */
    public function applyColors($string)
    {
        $string = str_replace("&0", TextFormat::BLACK, $string);
        $string = str_replace("&1", TextFormat::DARK_BLUE, $string);
        $string = str_replace("&2", TextFormat::DARK_GREEN, $string);
        $string = str_replace("&3", TextFormat::DARK_AQUA, $string);
        $string = str_replace("&4", TextFormat::DARK_RED, $string);
        $string = str_replace("&5", TextFormat::DARK_PURPLE, $string);
        $string = str_replace("&6", TextFormat::GOLD, $string);
        $string = str_replace("&7", TextFormat::GRAY, $string);
        $string = str_replace("&8", TextFormat::DARK_GRAY, $string);
        $string = str_replace("&9", TextFormat::BLUE, $string);
        $string = str_replace("&a", TextFormat::GREEN, $string);
        $string = str_replace("&b", TextFormat::AQUA, $string);
        $string = str_replace("&c", TextFormat::RED, $string);
        $string = str_replace("&d", TextFormat::LIGHT_PURPLE, $string);
        $string = str_replace("&e", TextFormat::YELLOW, $string);
        $string = str_replace("&f", TextFormat::WHITE, $string);
        $string = str_replace("&k", TextFormat::OBFUSCATED, $string);
        $string = str_replace("&l", TextFormat::BOLD, $string);
        $string = str_replace("&m", TextFormat::STRIKETHROUGH, $string);
        $string = str_replace("&n", TextFormat::UNDERLINE, $string);
        $string = str_replace("&o", TextFormat::ITALIC, $string);
        $string = str_replace("&r", TextFormat::RESET, $string);

        return $string;
    }

    /**
     * @param $string
     * @param Player $player
     * @param $message
     * @param null $levelName
     * @return mixed
     */
    public function applyPCTags($string, Player $player, $message, $levelName)
    {
        // TODO
        $string = str_replace("{DISPLAY_NAME}", $player->getDisplayName(), $string);

        if($message === null)
            $message = '';

        if($player->hasPermission("pchat.coloredMessages"))
        {
            $string = str_replace("{MESSAGE}", $message, $string);
        }
        else
        {
            $string = str_replace("{MESSAGE}", $this->stripColors($message), $string);
        }

        if($this->factionsAPI !== null)
        {
            $string = str_replace("{FACTION_NAME}", $this->factionsAPI->getPlayerFaction($player), $string);
            $string = str_replace("{FACTION_RANK}", $this->factionsAPI->getPlayerRank($player), $string);
        }
        else
        {
            $string = str_replace("{FACTION_NAME}", '', $string);
            $string = str_replace("{FACTION_RANK}", '', $string);
        }

        $string = str_replace("{WORLD_NAME}", ($levelName === null ? "" : $levelName), $string);

        $string = str_replace("{PREFIX}", $this->getPrefix($player, $levelName), $string);
        $string = str_replace("{SUFFIX}", $this->getSuffix($player, $levelName), $string);

        return $string;
    }

    /**
     * @param Player $player
     * @param $message
     * @param null $levelName
     * @return mixed
     */
    public function getChatFormat(Player $player, $message, $levelName = null)
    {
        $originalChatFormat = $this->getOriginalChatFormat($player, $levelName);

        $chatFormat = $this->applyColors($originalChatFormat);
        $chatFormat = $this->applyPCTags($chatFormat, $player, $message, $levelName);

        return $chatFormat;
    }

    /**
     * @param Player $player
     * @param null $levelName
     * @return mixed
     */
    public function getNametag(Player $player, $levelName = null)
    {
        $originalNametag = $this->getOriginalNametag($player, $levelName);

        $nameTag = $this->applyColors($originalNametag);
        $nameTag = $this->applyPCTags($nameTag, $player, null, $levelName);

        return $nameTag;
    }

    /**
     * @param Player $player
     * @param null $levelName
     * @return mixed
     */
    public function getOriginalChatFormat(Player $player, $levelName = null)
    {
        /** @var \_64FF00\PurePerms\PPGroup $group */
        $group = $this->purePerms->getUserDataMgr()->getGroup($player, $levelName);

        if($levelName === null)
        {
            if($this->getConfig()->getNested("groups." . $group->getName() . ".chat") === null)
            {
                $this->getLogger()->critical("Invalid chat format found in config.yml (Group: " . $group->getName() . ") / Setting it to default value.");

                $this->getConfig()->setNested("groups." . $group->getName() . ".chat", "&8&l[" . $group->getName() . "]&f&r {DISPLAY_NAME} &7> {MESSAGE}");

                $this->saveConfig();
            }

            return $this->getConfig()->getNested("groups." . $group->getName() . ".chat");
        }
        else
        {
            if($this->getConfig()->getNested("groups." . $group->getName() . "worlds.$levelName.chat") === null)
            {
                $this->getLogger()->critical("Invalid chat format found in config.yml (Group: " . $group->getName() . ", WorldName = $levelName) / Setting it to default value.");

                $this->getConfig()->setNested("groups." . $group->getName() . "worlds.$levelName.chat", "&8&l[" . $group->getName() . "]&f&r {DISPLAY_NAME} &7> {MESSAGE}");

                $this->saveConfig();
            }

            return $this->getConfig()->getNested("groups." . $group->getName() . "worlds.$levelName.chat");
        }
    }

    public function getOriginalNametag(Player $player, $levelName = null)
    {
        /** @var \_64FF00\PurePerms\PPGroup $group */
        $group = $this->purePerms->getUserDataMgr()->getGroup($player, $levelName);

        if($levelName === null)
        {
            if($this->getConfig()->getNested("groups." . $group->getName() . ".nametag") === null)
            {
                $this->getLogger()->critical("Invalid nametag found in config.yml (Group: " . $group->getName() . ") / Setting it to default value.");

                $this->getConfig()->setNested("groups." . $group->getName() . ".nametag", "&8&l[" . $group->getName() . "]&f&r {DISPLAY_NAME}");

                $this->saveConfig();
            }

            return $this->getConfig()->getNested("groups." . $group->getName() . ".nametag");
        }
        else
        {
            if($this->getConfig()->getNested("groups." . $group->getName() . "worlds.$levelName.nametag") === null)
            {
                $this->getLogger()->critical("Invalid nametag found in config.yml (Group: " . $group->getName() . ", WorldName = $levelName) / Setting it to default value.");

                $this->getConfig()->setNested("groups." . $group->getName() . "worlds.$levelName.nametag", "&8&l[" . $group->getName() . "]&f&r {DISPLAY_NAME}");

                $this->saveConfig();
            }

            return $this->getConfig()->getNested("groups." . $group->getName() . "worlds.$levelName.nametag");
        }
    }

    /**
     * @param Player $player
     * @param null $levelName
     * @return mixed|null|string
     */
    public function getPrefix(Player $player, $levelName = null)
    {
        if($levelName === null)
        {
            return $this->purePerms->getUserDataMgr()->getNode($player, "prefix");
        }
        else
        {
            $worldData = $this->purePerms->getUserDataMgr()->getWorldData($player, $levelName);

            if(!isset($worldData["prefix"]) || $worldData["prefix"] === null)
                return "";

            return $worldData["prefix"];
        }
    }

    /**
     * @param Player $player
     * @param null $levelName
     * @return mixed|null|string
     */
    public function getSuffix(Player $player, $levelName = null)
    {
        if($levelName === null)
        {
            return $this->purePerms->getUserDataMgr()->getNode($player, "suffix");
        }
        else
        {
            $worldData = $this->purePerms->getUserDataMgr()->getWorldData($player, $levelName);

            if(!isset($worldData["suffix"]) || $worldData["suffix"] === null)
                return "";

            return $worldData["suffix"];
        }
    }

    /**
     * @param Player $player
     * @param $chatFormat
     * @param null $levelName
     * @return bool
     */
    public function setOriginalChatFormat(Player $player, $chatFormat, $levelName = null)
    {
        /** @var \_64FF00\PurePerms\PPGroup $group */
        $group = $this->purePerms->getUserDataMgr()->getGroup($player, $levelName);

        if($levelName === null)
        {
            $this->getConfig()->setNested("groups." . $group->getName() . ".chat", $chatFormat);

            $this->saveConfig();
        }
        else
        {
            $this->getConfig()->setNested("groups." . $group->getName() . "worlds.$levelName.chat", $chatFormat);

            $this->saveConfig();
        }

        return true;
    }

    /**
     * @param Player $player
     * @param $nameTag
     * @param null $levelName
     * @return bool
     */
    public function setOriginalNametag(Player $player, $nameTag, $levelName = null)
    {
        /** @var \_64FF00\PurePerms\PPGroup $group */
        $group = $this->purePerms->getUserDataMgr()->getGroup($player, $levelName);

        if($levelName === null)
        {
            $this->getConfig()->setNested("groups." . $group->getName() . ".nametag", $nameTag);

            $this->saveConfig();
        }
        else
        {
            $this->getConfig()->setNested("groups." . $group->getName() . "worlds.$levelName.nametag", $nameTag);

            $this->saveConfig();
        }

        return true;
    }

    /**
     * @param $prefix
     * @param Player $player
     * @param null $levelName
     * @return bool
     */
    public function setPrefix($prefix, Player $player, $levelName = null)
    {
        if($levelName === null)
        {
            $this->purePerms->getUserDataMgr()->setNode($player, "prefix", $prefix);
        }
        else
        {
            $worldData = $this->purePerms->getUserDataMgr()->getWorldData($player, $levelName);

            $worldData["prefix"] = $prefix;

            $this->purePerms->getUserDataMgr()->setWorldData($player, $levelName, $worldData);
        }

        return true;
    }

    /**
     * @param $suffix
     * @param Player $player
     * @param null $levelName
     * @return bool
     */
    public function setSuffix($suffix, Player $player, $levelName = null)
    {
        if($levelName === null)
        {
            $this->purePerms->getUserDataMgr()->setNode($player, "suffix", $suffix);
        }
        else
        {
            $worldData = $this->purePerms->getUserDataMgr()->getWorldData($player, $levelName);

            $worldData["suffix"] = $suffix;

            $this->purePerms->getUserDataMgr()->setWorldData($player, $levelName, $worldData);
        }

        return true;
    }

    /**
     * @param $string
     * @return mixed
     */
    public function stripColors($string)
    {
        $string = str_replace(TextFormat::BLACK, '', $string);
        $string = str_replace(TextFormat::DARK_BLUE, '', $string);
        $string = str_replace(TextFormat::DARK_GREEN, '', $string);
        $string = str_replace(TextFormat::DARK_AQUA, '', $string);
        $string = str_replace(TextFormat::DARK_RED, '', $string);
        $string = str_replace(TextFormat::DARK_PURPLE, '', $string);
        $string = str_replace(TextFormat::GOLD, '', $string);
        $string = str_replace(TextFormat::GRAY, '', $string);
        $string = str_replace(TextFormat::DARK_GRAY, '', $string);
        $string = str_replace(TextFormat::BLUE, '', $string);
        $string = str_replace(TextFormat::GREEN, '', $string);
        $string = str_replace(TextFormat::AQUA, '', $string);
        $string = str_replace(TextFormat::RED, '', $string);
        $string = str_replace(TextFormat::LIGHT_PURPLE, '', $string);
        $string = str_replace(TextFormat::YELLOW, '', $string);
        $string = str_replace(TextFormat::WHITE, '', $string);
        $string = str_replace(TextFormat::OBFUSCATED, '', $string);
        $string = str_replace(TextFormat::BOLD, '', $string);
        $string = str_replace(TextFormat::STRIKETHROUGH, '', $string);
        $string = str_replace(TextFormat::UNDERLINE, '', $string);
        $string = str_replace(TextFormat::ITALIC, '', $string);
        $string = str_replace(TextFormat::RESET, '', $string);

        return $string;
    }
}
