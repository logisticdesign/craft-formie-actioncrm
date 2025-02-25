<?php

namespace logisticdesign\formieactioncrm\integrations\crm;

use Carbon\Carbon;
use Craft;
use craft\helpers\App;
use craft\helpers\StringHelper;
use GuzzleHttp\Client;
use Throwable;
use verbb\formie\base\Crm;
use verbb\formie\base\Integration;
use verbb\formie\elements\Submission;
use verbb\formie\Formie;
use verbb\formie\models\IntegrationField;
use verbb\formie\models\IntegrationFormSettings;

class Action extends Crm
{
    public ?string $username = null;
    public ?string $password = null;
    public ?string $sourceId = null;

    public ?array $fieldMapping = null;

    public static function displayName(): string
    {
        return Craft::t('formie', 'Action CRM');
    }

    public function getIconUrl(): string
    {
        return Craft::$app->getAssetManager()->getPublishedUrl('@logisticdesign/formieactioncrm/icon.svg', true);
    }

    public function getDescription(): string
    {
        return Craft::t('formie', 'Integration of Action CRM.');
    }

    public function getSettingsHtml(): string
    {
        $variables = $this->getSettingsHtmlVariables();

        return Craft::$app->getView()->renderTemplate('formie-actioncrm/integrations/crm/action/_pluginSettings', $variables);
    }

    public function getFormSettingsHtml($form): string
    {
        $variables = $this->getFormSettingsHtmlVariables($form);

        return Craft::$app->getView()->renderTemplate('formie-actioncrm/integrations/crm/action/_formSettings', $variables);
    }

    public function fetchFormSettings(): IntegrationFormSettings
    {
        $fields = [
            new IntegrationField([
                'handle' => 'email',
                'name' => Craft::t('formie-actioncrm', 'Email'),
                'required' => true,
            ]),
            new IntegrationField([
                'handle' => 'firstName',
                'name' => Craft::t('formie-actioncrm', 'First name'),
            ]),
            new IntegrationField([
                'handle' => 'lastName',
                'name' => Craft::t('formie-actioncrm', 'Last name'),
            ]),
            new IntegrationField([
                'handle' => 'phone',
                'name' => Craft::t('formie-actioncrm', 'Phone'),
            ]),
        ];

        return new IntegrationFormSettings([
            'lead' => $fields,
        ]);
    }

    public function sendPayload(Submission $submission): bool
    {
        try {
            $formValues = $this->getFieldMappingValues($submission, $this->fieldMapping, $this->getFormSettingValue('lead'));
            $formHandle = $submission->getFormHandle();

            $payload = [
                'ImportSourceID' => App::parseEnv($this->sourceId),
                'ImportSourceLeadID' => StringHelper::UUID(),
                'SourceLeadCreationDateUtc' => Carbon::now()->format('Y-m-d H:i'),
                // 'SalesDepartmentID' => $this->salesDepartmentId($submission),
                // 'LeadRequestTypeID' => $this->leadRequestTypeId($submission),
                'ContactSourceID' => 'EMAIL',
                'CustomerTypeID' => 'P',
                'FirstName' => $formValues['firstName'] ?? '',
                'LastName' => $formValues['lastName'] ?? '',
                'Email1' => $formValues['email'] ?? '',
                'Mobile1' => $formValues['phone'] ?? '',
                // 'SourceURI' => $submission->message['referralUrl'] ?? '',
                'OriginCodes' => [
                    ['OriginCode' => 'utm_source', 'OriginValue' => 'website'],
                    ['OriginCode' => 'utm_medium', 'OriginValue' => 'form'],
                    ['OriginCode' => 'utm_campaign', 'OriginValue' => $formHandle],
                ],
                // 'PrivacyType' => $this->privacyType($submission),
            ];

            // $this->deliverPayload($submission, 'api/lead/post', $payload);

        } catch (Throwable $e) {
            Integration::apiError($this, $e);

            return false;
        }

        return true;
    }

    public function getClient(): Client
    {
        if ($this->_client) {
            return $this->_client;
        }

        $accessToken = $this->getActionAccessToken();

        return $this->_client = Craft::createGuzzleClient([
            'base_uri' => 'http://service.action-crm.com/',
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function getActionAccessToken(): string
    {
        $client = Craft::createGuzzleClient([
            'base_uri' => 'http://service.action-crm.com/',
        ]);

        $response = $client->post('login', [
            'form_params' => [
                'userName' => App::parseEnv($this->username),
                'password' => App::parseEnv($this->password),
                'grant_type' => 'password',
            ],
        ]);

        $body = json_decode($response->getBody(), true);

        return $body['access_token'];
    }

    public function fetchConnection(): bool
    {
        try {
            $this->getActionAccessToken();
        } catch (Throwable $e) {
            Integration::apiError($this, $e);

            return false;
        }

        return true;
    }

    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['username', 'password', 'sourceId'], 'required'];

        $main = $this->getFormSettingValue('main');

        // Validate when saving form settings
        $rules[] = [
            ['fieldMapping'], 'validateFieldMapping', 'params' => $main, 'when' => function($model) {
                return $model->enabled;
            }, 'on' => [Integration::SCENARIO_FORM],
        ];

        return $rules;
    }
}
