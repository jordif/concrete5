<?

defined('C5_EXECUTE') or die(_("Access Denied."));
class DashboardSettingsController extends Controller {

	var $helpers = array('form');
	
	public function view($updated = false) {
		$u = new User();
		 
		$this->set('site_tracking_code', Config::get('SITE_TRACKING_CODE') );		
		$this->set('url_rewriting', URL_REWRITING);
		$this->set('marketplace_enabled_in_config', Config::get('ENABLE_MARKETPLACE_SUPPORT') );		
		$this->set('site', SITE);
		$this->set('ui_breadcrumb', $u->config('UI_BREADCRUMB'));
		
		if ($updated) {
			switch($updated) {
				case "tracking_code_saved";
					$this->set('message', t('Your tracking code has been saved.'));	
					break;			
				/*
				//moved to set_permissions
				case "maintenance_enabled";
					$this->set('message', t('Maintenance Mode turned on. Your site is now private.'));	
					break;
				case "maintenance_disabled":
					$this->set('message', t('Maintenance Mode turned off. Your site is public.'));	
					break;
				*/
				case "marketplace_turned_on";
					$this->set('message', t('Marketplace support is now enabled.'));	
					break;
				case "maintenance_turned_off":
					$this->set('message', t('Maintenance support is now disabled.'));	
					break;				
				case "favicon_saved":
					$this->set('message', t('Bookmark icon saved.'));	
					break;				
				case "editing_preferences_saved":
					$this->set('message', t('Editing preferences saved.'));	
					break;
				case "sitename_saved":
					$this->set('message', t("Your site's name has been saved."));	
					break;
				case "debug_saved":
					$this->set('message', t('Debug configuration saved.'));
					break;
				case "rewriting_saved":
					if (URL_REWRITING) {
						$this->set('message', t('URL rewriting enabled. Make sure you copy the lines below these URL Rewriting settings area and place them in your .htaccess or web server configuration file.'));
					} else {
						$this->set('message', t('URL rewriting disabled.'));
					}
					break;		
			}
		}
	}

	public function update_maintenance() { 
		$this->set_permissions();
		if ($this->token->validate("update_maintenance")) {
			if ($this->isPost()) {
				Config::save('SITE_MAINTENANCE_MODE', $this->post('site_maintenance_mode'));
				if ($this->post('site_maintenance_mode') == 1) { 
					$this->redirect('/dashboard/settings','set_permissions',"maintenance_enabled");
				} else {
					$this->redirect('/dashboard/settings','set_permissions',"maintenance_disabled");
				}
			}
		} else {
			$this->set('error', array($this->token->getErrorMessage()));
		}
	}
	
	public function update_tracking_code() {
		if ($this->token->validate("update_tracking_code")) {
			if ($this->isPost()) {
				Config::save('SITE_TRACKING_CODE', $this->post('tracking_code'));
				$this->redirect('/dashboard/settings','tracking_code_saved'); 			
			}
		} else {
			$this->set('error', array($this->token->getErrorMessage()));
		}
	}	

	public function update_sitename() {
		if ($this->token->validate("update_sitename")) {
			if ($this->isPost()) {
				Config::save('SITE', $this->post('SITE'));
				$this->redirect('/dashboard/settings','sitename_saved');
			}
		} else {
			$this->set('error', array($this->token->getErrorMessage()));
		}
	}

	public function update_user_settings() {
		if ($this->token->validate("update_user_settings")) {
			if ($this->isPost()) {
				$u = new User();
				$u->saveConfig('UI_BREADCRUMB', $this->post('ui_breadcrumb'));
				$this->redirect('/dashboard/settings','editing_preferences_saved');
			}
		} else {
			$this->set('error', array($this->token->getErrorMessage()));
		}
	}

