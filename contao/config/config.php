<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2017 Leo Feyer
 *
 *
 * PHP version 5
 * @copyright  Martin Kozianka 2011-2017
 * @author     Martin Kozianka <http://kozianka.de/>
 * @package    timetags
 * @license    LGPL 
 * @filesource
 */
 
$GLOBALS['TL_HOOKS']['replaceInsertTags'][]       = array('Timetags', 'replaceTags');
