<?php
/* Made By Thunder33345 */
namespace _64FF00\PureChat\Errors;
class ErrorHelper
{
  const undefinedError = "0x0";
  const notLowercased = "1x1";
  const invalidChar = "1x2";
  const prefixUsed = "2x1";

  public static function getDetails($code) //used for debugging
  {
    switch ($code) {
      case self::undefinedError:
        return "Undefined Error";
      case self::notLowercased:
        return "Prefix/Suffix Is Not Lowercased";
      case self::invalidChar:
        return "Invalid Charater Used In Prefix/Suffix";
      case self::prefixUsed:
        return "Prefix Is Used";
    }
    return "Unknown Error";
  }
}