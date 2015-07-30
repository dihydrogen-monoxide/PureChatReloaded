<?php

namespace _64FF00\PureChat;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\TextFormat;

/* PureChat by 64FF00 (xktiverz@gmail.com, @64ff00 for Twitter) */
/*
      # #    #####  #       ####### #######   ###     ###   
      # #   #     # #    #  #       #        #   #   #   #  
    ####### #       #    #  #       #       #     # #     # 
      # #   ######  #    #  #####   #####   #     # #     # 
    ####### #     # ####### #       #       #     # #     # 
      # #   #     #      #  #       #        #   #   #   #  
      # #    #####       #  #       #         ###     ###                                        
                                                                                       
*/

class PureChat extends PluginBase
{
    private $factionsPro;
    
    public function onLoad()
    {
        $this->saveDefaultConfig();
        
        if($this->getConfig()->getNested("enable-multiworld-support"))
        {
            $this->getLogger()->notice("Successfully enabled PureChat multiworld support");
        }
    }
    
    public function onEnable()
    {
        $this->PurePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
        $this->factionsPro = $this->getServer()->getPluginManager()->getPlugin("FactionsPro");
        
        $this->getServer()->getPluginManager()->registerEvents(new ChatListener($this), $this);
    }
    
    public function onDisable()
    {
    }

    /**
     * @param $chatFormat
     * @return mixed
     */
    public function addColors($chatFormat)
    {
        $chatFormat = str_replace("{COLOR_BLACK}", TextFormat::BLACK, $chatFormat);
        $chatFormat = str_replace("{COLOR_DARK_BLUE}", TextFormat::DARK_BLUE, $chatFormat);
        $chatFormat = str_replace("{COLOR_DARK_GREEN}", TextFormat::DARK_GREEN, $chatFormat);
        $chatFormat = str_replace("{COLOR_DARK_AQUA}", TextFormat::DARK_AQUA, $chatFormat);
        $chatFormat = str_replace("{COLOR_DARK_RED}", TextFormat::DARK_RED, $chatFormat);
        $chatFormat = str_replace("{COLOR_DARK_PURPLE}", TextFormat::DARK_PURPLE, $chatFormat);
        $chatFormat = str_replace("{COLOR_GOLD}", TextFormat::GOLD, $chatFormat);
        $chatFormat = str_replace("{COLOR_GRAY}", TextFormat::GRAY, $chatFormat);
        $chatFormat = str_replace("{COLOR_DARK_GRAY}", TextFormat::DARK_GRAY, $chatFormat);
        $chatFormat = str_replace("{COLOR_BLUE}", TextFormat::BLUE, $chatFormat);
        $chatFormat = str_replace("{COLOR_GREEN}", TextFormat::GREEN, $chatFormat);
        $chatFormat = str_replace("{COLOR_AQUA}", TextFormat::AQUA, $chatFormat);
        $chatFormat = str_replace("{COLOR_RED}", TextFormat::RED, $chatFormat);
        $chatFormat = str_replace("{COLOR_LIGHT_PURPLE}", TextFormat::LIGHT_PURPLE, $chatFormat);
        $chatFormat = str_replace("{COLOR_YELLOW}", TextFormat::YELLOW, $chatFormat);
        $chatFormat = str_replace("{COLOR_WHITE}", TextFormat::WHITE, $chatFormat);

        $chatFormat = str_replace("{FORMAT_OBFUSCATED}", TextFormat::OBFUSCATED, $chatFormat);
        $chatFormat = str_replace("{FORMAT_BOLD}", TextFormat::BOLD, $chatFormat);
        $chatFormat = str_replace("{FORMAT_STRIKETHROUGH}", TextFormat::STRIKETHROUGH, $chatFormat);
        $chatFormat = str_replace("{FORMAT_UNDERLINE}", TextFormat::UNDERLINE, $chatFormat);
        $chatFormat = str_replace("{FORMAT_ITALIC}", TextFormat::ITALIC, $chatFormat);

        $chatFormat = str_replace("{FORMAT_RESET}", TextFormat::RESET, $chatFormat);

        return $chatFormat;
    }

