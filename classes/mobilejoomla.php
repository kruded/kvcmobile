<?php
/**
 * ###DESC###
 * ###URL###
 *
 * @version		###VERSION###
 * @license		###LICENSE###
 * @copyright	###COPYRIGHT###
 * @date		###DATE###
 */
defined('_JEXEC') or die('Restricted access');

class MobileJoomla
{
	var $config = null;
	var $device = null;
	var $_ishomepage = false;
	var $_hidemodules = false;

	function &getConfig()
	{
		static $instance;
		if(!is_array($instance))
		{
			$config = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_mobilejoomla'.DS.'config.php';
			$MobileJoomla_Settings = array ();
			include($config);
			$instance = &$MobileJoomla_Settings;
		}
		return $instance;
	}

	function &getDevice()
	{
		static $instance;
		if(!is_array($instance))
		{
			$instance = array ('markup' => false, 'real_markup' => false, 'screenwidth' => 0, 'screenheight' => 0, 'imageformats' => null, 'mimetype' => null, 'param' => array ());
		}
		return $instance;
	}

	/**
	 * @param string $markup
	 * @return MobileJoomla
	 */
	function &getInstance($markup = '')
	{
		static $instance;
		if(!is_object($instance))
		{
			if($markup == '')
			{
				$nullvar = null;
				return $nullvar;
			}
			$class = 'MobileJoomla_'.strtoupper($markup);
			if(!class_exists($class))
			{
				$path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_mobilejoomla'.DS.'markup'.DS.$markup.'.php';
				require_once($path);
				if(!class_exists($class))
					JError::raiseError(500, 'Class not found: '.$class);
			}
			$instance = new $class;
			$instance->config =& MobileJoomla::getConfig();
			$instance->device =& MobileJoomla::getDevice();
		}
		return $instance;
	}

	function &getToolbar()
	{
		static $instance = null;
		if($instance == null)
		{
			include_once JPATH_ADMINISTRATOR.DS.'components'.DS.'com_mobilejoomla'.DS.'classes'.DS.'mjtoolbar.php';
			$instance = new MJToolbar;
		}
		return $instance;
	}

	function getMarkup()
	{
		return '';
	}

	function isHome()
	{
		return $this->_ishomepage;
	}

	function setHome($ishome)
	{
		$this->_ishomepage = $ishome;
	}

	function getCharset()
	{
		return 'utf-8';
	}

	function getContentType()
	{
		return 'text/html';
	}

	function getContentString()
	{
		return $this->getContentType().'; charset=utf-8';
	}

	function setHeader()
	{
		JResponse::setHeader('Content-Type', $this->getContentString(), true);
	}

	function showXMLheader()
	{
		echo '<?xml version="1.0" encoding="utf-8" ?>'."\n";
	}

	function showDocType()
	{
	}

	function getXmlnsString()
	{
		return '';
	}

	function getPageTitle()
	{
		/** @var JDocument $document */
		$document =& JFactory::getDocument();
		return $document->getTitle();
	}

	function showHead()
	{
		echo '<jdoc:include type="head" />';
	}

	function showComponent()
	{
		echo '<jdoc:include type="component" />';
	}
	
	function showMessage()
	{
		echo '<jdoc:include type="message" />';
	}

	function showFooter()
	{
	}

	function processPage($text)
	{
		return $text;
	}

	function getParam($name, $default = null)
	{
		$MobileJoomla_Settings =& MobileJoomla::getConfig();
		$full_name = $this->getMarkup().'.'.$name;
		$global_name = 'global.'.$name;
		if(!isset($MobileJoomla_Settings[$full_name]))
			return isset($MobileJoomla_Settings[$name]) ? $MobileJoomla_Settings[$name] : $default;
		if($MobileJoomla_Settings[$full_name]=='' && isset($MobileJoomla_Settings[$global_name]))
			return $MobileJoomla_Settings[$global_name];
		return $MobileJoomla_Settings[$full_name];
	}

	function hideModules()
	{
		$this->_hidemodules = true;
	}

	function getPosition($pos)
	{
		if(!isset($this->config)) return '';
		if($this->_hidemodules) return '';
		switch($pos)
		{
			case 'header':
				return $this->getParam('header1');
			case 'middle':
				return $this->getParam('middle1');
			case 'footer':
				return $this->getParam('footer1');
			case 'header2':
			case 'header3':
			case 'middle2':
			case 'middle3':
			case 'footer2':
			case 'footer3':
				return $this->getParam($pos);
		}
		return '';
	}

	function loadModules($position)
	{
		if($this->_hidemodules) return;
		echo '<jdoc:include type="modules" name="'.$position.'" />';
	}

	function loadModulesAsCards($position)
	{
	}

	function RescaleImages($text, $scaletype, $addstyles = false)
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_mobilejoomla'.DS.'classes'.DS.'imagerescaler.php');
		return ImageRescaler::RescaleImages($text, $scaletype, $addstyles);
	}

	function isCurrentMarkup($markup)
	{
		$MobileJoomla_Device =& MobileJoomla::getDevice();
		if($markup=='auto')
			$markup = $MobileJoomla_Device['real_markup'];
		elseif($markup=='desktop')
			$markup = '';
		elseif($markup=='mobile')
			$markup = $MobileJoomla_Device['real_markup']=='' ? 'xhtml' : $MobileJoomla_Device['real_markup'];
		return $markup === $MobileJoomla_Device['markup'];
	}

	function getDeviceViewURI($device)
	{
		$MobileJoomla_Settings =& MobileJoomla::getConfig();
		$MobileJoomla_Device =& MobileJoomla::getDevice();

		$uri = clone(JFactory::getURI());
		if($uri->getVar('format')=='html')
			$uri->delVar('format');
		$uri->delVar('device');

		$desktop_uri = new JURI($MobileJoomla_Settings['desktop_url']);
		$uri->setHost($desktop_uri->getHost());

		if($device=='mobile')
			$device = $MobileJoomla_Device['real_markup']=='' ? 'xhtml' : $MobileJoomla_Device['real_markup'];

		if($device=='auto')
			$device = $MobileJoomla_Device['real_markup']=='' ? 'desktop' : $MobileJoomla_Device['real_markup'];

		switch($device)
		{
		case 'xhtml':
		case 'wml':
		case 'chtml':
		case 'iphone':
			if($MobileJoomla_Settings[$device.'.domain'])
				$uri->setHost($MobileJoomla_Settings[$device.'.domain']);
			break;
		case 'desktop':
			break;
		default:
			$device = false;
		}

		if($device !== false)
			$uri->setVar('device', $device);

		return $uri->toString();
	}

	function getCanonicalURI()
	{
		$MobileJoomla_Device =& MobileJoomla::getDevice();
		if($MobileJoomla_Device['markup'] == $MobileJoomla_Device['default_markup'])
			return false;

		$MobileJoomla_Settings =& MobileJoomla::getConfig();
		$desktop_uri = new JURI($MobileJoomla_Settings['desktop_url']);

		$uri = clone(JFactory::getURI());
		$uri->delVar('device');
		$uri->delVar('format');
		$uri->setHost($desktop_uri->getHost());

		return $uri->toString();
	}

	function getAccessKey()
	{
		static $last_keynum = 0;
		if($last_keynum>=10)
			return false;
		$last_keynum++;
		return $last_keynum==10 ? '0' : $last_keynum;
	}
}
