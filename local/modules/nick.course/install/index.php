<?php


use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

class nick_course extends CModule
{
    public $MODULE_ID = 'nick.course';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    private array $errors = [];

    protected Application $application;

    function __construct()
    {
        $arModuleVersion = [];
        include(dirname(__FILE__) . '/version.php');

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $this->MODULE_NAME = Loc::getMessage('NI_CO_ST_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('NI_CO_ST_MODULE_DESC');
        $this->PARTNER_NAME = Loc::getMessage('NI_CO_ST_PARTNER');
        $this->PARTNER_URI = Loc::getMessage('NI_CO_ST_PARTNER_URL');
    }

    /**
     * @return bool
     * @throws Exception
     */
    function DoInstall(): bool
    {
        global $APPLICATION;

        ModuleManager::registerModule($this->MODULE_ID);

        match (false) {
            $this->InstallEvents() => $this->errors[] = Loc::getMessage(
                'NI_CO_MODULE_INSTALL_ERROR',
                ['#METHOD#' => Loc::getMessage('NI_CO_MODULE_INSTALL_ERROR_EVENTS')]
            ),
            $this->InstallDB() => $this->errors[] = Loc::getMessage(
                'NI_CO_MODULE_INSTALL_ERROR',
                ['#METHOD#' => Loc::getMessage('NI_CO_MODULE_INSTALL_ERROR_DB')]
            ),
            $this->InstallFiles() => $this->errors[] = Loc::getMessage(
                'NI_CO_MODULE_INSTALL_ERROR',
                ['#METHOD#' => Loc::getMessage('NI_CO_MODULE_INSTALL_ERROR_FILES')]
            ),
            default => null
        };

        if ($this->errors) {
            ModuleManager::unRegisterModule($this->MODULE_ID);
            $APPLICATION->ThrowException(implode("<br>", $this->errors));
            return false;
        }

        $APPLICATION->includeAdminFile(
            Loc::getMessage('NI_CO_USER_RATING_INSTALL_TITLE'),
            $this->getPath() . '/install/step1.php'
        );
        return true;
    }

    function DoUninstall(): void
    {
        global $APPLICATION, $step;

        $step = intval($step);
        if ($step < 2) {
            $APPLICATION->includeAdminFile(
                Loc::getMessage('NI_CO_USER_RATING_UNINSTALL_TITLE'),
                $this->getPath() . '/install/unstep1.php'
            );
        } elseif ($step == 2) {
            $this->UnInstallEvents();
            $this->uninstallDB();
            $this->uninstallFiles();

            ModuleManager::unRegisterModule($this->MODULE_ID);
            $APPLICATION->includeAdminFile(
                Loc::getMessage('NI_CO_USER_RATING_UNINSTALL_TITLE'),
                $this->getPath() . '/install/unstep2.php'
            );
        }
    }

    function InstallEvents(): bool
    {
        $eventManager = EventManager::getInstance();

        //Регистрация метода для расширения меню в администартивном разделе
        $eventManager->registerEventHandler(
            'main',
            'OnBuildGlobalMenu',
            $this->MODULE_ID,
            '\Nick\Course\Handler\BuildGlobalMenu',
            'addMenuItem'
        );

        return true;
    }

    function UnInstallEvents(): bool
    {
        $eventManager = EventManager::getInstance();
        //Удаление регистрация метода для расширения меню в администартивном разделе
        $eventManager->unRegisterEventHandler(
            'main',
            'OnBuildGlobalMenu',
            $this->MODULE_ID,
            '\Study\UserRating\Handlers\BuildGlobalMenu',
            'addMenuItem'
        );

        return true;
    }

    function InstallFiles(): bool
    {
        if (!self::checkWritableLocal(['local'])) {
            return false;
        }
        copyDirFiles(
            __DIR__ . '/admin',
            Application::getDocumentRoot() . '/bitrix/admin/',
            true,
            true
        );

        return true;
    }

    function UnInstallFiles(): bool
    {
        $adminPath = __DIR__ . '/admin';
        $directory = new Bitrix\Main\IO\Directory($adminPath);

        foreach ($directory->getChildren() as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $bitrixAdminFile = Application::getDocumentRoot() . '/bitrix/admin/' . $file->getName();

            if (file_exists($bitrixAdminFile)) {
                Bitrix\Main\IO\File::deleteFile($bitrixAdminFile);
            }
        }

        return true;
    }

    /**
     * @throws Exception
     */
    function InstallDB(): bool
    {
        global $DB;

        $sqlError = $DB->RunSQLBatch($this->getPath() . '/install/db/' . mb_strtolower($DB->type) . '/install.sql');

        if ($sqlError !== false) {
            $this->errors = array_merge($this->errors, $sqlError);
            return false;
        }

        return true;
    }

    /**
     * @throws Exception
     */
    function UnInstallDB(): bool
    {
        global $DB;

        $request = Application::getInstance()->getContext()->getRequest();

        if ($request->getQuery('save_tables') !== 'Y') {
            $sqlError = $DB->RunSQLBatch(
                $this->getPath() . '/install/db/' . mb_strtolower($DB->type) . '/uninstall.sql'
            );
            if ($sqlError !== false) {
                $this->errors = array_merge($this->errors, $sqlError);
                return false;
            }
        }
        return true;
    }

    protected function getPath($notDocumentRoot = false): array|string|null
    {
        $path = dirname(__DIR__);
        $path = str_replace("\\", '/', $path);
        return ($notDocumentRoot)
            ? preg_replace('#^(.*)/(local|bitrix)/modules#', '/$2/modules', $path)
            : $path;
    }

    //Проверка доступности директории

    /**
     * @throws Exception
     */
    protected function checkWritableLocal(array $arrDirs): bool
    {
        foreach ($arrDirs as $dir) {
            $localDirPath = Application::getDocumentRoot() . '/' . $dir;
            \Bitrix\Main\IO\Directory::createDirectory($localDirPath);
            if (!is_writable($localDirPath)) {
                $this->errors[] = Loc::getMessage('NI_CO_BR_ERROR_LOCAL_PATH', ['#DIR_NAME#' => $localDirPath]);
                return false;
            }
        }
        return true;
    }

}
