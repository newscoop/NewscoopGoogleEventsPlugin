<?php

/**
 * @package Newscoop\GoogleEventsPluginBundle
 * @author Mark Lewis <mark.kewis@sourcefabric.org>
 */

namespace Newscoop\GoogleEventsPluginBundle\TemplateList;

use Newscoop\Criteria;

/**
 * Available criteria for GoogleEvent
 */
class GoogleEventCriteria extends Criteria
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $location;

    /**
     * @var string
     */
    public $summary;

    /**
     * @var string
     */
    public $query;

    /**
     * @var array
     */
    public $orderBy = array('createdAt' => 'desc');

}
