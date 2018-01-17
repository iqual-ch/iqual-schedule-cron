<?php
namespace Drupal\scheduler_cron\EventSubscriber;

use Drupal\Core\Config\Config;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Represents a EventSubscriber to subscribe to the request event.
 */
class RequestSubscriber implements EventSubscriberInterface
{

    /**
     * Register running the Scheduler cronjob on shutdown if interval has passed.
     *
     * @param GetResponseEvent $event
     * @return void
     */
    public function runSchedulerCron(GetResponseEvent $event)
    {
        // Get configuration
        $config = \Drupal::config('scheduler_cron.settings');
        $interval = $config->get('interval');
        $log = $config->get('log');
        if (!\is_numeric($interval)) {
            $interval = 5;
        }

        // Get storage for last execution time
        $store = \Drupal::keyValue('scheduler_cron');
        if (!$store->has('last')) {
            $store->set('last', 0);
        }

        // If last execution was longer than $interval ago, register shutdown function.
        if ($store->get('last') < time() - $interval * 60) {
            if ($log) {
                \Drupal::logger('scheduler_cron')->notice('Executing scheduler cron on shutdown');
            }
            drupal_register_shutdown_function(array($this, 'executeCron'), $log, $store);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $events[KernelEvents::REQUEST][] = array('runSchedulerCron');
        return $events;
    }

    /**
     * Execute the cron job.
     *
     * @param boolean $log Whether to log the execution
     * @param \Drupal\Core\KeyValueStore\KeyValueStoreInterface $store The KeyValueStore for tracking the execution time.
     * @return void
     */
    public static function executeCron($log, $store)
    {
        // Get Scheduler cronjob key for url
        $config = \Drupal::config('scheduler.settings');
        $key = $config->get('lightweight_cron_access_key');
        $url = \Drupal::request()->getSchemeAndHttpHost() . '/scheduler/cron/' . $key;

        // Call url
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_exec($ch);
        curl_close($ch);

        // Update execution time.
        $store->set('last', time());

        if ($log) {
            \Drupal::logger('scheduler_cron')->notice('Executed scheduler cron');
        }

    }

}
