<?php

namespace Drupal\scheduler_cron\EventSubscriber;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Represents a EventSubscriber to subscribe to the request event.
 */
class RequestSubscriber implements EventSubscriberInterface {

  /**
   * Register running the Scheduler cronjob on shutdown if interval has passed.
   *
   * @param Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event.
   */
  public function runSchedulerCron(RequestEvent $event) {
    // Get configuration.
    $config = \Drupal::config('scheduler_cron.settings');
    $interval = $config->get('interval');
    $log = $config->get('log');
    if (!\is_numeric($interval)) {
      $interval = 5;
    }

    // Get storage for last execution time.
    $store = \Drupal::keyValue('scheduler_cron');
    if (!$store->has('last')) {
      $store->set('last', 0);
    }

    // If last execution was longer than $interval ago, register
    // shutdown function.
    if ($store->get('last') < time() - $interval * 60) {
      if ($log) {
        \Drupal::logger('scheduler_cron')->notice('Executing scheduler cron on shutdown');
      }
      // Update execution time.
      $store->set('last', time());

      drupal_register_shutdown_function([$this, 'executeCron'], $log, $store);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::REQUEST][] = ['runSchedulerCron'];
    return $events;
  }

  /**
   * Execute the cron job.
   *
   * @param bool $log
   *   Whether to log the execution.
   */
  public static function executeCron($log) {
    // Get Scheduler cronjob key for url.
    $schedulerManager = \Drupal::service('scheduler.manager');
    $schedulerManager->runLightweightCron();

    if ($log) {
      \Drupal::logger('scheduler_cron')->notice('Executed scheduler cron');
    }
  }

}
