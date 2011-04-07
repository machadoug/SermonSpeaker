<?php
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Frontendupload model.
 *
 * @package		Sermonspeaker.Administrator
 */
class SermonspeakerModelFrontendupload extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 */
	protected $text_prefix = 'COM_SERMONSPEAKER';

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	A record object.
	 * @return	boolean	True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canDelete($record)
	{
		return false;
	}
	
	/**
	 * Method to test whether a records state can be changed.
	 *
	 * @param	object	A record object.
	 * @return	boolean	True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check against the category.
		if (!empty($record->catid)) {
			return $user->authorise('core.edit.state', 'com_sermonspeaker.category.'.(int) $record->catid);
		}
		// Default to component settings if neither article nor category known.
		else {
			return parent::canEditState($record);
		}
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Sermon', $prefix = 'SermonspeakerTable', $config = array())
	{
//		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sermonspeaker'.DS.'tables');
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		JForm::addFormPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sermonspeaker'.DS.'models'.DS.'forms');
		JForm::addFieldPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sermonspeaker'.DS.'models'.DS.'fields');

		// Get the form.
		$form = $this->loadForm('com_sermonspeaker.sermon', 'sermon', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		// Determine correct permissions to check.
		if ((int)$this->getState('sermon.id')) {
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit');
			// Existing record. Can only edit own articles in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit.own');
		} else {
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.create');
		}

		// Modify the form based on Edit State access controls.
		if (!$this->canEditState((object) $data)) {
			// Disable fields for display.
			$form->setFieldAttribute('podcast', 'disabled', 'true');
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an article you can edit.
			$form->setFieldAttribute('podcast', 'filter', 'unset');
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_sermonspeaker.edit.sermon.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		// Reading ID3 Tags if the Lookup Button was pressed or a file was uploaded, priority on audiofile if both are present
		$data->audiofile = JRequest::getString('file0');
		$data->videofile = JRequest::getString('file1');
		if ($data->audiofile || $data->videofile){
			if ($data->audiofile && (JRequest::getCmd('type') != 'video')){
				$id3_file = $data->audiofile;
			} else {
				$id3_file = $data->videofile;
			}
			require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'id3.php';
			$params	= &JComponentHelper::getParams('com_sermonspeaker');

			$id3 = SermonspeakerHelperId3::getID3($id3_file, $params);
			foreach ($id3 as $key => $value){
				if ($value){
					$data->$key = $value;
				}
			}
			if (!$data->sermon_date){
				$data->sermon_date = JHTML::Date('', 'Y-m-d H:m:s', true);
			}
		}
		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		return $item;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable(&$table)
	{
		jimport('joomla.filter.output');

		$table->sermon_title	= htmlspecialchars_decode($table->sermon_title, ENT_QUOTES);
		$table->alias			= JApplication::stringURLSafe($table->alias);
		if (empty($table->alias)) {
			$table->alias = JApplication::stringURLSafe($table->sermon_title);
			if (empty($table->alias)) {
				$table->alias = JFactory::getDate()->format("Y-m-d-H-i-s");
			}
		}
		$params	= &JComponentHelper::getParams('com_sermonspeaker');
		if(Jrequest::getInt('sel1')){
			$table->audiofile = '/'.$params->get('path').'/'.$table->audiofile;
		}
		if(Jrequest::getInt('sel2')){
			$table->videofile = '/'.$params->get('path').'/'.$table->videofile;
		}
		if(Jrequest::getInt('sel3')){
			$table->addfile = '/'.$params->get('path_addfile').'/'.$table->addfile;
		}
		$time_arr = explode(':', $table->sermon_time);
		foreach ($time_arr as $time_int){
			$time_int = (int)$time_int;
			$time_int = str_pad($time_int, 2, '0', STR_PAD_LEFT);
		}
		if (count($time_arr) == 2) {
			$table->sermon_time = '00:'.$time_arr[0].':'.$time_arr[1];
		} elseif (count($time_arr) == 3) {
			$table->sermon_time = $time_arr[0].':'.$time_arr[1].':'.$time_arr[2];
		}
		if (!empty($table->metakey)) {
			// only process if not empty
			$bad_characters = array("\n", "\r", "\"", "<", ">"); // array of characters to remove
			$after_clean = JString::str_ireplace($bad_characters, "", $table->metakey); // remove bad characters
			$keys = explode(',', $after_clean); // create array using commas as delimiter
			$clean_keys = array();
			foreach($keys as $key) {
				if (trim($key)) {  // ignore blank keywords
					$clean_keys[] = trim($key);
				}
			}
			$table->metakey = implode(", ", $clean_keys); // put array back together delimited by ", "
		}

		// Reorder the articles within the category so the new article is first
		if (empty($table->id)) {
			$table->reorder('catid = '.(int) $table->catid.' AND state >= 0');
		}
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param	object	A record object.
	 * @return	array	An array of conditions to add to add to ordering queries.
	 * @since	1.6
	 */
	protected function getReorderConditions($table = null)
	{
		$condition = array();
		$condition[] = 'catid = '.(int) $table->catid;
		return $condition;
	}
}