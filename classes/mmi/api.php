<?php defined('SYSPATH') or die('No direct script access.');

abstract class MMI_API extends Kohana_MMI_API
{
    /**
     * Set a custom useragent string.
     *
     * @return  void
     */
    public function __construct()
    {
        parent::__construct();
        $this->_useragent = 'Mozilla/5.0 (compatible; Me Make It; '.URL::base(FALSE, TRUE).')';
    }
}