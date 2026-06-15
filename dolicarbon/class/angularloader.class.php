<?php

class DoliCarbonAngularLoader
{
	private $moduleName = 'dolicarbon';
	private $moduleWebPath = '/custom/dolicarbon/app/browser';
	private $hideTopMenu = true;
	private $hideLeftMenu = true;
	private $styles = array();
	private $modulePreloads = array();
	private $scripts = array(
		'polyfills' => '',
		'main' => '',
	);

	public function __construct($hideTopMenu = true, $hideLeftMenu = true)
	{
		$this->hideTopMenu = (bool) $hideTopMenu;
		$this->hideLeftMenu = (bool) $hideLeftMenu;
		global $conf;
		if ($this->hideTopMenu) {
			$conf->dol_hide_topmenu = 1;
		}
		if ($this->hideLeftMenu) {
			$conf->dol_hide_leftmenu = 1;
		}
		$this->loadAssets();
	}

	private function loadAssets()
	{
		$indexPath = DOL_DOCUMENT_ROOT.'/custom/'.$this->moduleName.'/app/browser/index.html';
		if (!file_exists($indexPath)) {
			return;
		}
		$html = file_get_contents($indexPath);
		if (!is_string($html) || $html === '') {
			return;
		}

		if (preg_match_all('/<link[^>]*href="([^"]*\.css)"/i', $html, $m)) {
			foreach ($m[1] as $href) {
				if (strpos($href, '/') === 0) {
					$this->styles[] = DOL_MAIN_URL_ROOT.$href;
				}
			}
		}

		if (preg_match_all('/<link rel="modulepreload" href="([^"]*)"/i', $html, $m)) {
			foreach ($m[1] as $href) {
				if (strpos($href, '/') === 0) {
					$this->modulePreloads[] = DOL_MAIN_URL_ROOT.$href;
				}
			}
		}

		if (preg_match_all('/<script[^>]*src="([^"]*)"[^>]*>/i', $html, $m)) {
			foreach ($m[1] as $src) {
				if (strpos($src, '/') !== 0) {
					continue;
				}
				if (strpos($src, 'polyfills') !== false) {
					$this->scripts['polyfills'] = DOL_MAIN_URL_ROOT.$src;
				} elseif (strpos($src, 'main') !== false) {
					$this->scripts['main'] = DOL_MAIN_URL_ROOT.$src;
				}
			}
		}
	}

	public function getStyles()
	{
		return $this->styles;
	}

	public function generateHtml()
	{
		global $conf, $user;

		if (empty($this->scripts['main'])) {
			return '<div class="warning">DoliCarbon Angular build not found. Please run ng build in DoliCarbonAppNg.</div>';
		}

		$html = '';
		// Ensure Material icon font is always available in Dolibarr shell.
		$html .= '<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">';
		$html .= '<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">';
		$html .= '<base href="'.DOL_MAIN_URL_ROOT.'/custom/'.$this->moduleName.'/">';
		$cfg = array(
			'apiUrl' => DOL_MAIN_URL_ROOT,
			'dolMainUrlRoot' => DOL_MAIN_URL_ROOT,
			'entity' => (int) $conf->entity,
			'user' => array('id' => (int) $user->id, 'admin' => !empty($user->admin) ? 1 : 0),
			'rights' => array(
				'validate' => $user->hasRight('dolicarbon', 'validate') ? 1 : 0,
			),
			'communicationDisclaimer' => 'DoliCarbon est un outil d’aide à la comptabilité carbone aligné méthodologiquement sur les bonnes pratiques Bilan Carbone. Il ne constitue pas une reconnaissance « Bilan Carbone Conform » par l’ABC sans audit externe réussi. Toute communication publique doit respecter ces limites.',
		);
		$html .= '<script nonce="'.getNonce().'">window.dolicarbonconfig = '.json_encode($cfg).';</script>';
		$html .= '<div id="angular-app"><app-root></app-root></div>';

		foreach ($this->modulePreloads as $preload) {
			$html .= '<link rel="modulepreload" href="'.$preload.'">';
		}
		if (!empty($this->scripts['polyfills'])) {
			$html .= '<script nonce="'.getNonce().'" type="module" src="'.$this->scripts['polyfills'].'"></script>';
		}
		$html .= '<script nonce="'.getNonce().'" type="module" src="'.$this->scripts['main'].'"></script>';
		return $html;
	}
}

