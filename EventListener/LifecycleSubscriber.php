<?php
/**
 * @package Newscoop\GoogleEventsPluginBundle
 * @author Mark Lewis <mark.lewis@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\GoogleEventsPluginBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Newscoop\EventDispatcher\Events\GenericEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Event lifecycle management
 */
class LifecycleSubscriber implements EventSubscriberInterface
{
    private $container;

    protected $em;
    
    protected $scheduler;

    protected $cronjobs;

    protected $preferences;

    public function __construct(ContainerInterface $container)
    {
        $appDirectory = realpath(__DIR__.'/../../../../application/console');
        $this->container = $container;
        $this->em = $this->container->get('em');
        $this->scheduler = $this->container->get('newscoop.scheduler');
        $this->preferences = $this->container->get('system_preferences_service');
        $this->cronjobs = array(
            "GoogleEvents plugin ingest events cron job" => array(
                'command' => $appDirectory . ' google_events_events:ingest',
                'schedule' => '*/15 * * * *',
            )
        );
    }

    public function install(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->updateSchema($this->getClasses(), true);

        $this->preferences->set('GoogleEventsBaseUrl', 'https://www.googleapis.com/calendar/v3/');

        // Generate proxies for entities
        $this->em->getProxyFactory()->generateProxyClasses($this->getClasses(), __DIR__ . '/../../../../library/Proxy');
        $this->addJobs();
    }

    public function update(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->updateSchema($this->getClasses(), true);

        // Generate proxies for entities
        $this->em->getProxyFactory()->generateProxyClasses($this->getClasses(), __DIR__ . '/../../../../library/Proxy');
    }

    public function remove(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->dropSchema($this->getClasses(), true);
        $this->removeJobs();
    }

    public static function getSubscribedEvents()
    {
        return array(
            'plugin.install.newscoop_google_events_plugin_bundle' => array('install', 1),
            'plugin.update.newscoop_google_events_plugin_bundle' => array('update', 1),
            'plugin.remove.newscoop_google_events_plugin_bundle' => array('remove', 1),
        );
    }

    /**
     * Add plugin cron jobs
     */
    private function addJobs()
    {
        foreach ($this->cronjobs as $jobName => $jobConfig) {
            $this->scheduler->registerJob($jobName, $jobConfig);
        }
    }

    /**
     * Remove plugin cron jobs
     */
    private function removeJobs()
    {
        foreach ($this->cronjobs as $jobName => $jobConfig) {
            $this->scheduler->removeJob($jobName, $jobConfig);
        }
    }

    private function getClasses()
    {
        return array(
            $this->em->getClassMetadata('Newscoop\GoogleEventsPluginBundle\Entity\GoogleEvent'),
        );
    }
}
