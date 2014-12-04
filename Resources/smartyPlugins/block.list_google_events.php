<?php
/**
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * List Yourtube Videos block
 *
 * @param array $params
 * @param string $content
 * @param Smarty_Internal_Template $smarty
 * @param bool $repeat
 * @return string
 */

use Newscoop\GoogleEventsPluginBundle\Entity\GoogleEvent;

function smarty_block_list_google_events(array $params, $content, &$smarty, &$repeat)
{

    $start = (isset($params['start'])) ? $params['start'] : 'now';
    $end = (isset($params['end'])) ? $params['end'] : null;
    $length = (isset($params['length'])) ? $params['length'] : null;

    $container = \Zend_Registry::get('container');
    $em = $container->get('em');
    $cacheService = $container->get('newscoop.cache');
    $cacheKey = "google_events_list_" . $start . '_' . $end . '-' . $length;

    if (!isset($content)) {
        // load the list from entites GoogleEvent
        try {
            $qb = $em->getRepository('Newscoop\GoogleEventsPluginBundle\Entity\GoogleEvent')
                ->createQueryBuilder('p')
                ->where('p.start > :start')
                ->setParameter('start', new \DateTime($start));
            if (isset($end)) {
                $qb->andWhere('p.end < :end')
                    ->setParameter('end', new \DateTime($end));
            }
            if (isset($length)) {
                $qb->setMaxResults($length);
            }
            $qb->andWhere('p.isActive = 1')
                ->addOrderBy('p.start', 'ASC');
            $query = $qb->getQuery();
            $events = $query->getResult();
            $cacheService->save($cacheKey, $events);
        } catch(\Exception $e) {
            error_log($e->getMessage());
        }
    }

    $events = $cacheService->fetch($cacheKey);

    if (!empty($events)) {
        // load the current record
        $event = array_shift($events);
        $smarty->assign('event', $event); 
        $smarty->assign('eventIndex', abs(count($events) - $length)); 
        $cacheService->save($cacheKey, $events);
        $repeat = true;
    } else {
        $repeat = false;
    } 

    return $content;
}