	public function update_debug() {
		if ($this->token->validate("update_debug")) {
			if ($this->isPost()) {
				Config::save('SITE_DEBUG_LEVEL', $this->post('debug_level'));
				$this->redirect('/dashboard/settings','set_developer','debug_saved');
			}
		} else {
			$this->set('error', array($this->token->getErrorMessage()));
		}
	}

	public function update_logging() {
		if ($this->token->validate("update_logging")) {
			if ($this->isPost()) {
				$eldq = $this->post('ENABLE_LOG_DATABASE_QUERIES') == 1 ? 1 : 0;
				$elem = $this->post('ENABLE_LOG_EMAILS') == 1 ? 1 : 0;
				$eler = $this->post('ENABLE_LOG_ERRORS') == 1 ? 1 : 0;
				
				Config::save('ENABLE_LOG_DATABASE_QUERIES', $eldq);
				Config::save('ENABLE_LOG_EMAILS', $elem);
				Config::save('ENABLE_LOG_ERRORS', $eler);
				$this->redirect('/dashboard/settings','set_developer','logging_saved');
			}
		} else {
			$this->set('error', array($this->token->getErrorMessage()));
		}
	}

	public function update_rewriting() {
		if ($this->isPost()) {
			Config::save('URL_REWRITING', $this->post('URL_REWRITING'));
			$this->redirect('/dashboard/settings','rewriting_saved');
		}
	}
	
	public function set_developer($updated = false) {
		$debug_level = Config::get('SITE_DEBUG_LEVEL');
		$enable_log_emails = Config::get('ENABLE_LOG_EMAILS');
		$enable_log_database_queries = Config::get('ENABLE_LOG_DATABASE_QUERIES');
		$enable_log_errors = Config::get('ENABLE_LOG_ERRORS');
		if ($debug_level < 1) {
			$debug_level = DEBUG_DISPLAY_PRODUCTION;
		}
		$this->set('debug_level', $debug_level);		
		$this->set('enable_log_database_queries', $enable_log_database_queries);		
		$this->set('enable_log_emails', $enable_log_emails);		
		$this->set('enable_log_errors', $enable_log_errors);		
		if ($updated) {
			switch($updated) {
				case "debug_saved":
					$this->set('message', t('Debug configuration saved.'));
					break;
				case "logging_saved":
					$this->set('message', t('Logging configuration saved.'));	
					break;
			}
		}
	}
	
	
	public function on_start() {
		$prefsSelected = false;
		$globalSelected = false;
		$permsSelected = false;
		$this->token = Loader::helper('validation/token');
		
		switch($this->getTask()) {
			case "set_developer":
				$devSelected = true;
				break;
			case "set_permissions":
				$permsSelected = true;
				break;
			default:
				$globalSelected = true;
				break;
		}					
		$subnav = array(
			array(View::url('/dashboard/settings'), t('General'), $globalSelected),
			array(View::url('/dashboard/settings', 'set_permissions'), t('Access'), $permsSelected),
			array(View::url('/dashboard/settings', 'set_developer'), t('Debug'), $devSelected)
		);
		$this->set('subnav', $subnav);
	}
	
	protected function set_permissions($saved = false) {
	
		//maintanence mode
		$site_maintenance_mode = Config::get('SITE_MAINTENANCE_MODE');
		if ($site_maintenance_mode < 1) {
			$site_maintenance_mode = 0;
		}
		$this->set('site_maintenance_mode', $site_maintenance_mode);	
	
		if ($saved) {
			switch($saved) { 
				case "maintenance_enabled";
					$this->set('message', t('Maintenance Mode turned on. Your site is now private.'));	
					break;
				case "maintenance_disabled":
					$this->set('message', t('Maintenance Mode turned off. Your site is public.'));	
					break;	
				//permissions saved	
				default: 
					$this->set('message', t('Permissions saved.'));	
			}
		}	
	
		if (PERMISSIONS_MODEL != 'simple') {
			return;
		}
		
		$home = Page::getByID(1, "RECENT");
		$gl = new GroupList($home, false, true);
		$gArrayTmp = $gl->getGroupList();
		$gArray = array();
		foreach($gArrayTmp as $gi) {
			if ($gi->getGroupID() == GUEST_GROUP_ID) {
				$ggu = $gi;
				if ($ggu->canRead()) {
					$this->set('guestCanRead', true);
				}
			} else if ($gi->getGroupID() == REGISTERED_GROUP_ID) {
				$gru = $gi;
				if ($gru->canRead()) {
					$this->set('registeredCanRead', true);
				}
			} else {
				$gArray[] = $gi;
			}
		}
		
		$this->set('ggu', $ggu);
		$this->set('gru', $gru);
		$this->set('gArray', $gArray);
		$this->set('home', $home);
	}
	
