<?php
/**
 * @package Newscoop\TagesWocheExtraBundle
 * @author Mark Lewis <mark.lewis@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\GoogleEventsPluginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Newscoop\GoogleEventsPluginBundle\TemplateList\GoogleEventCriteria;

class AdminController extends Controller
{
    /**
     * @Route("/admin/google-events")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->container->get('em');
        $preferencesService = $this->container->get('system_preferences_service');
        $currentIngestJob = $this->getCurrentJob();
        $scheduleParts = array();
        preg_match('/^\*\/([0-9]+) .+/', $currentIngestJob->getSchedule(), $scheduleParts);
        $mins = $scheduleParts[1];
        list($console, $command) = split(" ", $currentIngestJob->getCommand());
        $deleteOld = $preferencesService->GoogleEventsDeleteOld;
        $calendarList = $preferencesService->GoogleEventsCalendarList;
        $apikey = $preferencesService->GoogleEventsApiKey;
        $start = $preferencesService->GoogleEventsStart;
        $end = $preferencesService->GoogleEventsEnd;

        // settings update
        if ($request->isMethod('POST')) {
            $newDeleteOld = $request->request->get('delete_old');
            $newStart = $request->request->get('start');
            $newEnd = $request->request->get('end');
            $newMins = $request->request->get('mins');
            $newApikey = $request->request->get('apikey');
            $newCalendarId = $request->request->get('calendar_id');
            try {
                $preferencesService->set('GoogleEventsApiKey', $newApikey);
                $preferencesService->set('GoogleEventsDeleteOld', $newDeleteOld);
                $preferencesService->set('GoogleEventsStart', $newStart);
                $preferencesService->set('GoogleEventsEnd', $newEnd);
                $newCommand = "$console $command $newStart $newEnd";
                $newSchedule = "*/$newMins * * * *"; 
                $currentIngestJob->setCommand($newCommand);
                $currentIngestJob->setSchedule($newSchedule);
                $em->flush();
                $status = true;
                $message = "";
            } catch (\Exception $e) {
                $status = false;
                $message = $e->getMessage();
            }
            // return JSON response with status, message
            return new JsonResponse(array("status" => $status, "message" => $message));
        }

        return array(
            'delete_old' => $deleteOld,
            'start' => $start,
            'end' => $end,
            'mins' => $mins,
            'apikey' => $apikey,
            'calendar_list' => explode(',', $calendarList)
        );
    }

    /**
     * @Route("admin/google-events/add-calendar", options={"expose"=true})
     */
    public function addCalendarAction(Request $request)
    {
        $preferencesService = $this->container->get('system_preferences_service');
        $calendarId = $request->request->get('calendar_id');
        $calendarList = explode(",", $preferencesService->GoogleEventsCalendarList);
        if (!in_array($calendarId, $calendarList)) {
            array_push($calendarList, $calendarId);
            $preferencesService->set('GoogleEventsCalendarList', implode(",",$calendarList));
        }
    
        return new JsonResponse(array("status" => true, "message" => $calendarId.' added'));
    }

    /**
     * @Route("admin/google-events/delete-calendar", options={"expose"=true})
     */
    public function deleteCalendarAction(Request $request)
    {
        $preferencesService = $this->container->get('system_preferences_service');
        $calendarId = $request->request->get('calendar_id');
        $calendarList = explode(",", $preferencesService->GoogleEventsCalendarList);
        $index = array_search($calendarId, $calendarList);
        unset($calendarList[$index]); 
        $preferencesService->set('GoogleEventsCalendarList', implode(",",$calendarList));
    
        return new JsonResponse(array("status" => true, "message" => $calendarId.' deleted'));
    }

    /**
     * @Route("admin/google-events/ingest", options={"expose"=true})
     */
    public function ingestAction(Request $request)
    {
        $googleEventsService = $this->container->getService('newscoop_google_events_plugin.google_events_service');
        $preferencesService = $this->container->get('system_preferences_service');
        $calendarList = explode(',', $preferencesService->GoogleEventsCalendarList);
        $start = ($request->request->get('start')) ? $request->request->get('start') : date('Y-m-d\T00:00:00\Z', strtotime("-1 month"));
        $end = $request->request->get('end');
        $eventsAdded = 0;

        foreach ($calendarList as $calendarId) {
            if (!empty($calendarId)) {
                $added = $googleEventsService->ingestEvents($calendarId, $start, $end);
                $eventsAdded += $added;
            }
        }
        if ($eventsAdded) {
            $status = true;
            $message = "Added " . $eventsAdded . " events";
        } else {
            $status = false;
            $message = "Added " . $eventsAdded . " events";
        }

        return new JsonResponse(array("status" => $status, "message" => $message));
    }


    /**
     * @Route("admin/google-events/load/", options={"expose"=true})
     */
    public function loadEventsAction(Request $request)
    {   
        $em = $this->get('em');
        $cacheService = $this->get('newscoop.cache');
        $googleEventsService = $this->container->get('newscoop_google_events_plugin.google_events_service');
        $criteria = $this->processRequest($request);
        $eventsCount = $googleEventsService->countBy(array('isActive' => true)); 
        $eventsInactiveCount = $googleEventsService->countBy(array('isActive' => false));
     
        $cacheKey = array('google_events__'.md5(serialize($criteria)), $eventsCount, $eventsInactiveCount);
        if ($cacheService->contains($cacheKey)) {
            $responseArray = $cacheService->fetch($cacheKey);                                                                                               
        } else { 
            $events = $em->getRepository('Newscoop\GoogleEventsPluginBundle\Entity\GoogleEvent')->getListByCriteria($criteria);                                         
            
            $processed = array();
            foreach ($events as $event) {
                $processed[] = array(
                    'id' => $event->getId(),
                    'location' => $event->getLocation(),
                    'htmlLink' => $event->getHtmlLink(),
                    'description' => $event->getDescription(),
                    'createdAt' => $event->getCreatedAt(),
                    'start' => $event->getStart(),
                    'end' => $event->getEnd(),
                    'creatorEmail' => $event->getCreatorEmail(),
                    'isActive' => $event->getIsActive()
                );                                                                                           
            }                                                                                                                                               
            
            $responseArray = array(
                'records' => $processed,
                'queryRecordCount' => $events->count,
                'totalRecordCount'=> count($events->items)
            );                                                                                                                                              
            
            $cacheService->save($cacheKey, $responseArray);                                                                                                 
        }                                                                                                                                                   
        
        return new JsonResponse($responseArray);                                                                                                            
    }

    /**
     * Load current scheduled google-events ingest job
     *
     * @return Newscoop\Entity\CronJob
     */
    private function getCurrentJob()
    {
        $em = $this->get('em');
        $schedulerService = $this->get('newscoop.scheduler');
        $job = $em->getRepository('Newscoop\Entity\CronJob')->findOneByName("GoogleEvents plugin ingest events cron job");
        return $job;
    }

    /**
     * Process request parameters
     *
     * @param Request $request Request object
     *
     * @return GoogleEventCriteria
     */
    private function processRequest(Request $request)
    {   
        $criteria = new GoogleEventCriteria();
 
        if ($request->query->has('sorts')) {
            foreach ($request->get('sorts') as $key => $value) {
                $criteria->orderBy[$key] = $value == '-1' ? 'desc' : 'asc';
            }
        }

        if ($request->query->has('queries')) {
            $queries = $request->query->get('queries');
            if (array_key_exists('search', $queries)) {
                $criteria->query = $queries['search'];
            }
        }

        $criteria->maxResults = $request->query->get('perPage', 10);
        if ($request->query->has('offset')) {
            $criteria->firstResult = $request->query->get('offset');
        }
        
        return $criteria;
    }


    /**
     * @Route("/admin/google-events/activate/{id}", options={"expose"=true})
     */
    public function activateAction(Request $request, $id)
    {
        try {
            $em = $this->container->get('em');
            $googleEventsService = $this->container->get('newscoop_google-events_plugin.google-events_service');
            $status = true;

            $event = $em->getRepository('Newscoop\GoogleEventsPluginBundle\Entity\GoogleEvent')
                ->findOneById($id);
            $googleEventsService->activateGoogleEvent($event);

        } catch (\Exception $e) {
            $status = false;
        }

        return new JsonResponse(array(
            'status' => $status
        ));
    }

    /**
     * @Route("admin/google-events/deactivate/{id}", options={"expose"=true})
     */
    public function deactivateAction(Request $request, $id)
    {
        try {
            $em = $this->container->get('em');
            $googleEventsService = $this->container->get('newscoop_google_events_plugin.google_events_service');
            $status = true;

            $event = $em->getRepository('Newscoop\GoogleEventsPluginBundle\Entity\GoogleEvent')
                ->findOneById($id);
            $googleEventsService->deactivateGoogleEvent($event);
        } catch (\Exception $e) {
            $status = false;
        }

        return new JsonResponse(array(
            'status' => $status
        ));
    }

    /**
     * @Route("admin/google-events/delete/{id}", options={"expose"=true})
     */
    public function deleteAction(Request $request, $id)
    {
        try {
            $em = $this->container->get('em');
            $googleEventsService = $this->container->get('newscoop_google_events_plugin.google_events_service');
            $googleEventsService->deleteGoogleEvent($id);
            $status = true;
        } catch (\Exception $e) {
            $status = false;
        }

        return new JsonResponse(array(
            'status' => $status
        ));
    }
}
