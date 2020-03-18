<?php
defined('_JEXEC') or die('Restricted access');
ini_set('display_errors', 'On');

class plgContentImageresizer extends JPlugin
{
	protected $_app;
	protected $_contexts;
	protected $_types;
	protected $_maxfilesize;
	protected $autoloadLanguage = true;

	public function __construct(&$subject, $config)
	{
		if($config['params'] instanceof JRegistry)
		{
			$this->params = $config['params'];
		}
		else
		{
			$this->params = new JRegistry;
			$this->params->loadString($config['params']);
		}

		$this->loadLanguage();
		$types              = (array) $this->params->get('types');
		$this->_app         = JFactory::getApplication();
		$this->_types       = $this->getContentTypes($types);
		$this->_maxfilesize = (int) $this->params->get('max_filesize', 1024);

		parent::__construct($subject, $config);
	}

	public function __destruct() { }

	public function onContentPrepareData($context, $data)
	{
		/* $this->_app->enqueueMessage($context, 'info');
		$this->_app->enqueueMessage($this->_maxfilesize, 'info');
		$this->_app->enqueueMessage(implode(' || ', $this->_types), 'info'); */

		return true;
	}

	/**
	 * This is an event that is called after the content is saved into the database. Even though article object is passed by reference, changes will not be saved since storing data into database phase is past. An example use case would be redirecting user to the appropriate place after saving.
	 *
	 * @param string  $context  The context of the content being passed to the plugin - this is the component name and view - or name of module (e.g. com_content.article). Use this to check whether you are in the desired context for the plugin.
	 * @param object  $article  A reference to the JTableContent object that is being saved which holds the article data.
	 * @param bool    $isNew    A boolean which is set to true if the content is about to be created.
	 *
	 * @todo $category_images and $article_images will need to be converted to an array or object before trying to get the image inside the set
	 */
	public function onContentAfterSave($context = null, $article = null, $isNew = 0)
	{
		$uri             = JUri::getInstance();
		$website         = $uri->getScheme() . '://' . $uri->getHost();
		$image_fields    = array('photo', 'image');
		$category_images = isset($article->params) ? $article->params : null;
		$article_images  = isset($article->images) ? $article->images : null;

		if((in_array($context, $this->_types) !== false) && $this->_app->isClient('administrator'))
		{
			// stuff

			$split_context = explode('.', $context);

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Builds a list of selected content types to compare against the current context
	 */
	protected function getContentTypes(array $types)
	{
		$db         = JFactory::getDbo();
		$query      = $db->getQuery(true);
		$typestring = implode(',', $types);
		$contexts   = array();

		$query->select('type_alias')->from($db->qn('#__content_types'))->where('type_id IN(' . $db->escape($typestring) . ')');

		try
		{
			$db->setQuery($query);

			$result = $db->loadRowList();

			array_walk_recursive($result, function($v) use (&$contexts) { $contexts[] = $v; });
		}
		catch(\Exception $e)
		{
			$this->_app->enqueueMessage($e->getCode() . ': ' . $e->getMessage(), 'error');
		}

		return $contexts;
	}
}
