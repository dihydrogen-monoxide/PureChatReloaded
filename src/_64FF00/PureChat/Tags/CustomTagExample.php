<?php
namespace _64FF00\PureChat\Tags;

use _64FF00\PureChat\Errors\ErrorHelper;
use pocketmine\Player;

class CustomTagExample implements CustomTagInterface
{
  public function onAdd(){ echo "Added"; }

  public function onRemove(){ echo "Removed"; }

  public function onError($code){ echo "\n\nFail Added $code, " . ErrorHelper::getDetails($code) . "\n\n"; }

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
      "test" => "test1tag",
      "testing" => "tag2",
      //"suffix" => "callable fucntion"
    ];
  }

  public function test1tag(Player $player)
  {
    return "test!";//do something here with player
  }

  public function tag2(Player $player)
  {
    return "testing!";//do something here with player
  }
}