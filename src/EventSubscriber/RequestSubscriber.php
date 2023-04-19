<?php

namespace Drupal\scheduler_cron\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Represents a EventSubscriber to subscribe to the request event.
 */
class RequestSubscriber implements EventSubscriberInterface {

  /**
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The key value store to use.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValueFactory;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Creates a DiffFormatter to render diffs in a table.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value_factory
   *   The key value store to use.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger channel factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory, KeyValueFactoryInterface $key_value_factory, LoggerChannelFactoryInterface $logger) {
    $this->configFactory = $config_factory;
    $this->keyValueFactory = $key_value_factory;
    $this->logger = $logger;
  }

  /**
   * Register running the Scheduler cronjob on shutdown if interval has passed.
   *
   * @param Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event.
   */
  public function runSchedulerCron(RequestEvent $event) {
    // Get configuration.
    $config = $this->configFactory->get('scheduler_cron.settings');
    $interval = $config->get('interval');
    $log = $config->get('log');
    if (!\is_numeric($interval)) {
      $interval = 5;
    }

    // Get storage for last execution time.
    $store = $this->keyValueFactory->get('scheduler_cron');
    if (!$store->has('last')) {
      $store->set('last', 0);
    }

    // If last execution was longer than $interval ago, register
    // shutdown function.
    if ($store->get('last') < time() - $interval * 60) {
      if ($log) {
        $this->logger->get('scheduler_cron')->notice('Executing scheduler cron on shutdown');
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
