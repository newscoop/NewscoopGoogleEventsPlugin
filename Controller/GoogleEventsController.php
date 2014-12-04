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
use Newscoop\GoogleEventsPluginBundle\TemplateList\GoogleEventCriteria;

/**
 * Route("/google-events")
 */
class GoogleEventsController extends Controller
{
    /**
     * @Route("/google-events/search")
     */
    public function eventSearchAction(Request $request)
    {
        $em = $this->get('em');
        $cacheService = $this->get('newscoop.cache');
        $templatesService = $this->container->get('newscoop.templates.service');
        $googleEventsService = $this->container->get('newscoop_google_events_plugin.google_events_service');
        $criteria = new GoogleEventCriteria();
        
        // params
        $search = $this->_getParam('search', $request);
        $perPage = $this->_getParam('perPage', $request);
        $offset = $this->_getParam('offset', $request);

        if ($search) {
            $criteria->query = $search;
        }        
        $criteria->maxResults = ($perPage) ? $perPage: '12';
        if ($offset) {
            $criteria->firstResult = $offset;
        }
        $cacheKey = array('google_events_events__'.md5(serialize($criteria)));

        if ($cacheService->contains($cacheKey)) {
            $responseArray = $cacheService->fetch($cacheKey);                                                                                               
        } else { 
            $events = $em->getRepository('Newscoop\GoogleEventsPluginBundle\Entity\GoogleEvent')->getListByCriteria($criteria);
            $cacheService->save($cacheKey, $events);                                                                                                 
        }

        $smarty = $templatesService->getSmarty();
        $templateDir = array_shift($smarty->getTemplateDir());
        $templateFile = "_views/google_events_search_results.tpl";
        $response = new Response();
        $response->headers->set('Content-Type', 'text/html');

        // render _views/google_events_event.tpl if it exists, if not render the plugin template instead
        if (!file_exists($templateDir . $templateFile)) {
            $templateFile = __DIR__ . "/../Resources/views/GoogleEvents/google_events_search_results.tpl";
        }

        $next = ($offset + $criteria->maxResults);
        $prev = ($offset - $criteria->maxResults);
        if ($next < count($events)) { 
            $nextPageUrl = $this->generateUrl('newscoop_googleeventsplugin_googleevents_eventsearch', array(
                'search' => $search,
                'offset' => $next,
                'perPage' => $perPage
            ));
        } else {
            $nextPageUrl = "#";
        }

        if ($criteria->firstResult > 0) { 
            $prevPageUrl = $this->generateUrl('newscoop_googleeventsplugin_googleevents_eventsearch', array(
                'search' => $search,
                'offset' => $prev,
                'perPage' => $perPage
            ));
        } else {
            $prevPageUrl = "#";
        }

        $response->setContent($templatesService->fetchTemplate(
            $templateFile, 
            array(
                'searchTerm' => $search,
                'offset' => $offset,
                'perPage' => $perPage,
                'events' => $events, 
                'eventCount' => count($events),
                'nextPageUrl' => $nextPageUrl,
                'prevPageUrl' => $prevPageUrl
            )
        ));
        return $response;

    }

    /**
     * @Route("/google-events/events/{id}", defaults={"id" = 0})
     */
    public function eventsAction($id, Request $request)
    {
        $googleEventsService = $this->container->getService('newscoop_google_events_plugin.google_events_service');
        $templatesService = $this->container->get('newscoop.templates.service');
        $smarty = $templatesService->getSmarty();
        $templateDir = array_shift($smarty->getTemplateDir());

        if ($id > 0) {
            $event = $googleEventsService->getGoogleEventById($id);
            $templateFile = "_views/google_event.tpl";
            $response = new Response();
            $response->headers->set('Content-Type', 'text/html');
            // render _views/google_event.tpl if it exists, if not render the plugin template instead
            if (!file_exists($templateDir . $templateFile)) {
                $templateFile = __DIR__ . "/../Resources/views/GoogleEvents/google_event.tpl";
            }
            $response->setContent($templatesService->fetchTemplate(
                $templateFile, 
                array('event' => $event)
            ));
        } else {
            $events = $googleEventsService->getAllGoogleEvents();
            $templateFile = "_views/google_events.tpl";
            $response = new Response();
            $response->headers->set('Content-Type', 'text/html');
            // render _views/google_event.tpl if it exists, if not render the plugin template instead
            if (!file_exists($templateDir . $templateFile)) {
                $templateFile = __DIR__ . "/../Resources/views/GoogleEvents/google_events.tpl";
            }
            $response->setContent($templatesService->fetchTemplate(
                $templateFile, 
                array('events' => $events)
            ));

        }

        return $response;
    }

    public function _getParam($param, Request $request)
    {
        if ($request !== null) {
            if ($request->request->get($param)) {
                return $request->request->get($param);
            }
            if ($request->query->get($param)) {
                return $request->query->get($param);
            }
        }

        return null;
    }
}

