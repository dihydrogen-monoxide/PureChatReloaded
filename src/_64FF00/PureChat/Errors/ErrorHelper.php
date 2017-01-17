<?php
/* Made By Thunder33345 */
namespace _64FF00\PureChat\Errors;
class ErrorHelper
{
  //0.general,1.Character,2.Prefix,3.Suffix,4.Processing
  const undefined_error = "0x0";
  const not_lowercased = "1x1";
  const invalid_char = "1x2";
  const prefix_used = "2x1";
  const suffix_func_invalid = "3x1";
  const proc_invalid_ret = "4x1";
  const proc_throw = "4x2";

  public static function getDetails($code) //used for debugging
  {
    switch ($code) {
      case self::undefined_error:
        return "Undefined Error";
      case self::not_lowercased:
        return "Prefix/Suffix Is Not Lowercased";
      case self::invalid_char:
        return "Invalid Charater Used In Prefix/Suffix";
      case self::prefix_used:
        return "Prefix Is Used";
      case self::suffix_func_invalid:
        return"Suffix Function Invalid";
      case self::proc_invalid_ret:
        return"Suffix function returned a invalid data";
      case self::proc_throw:
        return"Unknow error throwed while trying to process function";
    }
    return "Unknown Error";
  }
}