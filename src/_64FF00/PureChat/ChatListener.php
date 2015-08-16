<?php

namespace _64FF00\PureChat;

use _64FF00\PurePerms\event\PPGroupChangedEvent;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\Player;

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
    
class ChatListener implements Listener
{
    private $plugin;
    
    public function __construct(PureChat $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param PPGroupChangeEvent $event
     */
    public function onGroupChanged(PPGroupChangedEvent $event)
    {
        $player = $event->getPlayer();

        if($player instanceof Player)
        {
            $isMultiWorldSupportEnabled = $this->plugin->getConfig()->get("enable-multiworld-support");

            $levelName = $isMultiWorldSupportEnabled ? $player->getLevel()->getName() : null;

            $nameTag = $this->plugin->getNameTag($player, $levelName);

            if($player->hasPermission("pchat.colored"))
            {
                $nameTag = $this->addColors($nameTag);
            }
            else
            {
                $nameTag = $this->removeColors($nameTag);
            }

            $player->setNameTag($nameTag);
        }
    }
    
    /**
     * @param PlayerChatEvent $event
     * @priority HIGHEST
     */
    public function onPlayerChat(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();

        $isMultiWorldSupportEnabled = $this->plugin->getConfig()->get("enable-multiworld-support");
        
        $levelName = $isMultiWorldSupportEnabled ?  $player->getLevel()->getName() : null;
        
        $chatFormat = $this->plugin->formatMessage($player, $event->getMessage(), $levelName);

        if($player->hasPermission("pchat.colored"))
        {
            $chatFormat = $this->addColors($chatFormat);
        }
        else
        {
            $chatFormat = $this->removeColors($chatFormat);
        }
        
        $event->setFormat($chatFormat);
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onPlayerJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        
        $isMultiWorldSupportEnabled = $this->plugin->getConfig()->get("enable-multiworld-support");
        
        $levelName = $isMultiWorldSupportEnabled ?  $player->getLevel()->getName() : null;

        $nameTag = $this->plugin->getNameTag($player, $levelName);

        if($player->hasPermission("pchat.colored"))
        {
            $nameTag = $this->addColors($nameTag);
        }
        else
        {
            $nameTag = $this->removeColors($nameTag);
        }

        $player->setNameTag($nameTag);
    }
}