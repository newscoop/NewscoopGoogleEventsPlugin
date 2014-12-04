<?php

/**
 * @package Newscoop\GoogleEventsPluginBundle
 * @author Mark Lewis <mark.lewis@sourcefabric.org>
 */

namespace Newscoop\GoogleEventsPluginBundle\Services;

use Doctrine\ORM\EntityManager;
use Newscoop\GoogleEventsPluginBundle\Entity\GoogleEvent;
use Symfony\Component\DependencyInjection\Container;

/**
 * GoogleEvents Service
 */
class GoogleEventsService
{
    /** @var Container */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * get all events 
     *
     * @return array 
     */
    public function getAllGoogleEvents()
    {
        $em = $this->container->get('em');
        $events = $em->getRepository('Newscoop\GoogleEventsPluginBundle\Entity\GoogleEvent')
            ->createQueryBuilder('e')
            ->where('e.isActive = 1')
            ->addOrderBy('e.start', 'ASC')
            ->getQuery()
            ->getResult();

        return $events;
    }

    /**
     * Delete event by given id
     *
     * @param int|string $id GoogleEvent id
     *
     * @return boolean
     */
    public function deleteGoogleEvent($id)
    {
        $em = $this->container->get('em');
        $event = $this->getRepository()
            ->findOneById($id);

        if ($event) {
            $em->remove($event);
            $em->flush();

            return true;
        }

        return false;
    }

    /**
     * Delete events with end times in the past
     *
     * @return boolean
     */
    public function deleteOldEvents()
    {   
        $deleted = false;
        $deleted = $this->getRepository()
            ->deleteOldEvents();
        return $deleted;
    }

    /**
     * Activate event by given id
     *
     * @param GoogleEvent $event GoogleEvent
     *
     * @return boolean
     */
    public function activateGoogleEvent(GoogleEvent $event)
    {
        $em = $this->container->get('em');
        $event->setIsActive(true);
        $em->flush();

        return true;
    }

    /**
     * Deactivate event by given id
     *
     * @param GoogleEvent $event GoogleEvent
     *
     * @return boolean
     */
    public function deactivateGoogleEvent(GoogleEvent $event)
    {
        $em = $this->container->get('em');
        $event->setIsActive(false);
        $em->flush();

        return true;
    }

    /**
     * Tests if a event already exists by given id
     *
     * @param string $id
     *
     * @return bool
     */
    public function exists($id)
    {
        $em = $this->container->get('em');
        $event = $this->getRepository()
            ->findOneById($id);

        if ($event) {
          return true;
        }

        return false;
    }

    /**
     * Takes JSON response from the google_events api and saves an Entity\GoogleEvent
     *
     * @param text $event
     *
     * @return Entity\GoogleEvent
     */
    public function saveGoogleEvent($event)
    {
        $em = $this->container->get('em');

        try {
            //TODO: extract relational data from json
            $googleEvent = new GoogleEvent();
            $googleEvent->setId($event['id'])
                ->setKind($event['kind'])
                ->setEtag($event['etag'])
                ->setStatus($event['status'])
                ->setHtmlLink($event['htmlLink'])
                ->setSummary($event['summary'])
                ->setDescription($event['description'])
                ->setLocation($event['location'])
                ->setCreatorEmail($event['creator']['email'])
                ->setCreatorDisplayName($event['creator']['displayName'])
                ->setCreatedAt(new \DateTime(date('Y-m-d H:i:s', strtotime($event['created']))))
                ->setUpdatedAt(new \DateTime(date('Y-m-d H:i:s', strtotime($event['updated']))))
                ->setStart(new \DateTime(date('Y-m-d H:i:s', strtotime($event['sanitizedStart']))))
                ->setEnd(new \DateTime(date('Y-m-d H:i:s', strtotime($event['sanitizedEnd']))))
                ->setJson(json_encode($event));
            $em->persist($googleEvent);
            $em->flush();
        } catch (\Exception $e) {
            print('Error: ' . $e->getMessage() . "\n");
        }

        return $googleEvent;
    }

    /**
     * Get event by given id
     *
     * @param int|string $id GoogleEvent id
     *
     * @return GoogleEvent
     */
    public function getGoogleEventById($id)
    {
        $em = $this->container->get('em');
        $event = $this->getRepository()
            ->findOneById($id);

        if ($event) {
            return $event;
        }
        
        return false;
    }

    /**
     * Count phtos by given criteria
     *
     * @param array $criteria
     * @return int
     */
    public function countBy(array $criteria = array())
    {   
        return $this->getRepository()->countBy($criteria);
    }


    /**
     * Get repository for announcments entity
     *
     * @return Newscoop\GoogleEventsPluginBundle\Repository
     */
    private function getRepository()
    {
        $em = $this->container->get('em');

        return $em->getRepository('Newscoop\GoogleEventsPluginBundle\Entity\GoogleEvent');

    }

    /**
     * Makes GogleCalendar api all to fetch calendar events
     *
     * @param string $calendarId
     * @param string $start (expected format Y-m-d\TH:i:s\Z)
     * @param string $end (expected format Y-m-d\TH:i:s\Z)
     * @return int eventsAdded
     */
    public function ingestEvents($calendarId, $start, $end)
    {
        $preferencesService = $this->container->get('system_preferences_service');
        $baseUrl = $preferencesService->GoogleEventsBaseUrl;
        $apiKey = $preferencesService->GoogleEventsApiKey;
        if (empty($apiKey)) {
            error_log("Missing apikey. Please configure your google apikey in the plugin admin.");
            return false;
        }
        if (empty($start)) {
            $start = date('Y-m-d\T00:00:00\Z', strtotime("-1 month"));
        }
        $url = $baseUrl . "calendars/" . urlencode($calendarId) . "/events?key=" . $apiKey;
        $url .= "&timeMin=" . urlencode($start);
        if ($end) {
            $url .= "&timeMax=" . urlencode($end);
        }
        try {
            $em = $this->container->get('em');
            $client = new \Buzz\Client\Curl();
            $client->setTimeout(3600);
            $browser = new \Buzz\Browser($client);
            $response =  $browser->get($url);

            $results = json_decode($response->getContent(), true);
            $events = $results['items']; 
            $eventsAdded = 0;

            foreach ($events as $event) {
                // check if we already have this event
                $now = 0;
                $updated = 0; 
                $existingEvent = $this->getGoogleEventById($event['id']);
                if ($existingEvent) {
                    $imported = $existingEvent->getImportedAt()->format('U'); 
                    $updated = strtotime($event['updated']); 
                    if ($updated > $imported) {
                        $this->deleteGoogleEvent($existingEvent->getId());
                    }
                }
                if (!$this->exists($event['id'])) {
                    $event['sanitizedStart'] = ($event['start']['dateTime']) ? $event['start']['dateTime'] : $event['start']['date'];
                    $event['sanitizedEnd'] = ($event['end']['dateTime']) ? $event['end']['dateTime'] : $event['end']['date'];
                    $googleEvent = $this->saveGoogleEvent($event);
                    $eventsAdded++;
                }
            }
            return $eventsAdded;
        } catch (\Exception $e) {
            error_log('ERROR: '.$e->getMessage());
            return false;
        }
    }
}