    /**
     * @param Player $player
     * @param $message
     * @param null $levelName
     * @return mixed
     */
    public function formatMessage(Player $player, $message, $levelName = null)
    {
        $group = $this->PurePerms->getUser($player)->getGroup($levelName);
        
        $groupName = $group->getName();
        
        if($levelName == null)
        {
            if($this->getConfig()->getNested("groups.$groupName.default-chat") == null)
            {
                $this->getConfig()->setNested("groups.$groupName.default-chat", "[$groupName] {display_name} > {message}");

                $this->saveConfig();
            }

            $chatFormat = $this->getConfig()->getNested("groups.$groupName.default-chat");
        }
        else
        {
            if($this->getConfig()->getNested("groups.$groupName.worlds.$levelName.default-chat") == null)
            {
                $this->getConfig()->setNested("groups.$groupName.worlds.$levelName.default-chat", "[$groupName] {display_name} > {message}");
                
                $this->saveConfig();
            }

            $chatFormat = $this->getConfig()->getNested("groups.$groupName.worlds.$levelName.default-chat");
        }

        $chatFormat = str_replace("{world_name}", $levelName, $chatFormat);
        $chatFormat = str_replace("{display_name}", $player->getDisplayName(), $chatFormat);
        $chatFormat = str_replace("{user_name}", $player->getName(), $chatFormat);
        $chatFormat = str_replace("{message}", $message, $chatFormat);
        
        if($this->factionsPro != null) 
        {
            if($this->getConfig()->getNested("custom-no-fac-message") == null)
            {
                $this->getConfig()->setNested("custom-no-fac-message", "...");

                $this->saveConfig();
            }

            if(!$this->factionsPro->isInFaction($player->getName()))
            {
                $chatFormat = str_replace("{faction}", $this->getConfig()->getNested("custom-no-fac-message"), $chatFormat);
            }

            if($this->factionsPro->isLeader($player->getName()))
            {
                $chatFormat = str_replace("{faction}", "**" . $this->factionsPro->getPlayerFaction($player->getName()), $chatFormat);
            }
            elseif($this->factionsPro->isOfficer($player->getName()))
            {
                $chatFormat = str_replace("{faction}", "*" . $this->factionsPro->getPlayerFaction($player->getName()), $chatFormat);
            }
            else
            {
                $chatFormat = str_replace("{faction}", "" . $this->factionsPro->getPlayerFaction($player->getName()), $chatFormat);
            }
        }

        $chatFormat = $this->addColors($chatFormat);

        if(!$player->hasPermission("pchat.colored")) $chatFormat = $this->removeColors($chatFormat);

        return $chatFormat;
    }

    /**
     * @param Player $player
     * @param $levelName
     * @return mixed
     */
    public function getNameTag(Player $player, $levelName)
    {
        $group = $this->PurePerms->getUser($player)->getGroup($levelName);
        
        $groupName = $group->getName();
        
        if($levelName == null)
        {
            if($this->getConfig()->getNested("groups.$groupName.default-nametag") == null)
            {
                $this->getConfig()->setNested("groups.$groupName.default-nametag", "[$groupName] {display_name}");
            }
            
            $nameTag = $this->getConfig()->getNested("groups.$groupName.default-nametag");
        }
        else
        {
            if($this->getConfig()->getNested("groups.$groupName.worlds.$levelName.default-nametag") == null)
            {
                $this->getConfig()->setNested("groups.$groupName.worlds.$levelName.default-nametag", "[$groupName] {display_name}");
                
                $this->getConfig()->save();
            }
            
            $nameTag = $this->getConfig()->getNested("groups.$groupName.worlds.$levelName.default-nametag");
        }
        
        $nameTag = str_replace("{world_name}", $levelName, $nameTag);
        $nameTag = str_replace("{display_name}", $player->getDisplayName(), $nameTag);
        $nameTag = str_replace("{user_name}", $player->getName(), $nameTag);
        
        if($this->factionsPro != null) 
        {
            if($this->getConfig()->getNested("custom-no-fac-message") == null)
            {
                $this->getConfig()->setNested("custom-no-fac-message", "...");

                $this->saveConfig();
            }

            if(!$this->factionsPro->isInFaction($player->getName()))
            {            
                $nameTag = str_replace("{faction}", $this->getConfig()->getNested("custom-no-fac-message"), $nameTag);
            }

            if($this->factionsPro->isLeader($player->getName()))
            {
                $nameTag = str_replace("{faction}", "**" . $this->factionsPro->getPlayerFaction($player->getName()), $nameTag);
            }
            elseif($this->factionsPro->isOfficer($player->getName()))
            {
                $nameTag = str_replace("{faction}", "*" . $this->factionsPro->getPlayerFaction($player->getName()), $nameTag);
            }
            else
            {
                $nameTag = str_replace("{faction}", "" . $this->factionsPro->getPlayerFaction($player->getName()), $nameTag);
            }
        }

        $nameTag = $this->addColors($nameTag);

        if(!$player->hasPermission("pchat.colored")) $nameTag = $this->removeColors($nameTag);

        return $nameTag;
    }

    /**
     * @param $chatFormat
     * @return mixed
     */
    public function removeColors($chatFormat)
    {
        $chatFormat = str_replace(TextFormat::BLACK, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::DARK_BLUE, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::DARK_GREEN, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::DARK_AQUA, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::DARK_RED, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::DARK_PURPLE, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::GOLD, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::GRAY, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::DARK_GRAY, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::BLUE, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::GREEN, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::AQUA, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::RED, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::LIGHT_PURPLE, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::YELLOW, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::WHITE, "", $chatFormat);

        $chatFormat = str_replace(TextFormat::OBFUSCATED, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::BOLD, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::STRIKETHROUGH, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::UNDERLINE, "", $chatFormat);
        $chatFormat = str_replace(TextFormat::ITALIC, "", $chatFormat);

        $chatFormat = str_replace(TextFormat::RESET, "", $chatFormat);

        return $chatFormat;
    }
}