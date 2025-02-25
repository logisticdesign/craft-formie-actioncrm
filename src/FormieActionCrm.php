<?php

namespace logisticdesign\formieactioncrm;

use Craft;
use craft\base\Plugin;
use logisticdesign\formieactioncrm\integrations\crm\Action;
use verbb\formie\events\RegisterIntegrationsEvent;
use verbb\formie\services\Integrations;
use yii\base\Event;

/**
 * Action CRM for Formie plugin
 *
 * @method static FormieActionCrm getInstance()
 * @author Logistic Design <dev@logisticdesign.it>
 * @copyright Logistic Design
 * @license MIT
 */
class FormieActionCrm extends Plugin
{
    public string $schemaVersion = '1.0.0';

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        $this->attachEventHandlers();

        // Any code that creates an element query or loads Twig should be deferred until
        // after Craft is fully initialized, to avoid conflicts with other plugins/modules
        Craft::$app->onInit(function() {
            // ...
        });
    }

    private function attachEventHandlers(): void
    {
        Event::on(
            Integrations::class,
            Integrations::EVENT_REGISTER_INTEGRATIONS,
            function(RegisterIntegrationsEvent $event) {
                $event->crm[] = Action::class;
            }
        );
    }
}
