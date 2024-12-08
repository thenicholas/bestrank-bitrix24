<?php


use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\IO\FileNotFoundException;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\SystemException;
use Nick\Course\Migrations\HlBlock;
use Nick\Course\Migrations\IBlock;

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
     * @return void
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
            Loader::requireModule($this->MODULE_ID);

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

        $eventManager->registerEventHandler(
            'main',
            'OnBuildGlobalMenu',
            $this->MODULE_ID,
            '\Nick\Course\Handler\BuildGlobalMenu',
            'addMenuItem'
        );

        $eventManager->registerEventHandler(
            'main',
            'OnEpilog',
            $this->MODULE_ID,
            '\Nick\Course\Handler\Epilog',
            'includeJsLibraries'
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
            '\Nick\Course\Handler\BuildGlobalMenu',
            'addMenuItem'
        );

        $eventManager->unRegisterEventHandler(
            'main',
            'OnEpilog',
            $this->MODULE_ID,
            '\Nick\Course\Handler\Epilog',
            'includeJsLibraries'
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

        copyDirFiles(
            __DIR__ . '/js',
            Application::getDocumentRoot() . '/local/js/nick_course/',
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

        DeleteDirFilesEx('/local/js/nick_course');
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
            $HighLoadBlockId = HlBlock::up();
            if ($HighLoadBlockId) {
                Option::set($this->MODULE_ID, 'GRADE_LIST_ID', $HighLoadBlockId);
            }
        } else {
            Option::set($this->MODULE_ID, 'GRADE_LIST_ID', '');
            Option::set($this->MODULE_ID, 'GRADES_FIELD_NAME', '');
        }

        if ($request->getQuery('create_competence_list') === 'Y') {
            $competenceListIblockId = IBlock::up();
            if ($competenceListIblockId) {
                Option::set($this->MODULE_ID, 'USER_COMPETENCE_LIST_ID', $competenceListIblockId);
            }
        } else {
            Option::set($this->MODULE_ID, 'USER_COMPETENCE_LIST_ID', '');
            Option::set($this->MODULE_ID, 'USER_COMPETENCE_LIST_USER_PROP_ID', '');
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
            Loader::requireModule($this->MODULE_ID);
            HlBlock::down();
            IBlock::down();
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
