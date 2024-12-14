<?php

namespace Nick\Course\Helper;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;

class Options
{
    public const moduleId = 'nick.course';

    public static function getModuleId(): string
    {
        return self::moduleId;
    }

    /**
     * Получить путь к модулю
     * @param  bool  $absolute
     * @return string
     */
    public static function getModuleDir(bool $absolute = false): string
    {
        if ($absolute)
            return str_replace('lib/Helper', "", __DIR__);

        return str_replace([Application::getDocumentRoot(), 'lib/Helper'], "", __DIR__);
    }

    public static function getParam(string $code, $default = null): string
    {
        return trim(Option::get(self::getModuleId(), $code, $default));
    }

    /**
     * @throws ArgumentOutOfRangeException
     */
    public static function setParam(string $code, $value, $default = null): void
    {
        Option::set(self::getModuleId(), $code, $value, $default);
    }


}
