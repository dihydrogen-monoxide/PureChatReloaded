<?php
namespace _64FF00\PureChat\Tags;

use pocketmine\Player;

class CustomTagExample implements CustomTagInterface
{
  public function onAdd(): void { echo "Added"; }

  public function onRemove(): void { echo "Removed"; }

  public function onFailedAdd($code): void { echo "Fail Added"; }

  public function getAPI()
  {

  }

  public function getPrefix(): string
  {
    return "test"; //This indicates the prefix of the said tag this would be test_(stffix)
  }

  public function getAllTags(): array
  {
    return [
      "test1" => "test1tag",
      "test2" => "tag2",
      //"suffix" => "callable fucntion"
    ];
  }

  public function test1tag(Player $player)
  {
    return "test";//do something here with player
  }

  public function tag2(Player $player)
  {
    return "test2";//do something here with player
  }
}