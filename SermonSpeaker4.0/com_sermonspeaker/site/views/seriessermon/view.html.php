<?php
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the SermonSpeaker Component
 */
class SermonspeakerViewSeriessermon extends JView
{
	public function __construct($config = array()){

		parent::__construct($config);
	}

	function display($tpl = null)
	{
		// Applying CSS file
		JHTML::stylesheet('sermonspeaker.css', 'media/com_sermonspeaker/css/');

		$app		= JFactory::getApplication();
		$params		= $app->getParams();

		$columns = $params->get('col');
		if (!$columns){
			$columns = array();
		}
		$col_serie = $params->get('col_serie');
		if (!$col_serie){
			$col_serie = array();
		}

		// check if access is not public
		$user = JFactory::getUser();
		$groups	= $user->getAuthorisedViewLevels();
		
		if (!in_array($params->get('access'), $groups)) {
			JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		// Get some data from the models
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Get the category name(s)
		if($state->get('series_category.id')){
			$cat	= $this->get('Cat');
		} else {
			$cat 	= '';
		}

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Set layout from parameters if not already set elsewhere
		if ($this->getLayout() == 'default') {
			$this->setLayout($params->get('seriessermonlayout', 'normal'));
		}

		// push data into the template
		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('params',		$params);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('serie',		$serie);
		$this->assignRef('cat',			$cat);
		$this->assignRef('columns', 	$columns);
		$this->assignRef('col_serie', 	$col_serie);

		$this->_prepareDocument();

		parent::display($tpl);
	}	

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app	= JFactory::getApplication();

		if (in_array('seriessermon:player', $this->columns)){
			require_once(JPATH_COMPONENT.DS.'helpers'.DS.'player.php');
		}
		
		// Set Page Header if not already set in the menu entry
		$menus	= $app->getMenu();
		$menu 	= $menus->getActive();
		if ($menu){
			$this->params->def('page_heading', $menu->title);
		} else {
			$this->params->def('page_heading', JText::_('COM_SERMONSPEAKER_SERIESSERMONS_TITLE'));
		}

		// Set Pagetitle
		if (!$menu) {
			$title = JText::_('COM_SERMONSPEAKER_SERIESSERMONS_TITLE');
		} else {
			$title = $this->params->get('page_title', '');
		}
		if ($app->getCfg('sitename_pagetitles', 0)) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		$this->document->setTitle($title);

		// Set MetaData from menu entry if available
		if ($this->params->get('menu-meta_description')){
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}
		if ($this->params->get('menu-meta_keywords')){
			$this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
		}
	}
}