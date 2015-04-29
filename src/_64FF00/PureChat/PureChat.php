<?php

namespace _64FF00\PureChat;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\TextFormat;

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
    /* PurePerms by 64FF00 (xktiverz@gmail.com, @64ff00 for Twitter) */

    /*
          # #    #####  #       ####### #######   ###     ###   
          # #   #     # #    #  #       #        #   #   #   #  
        ####### #       #    #  #       #       #     # #     # 
          # #   ######  #    #  #####   #####   #     # #     # 
        ####### #     # ####### #       #       #     # #     # 
          # #   #     #      #  #       #        #   #   #   #  
          # #    #####       #  #       #         ###     ###                                        
                                                                                       
    */
    
    private $config, $plugin, $factionsPro;
    
    public function onLoad()
    {
        $this->saveDefaultConfig();
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
    
    public function formatMessage(Player $player, $message, $levelName = null)
    {
        $group = $this->PurePerms->getUser($player)->getGroup($levelName);
        
        $groupName = $group->getName();
        
        if($levelName == null)
        {
            if($this->getConfig()->getNested("groups.$groupName.default") == null)
            {
                $this->getConfig()->setNested("groups.$groupName.default", "[$groupName] {display_name} > {message}");
            }
            
            $chatFormat = $this->getConfig()->getNested("groups.$groupName.default");
        }
        else
        {
            if($this->getConfig()->getNested("groups.$groupName.worlds.$levelName") == null)
            {
                $this->getConfig()->setNested("groups.$groupName.worlds.$levelName", "[$groupName] {display_name} > {message}");
                
                $this->getConfig()->save();
            }
            
            $chatFormat = $this->getConfig()->getNested("groups.$groupName.worlds.$levelName");
        }
        
        $chatFormat = str_replace("{world_name}", $levelName, $chatFormat);
        $chatFormat = str_replace("{display_name}", $player->getDisplayName(), $chatFormat);
        $chatFormat = str_replace("{user_name}", $player->getName(), $chatFormat);
        $chatFormat = str_replace("{message}", $message, $chatFormat);
        
        if($this->factionsPro != null) 
        {
            if(!$this->factionsPro->isInFaction($player->getName()))
            {
                $chatFormat = str_replace("{faction}", "...", $chatFormat);
            }
            
            $chatFormat = str_replace("{faction}", $this->factionsPro->getPlayerFaction($player->getName()), $chatFormat);
        }
        
        return $this->addColors($chatFormat);
    }
}