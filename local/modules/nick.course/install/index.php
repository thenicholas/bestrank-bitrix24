<?php


use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\IO\FileNotFoundException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\SystemException;
use Nick\Course\Migrations\HlBlock;

class nick_course extends CModule
{
    public $MODULE_ID = 'nick.course';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    protected Application $application;

    public function __construct()
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
    public function DoInstall(): void
    {
        global $APPLICATION, $step;

        try {
            if ($step < 2) {
                $APPLICATION->includeAdminFile(
                    Loc::getMessage('NI_CO_USER_RATING_INSTALL_TITLE'),
                    $this->getPath() . '/install/step1.php'
                );
            }

            ModuleManager::registerModule($this->MODULE_ID);

            $this->InstallEvents();

            $this->InstallDB();

            $this->InstallFiles();
        } catch (Exception $e) {
            ModuleManager::unRegisterModule($this->MODULE_ID);
            $APPLICATION->ThrowException($e->getMessage());
        }

        $APPLICATION->includeAdminFile(
            Loc::getMessage('NI_CO_USER_RATING_INSTALL_TITLE'),
            $this->getPath() . '/install/step2.php'
        );
    }

    /**
     * @throws Exception
     */
    public function DoUninstall(): void
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

    public function InstallEvents(): void
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
    }

    public function UnInstallEvents(): void
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
    }

    /**
     * @throws Exception
     */
    public function InstallFiles(): void
    {
        self::checkWritableLocal(['local']);

        copyDirFiles(
            __DIR__ . '/admin',
            Application::getDocumentRoot() . '/bitrix/admin/',
            true,
            true
        );
    }

    /**
     * @throws FileNotFoundException
     */
    public function UnInstallFiles(): void
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
    }

    /**
     * @throws Exception
     */
    public function InstallDB(): void
    {
        global $DB;

        $sqlError = $DB->RunSQLBatch($this->getPath() . '/install/db/' . mb_strtolower($DB->type) . '/install.sql');

        if ($sqlError !== false) {
            throw new SystemException(implode(", ", $sqlError));
        }

        $request = Application::getInstance()->getContext()->getRequest();
        if ($request->getQuery('create_hlblock') === 'Y') {
            \Bitrix\Main\Loader::includeModule($this->MODULE_ID);
            $HighLoadBlockId = HlBlock::up();
            if ($HighLoadBlockId) {
                Option::set($this->MODULE_ID, 'GRADE_LIST_ID', $HighLoadBlockId);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function UnInstallDB(): void
    {
        global $DB;

        $request = Application::getInstance()->getContext()->getRequest();

        if ($request->getQuery('save_tables') !== 'Y') {
            $sqlError = $DB->RunSQLBatch(
                $this->getPath() . '/install/db/' . mb_strtolower($DB->type) . '/uninstall.sql'
            );
            if ($sqlError !== false) {
                throw new SystemException(implode(', ', $sqlError));
            }
            \Bitrix\Main\Loader::requireModule($this->MODULE_ID);
            HlBlock::down();
        }
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
                throw new SystemException(
                    Loc::getMessage('NI_CO_BR_ERROR_LOCAL_PATH', ['#DIR_NAME#' => $localDirPath])
                );
            }
        }
        return true;
    }

}
