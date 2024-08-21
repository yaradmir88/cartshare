<? 
use Bitrix\Main\Application as App;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\EventManager;
use Bitrix\Main\Config\Option;
use Radmir\CartShare\SavedCartTable;


Loc::loadMessages(__FILE__);

Class radmir_cartshare extends CModule
{

    public function __construct()
    {
        $arModuleVersion = [];
		
        include __DIR__ . '/version.php';
				
        if (!empty($arModuleVersion['VERSION'])) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        }

        if (!empty($arModuleVersion['VERSION_DATE'])) {
          $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_PARTNER_ID = 'radmir';

        $this->MODULE_ID = 'radmir.cartshare';
        $this->MODULE_NAME = Loc::getMessage('RADMIR_CARTSHARE_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('RADMIR_CARTSHARE_MODULE_DESC');
        $this->MODULE_GROUP_RIGHTS = 'N';

        $this->PARTNER_NAME = Loc::getMessage('RADMIR_CARTSHARE_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('RADMIR_CARTSHARE_PARTNER_SITE');
    }
    
    public function doInstall()
    {
      global $APPLICATION;
      if ($this->isD7() && $this->checkDependentsModules()) {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->installFiles();
        $this->installDB();
        $this->installEvents();
      } elseif (!$this->isD7()) {
        $APPLICATION->ThrowException(Loc::getMessage('RADMIR_CARTSHARE_INSTALL_D7_ERROR'));
      } else {
        $APPLICATION->ThrowException(Loc::getMessage('RADMIR_CARTSHARE_INSTALL_DM_ERROR'));
      }
      
      $APPLICATION->IncludeAdminFile(Loc::getMessage('RADMIR_CARTSHARE_INSTALL_TITLE'), $this->GetPath()."/install/step.php");
        
    }
		
    public function doUninstall()
    {
        global $APPLICATION;
        $context = App::getInstance()->getContext();
        $request = $context->getRequest();

        $step = (!empty($request['step'])) ? $request['step'] : 0;

        if ($step < 2) {
          $APPLICATION->IncludeAdminFile(Loc::getMessage('RADMIR_CARTSHARE_UNINSTALL_TITLE'), $this->GetPath()."/install/unstep1.php");
        } elseif($step == 2) {
          $this->uninstallFiles();
          $this->uninstallEvents();
          if ($request['save_data'] != 'Y') {
              $this->uninstallDB();
          }
          ModuleManager::unRegisterModule($this->MODULE_ID);
          $APPLICATION->IncludeAdminFile(Loc::getMessage('RADMIR_CARTSHARE_UNINSTALL_TITLE'), $this->GetPath()."/install/unstep2.php");
        }
    }
		
    public function installDB()
    {
        if (Loader::includeModule($this->MODULE_ID)) {
          if (!App::getConnection()->isTableExists(Base::getInstance('\Radmir\Cartshare\SavedCartTable')->getDBTableName())) {
              SavedCartTable::addHL();

          }
        }
    }
		
    public function uninstallDB()
    {
        if (Loader::includeModule($this->MODULE_ID)) {
          if (App::getConnection()->isTableExists(Base::getInstance('\Radmir\Cartshare\SavedCartTable')->getDBTableName())) {
              SavedCartTable::deleteHL();
          }
          Option::delete($this->MODULE_ID);
        }
    }

    public function installFiles() {
      CopyDirFiles($this->GetPath() . "/install/components", App::getDocumentRoot() . "/bitrix/components", true, true);
      return true;
    }

    public function uninstallFiles() {
      $installDirPath = $this->GetPath() . '/install/components/' . $this->MODULE_PARTNER_ID;
      

      $installDir = new Directory($installDirPath);
      $files = $installDir->getChildren();
      $dirToDelete = [];

      //collect directories from this module
      foreach ($files as $obDir) {
        $path = $obDir->getPath();
        $expPath = explode('/', trim($path, '/'));
        $dirToDelete[] = array_pop($expPath);
      }


      $baseDirPath = App::getDocumentRoot() . '/bitrix/components/' . $this->MODULE_PARTNER_ID;

      //delete only this module components
      foreach ($dirToDelete as $currentDir) {
        $dirPathToDelete = $baseDirPath . '/' . $currentDir;
        if (Directory::isDirectoryExists($dirPathToDelete)) {
          Directory::deleteDirectory($dirPathToDelete);
        }
      }

      //f there are no other partner modules - delete partner`s directory
      $baseDir = new Directory($baseDirPath);
      $files = $baseDir->getChildren();
      if (empty($files)) {
        Directory::deleteDirectory($baseDirPath);
      }

      return true;
    }

    public function installEvents() {
      EventManager::getInstance()->registerEventHandler(
        'main',
        'OnBeforeProlog',
        $this->MODULE_ID,
        '\Radmir\Cartshare\Events',
        'checkCartAction'
      );
      return true;
    }

    public function uninstallEvents() {
      EventManager::getInstance()->unRegisterEventHandler(
        'main',
        'OnBeforeProlog',
        $this->MODULE_ID,
        '\Radmir\Cartshare\Events',
        'checkCartAction'
      );
    }
    

    public function GetPath($notDocumentRoot = false){
      if ($notDocumentRoot) {
        return str_ireplace(App::getDocumentRoot(), '', dirname(__DIR__));
      } else {
        return dirname(__DIR__);
      }
    }

    public function isD7() {
      return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
    }

    public function checkDependentsModules() {
      return (Loader::includeModule('sale') && Loader::includeModule('catalog') && Loader::includeModule('highloadblock'));
    }
}