	public function update_permissions() {
		$this->set_permissions();
		$home = $this->get('home');
		$gru = Group::getByID(REGISTERED_GROUP_ID);
		$ggu = Group::getByID(GUEST_GROUP_ID);
		$gau = Group::getByID(ADMIN_GROUP_ID);
		
		$args = array();
		switch($_POST['view']) {
			case "ANYONE":
				$args['collectionRead'][] = 'gID:' . $ggu->getGroupID(); // this API is pretty crappy. TODO: clean this up in a nice object oriented fashion
				break;
			case "USERS":
				$args['collectionRead'][] = 'gID:' . $gru->getGroupID(); // this API is pretty crappy. TODO: clean this up in a nice object oriented fashion
				break;
			case "PRIVATE":
				$args['collectionRead'][] = 'gID:' . $gau->getGroupID();
				break;
			
		}
		
		$args['collectionWrite'] = array();
		if (is_array($_POST['gID'])) {
			foreach($_POST['gID'] as $gID) {
				$args['collectionWrite'][] = 'gID:' . $gID;
				$args['collectionAdmin'][] = 'gID:' . $gID;
				$args['collectionDelete'][] = 'gID:' . $gID;
			}
		}
		
		$args['cInheritPermissionsFrom'] = 'OVERRIDE';
		$args['cOverrideTemplatePermissions'] = 1;
		
		$home->updatePermissions($args);
		
		$this->redirect('/dashboard/settings/', 'set_permissions', 'permissions_saved');
	}
	
	function update_favicon(){
		Loader::block('library_file');
		
		if ($this->token->validate("update_favicon")) { 
		
			if ( isset($_FILES['favicon_file']) ) {
				$fh = Loader::helper('file');
				if(!$fh->hasAllowedExtension($_FILES['favicon_file']['name'])){	
					$msg = t('Invalid file extension.');
				}else{ 
					$bt = BlockType::getByHandle('library_file');
					$data = array();
					$data['file'] = $_FILES['favicon_file']['tmp_name'];
					$data['name'] = $_FILES['favicon_file']['name'];
					$nb = $bt->add($data);
					$fileBlock=LibraryFileBlockController::getFile( $nb->getBlockID() );
					$fileID=$fileBlock->getFileID(); 
					Config::save('FAVICON_FID', $fileID);
					$this->redirect('/dashboard/settings/', 'favicon_saved');
				}				
			}else{
				$msg = t('An error occured while uploading your file');
			}
			
		}else{
			$this->set('error', array($this->token->getErrorMessage()));
		}
	}
	
	function update_marketplace_support(){ 		
		if ($this->token->validate("update_marketplace_support")) { 
 			Config::save('ENABLE_MARKETPLACE_SUPPORT', intval($this->post('MARKETPLACE_ENABLED')) );
 			if($this->post('MARKETPLACE_ENABLED'))
 				 $this->redirect('/dashboard/settings/', 'marketplace_turned_on');
			else $this->redirect('/dashboard/settings/', 'marketplace_turned_off');	
		}else{
			$this->set('error', array($this->token->getErrorMessage()));
		}
	}	
}