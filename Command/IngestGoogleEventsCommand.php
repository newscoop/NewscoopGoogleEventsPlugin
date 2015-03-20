<?php
/**
 * @package   Newscoop\GoogleEventsPluginBundle
 * @author    Mark Lewis <mark.lewis@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\GoogleEventsPluginBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Newscoop\GoogleEventsPluginBundle\Entity\GoogleEvent;

/**
 * Gets google events via google api by hashtag and insert into plugin_google_event
 */
class IngestGoogleEventsCommand extends ContainerAwareCommand
{
    /**
     */
    protected function configure()
    {
        $this
        ->setName('google_events:ingest')
        ->addOption('calendar_id', null, InputArgument::OPTIONAL, 'google calendar id (ex: mygoogleid@gmail.com)')
        ->addOption('start', null, InputArgument::OPTIONAL, 'google calendar start date string (ex: 2014-01-01T00:00:00Z)')
        ->addOption('end', null, InputArgument::OPTIONAL, 'google calendar end date string (ex: 2015-01-01T00:00:00Z)')
        ->setDescription('Gets google events by calendar_id and insert into plugin_google_event');
    }

    /**
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $googleEventsService = $this->getContainer()->getService('newscoop_google_events_plugin.google_events_service');
        $preferencesService = $this->getContainer()->getService('system_preferences_service');
        // default to one month back
        $start = ($input->getOption('start')) ? $input->getOption('start') : date('Y-m-d\T00:00:00\Z', strtotime("-1 month"));
        $end = $input->getOption('end');
        $calendarId = $input->getOption('calendar_id');
        $deleteOld = $preferencesService->GoogleEventsDeleteOld;

        try {
            $googleEventsService->setTemporaryTimezone();

            if (empty($calendarId)) {

                $calendarList = explode(",", $preferencesService->GoogleEventsCalendarList);
                $eventsAdded = 0;
                foreach ($calendarList as $calendarId) {
                    if (!empty($calendarId)) {
                        $added = $googleEventsService->ingestEvents($calendarId, $start, $end);
                        $eventsAdded += $added;
                    }
                }
            } else {
                $eventsAdded = $googleEventsService->ingestEvents($calendarId, $start, $end);
            }

            $googleEventsService->restoreTimezone();

            if ($deleteOld == "ON") {
                // delete old events
                $deleted = $googleEventsService->deleteOldEvents();
                $output->writeln('<info>Deleted ' . $deleted . ' events.</info>');
            }
            $output->writeln('<info>Finished...' . $eventsAdded . ' records ingested.</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>Error occured: '.$e->getMessage().'</error>');
            return false;
        }
    }

}
