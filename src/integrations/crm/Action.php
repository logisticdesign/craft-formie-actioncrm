<?php

namespace logisticdesign\formieactioncrm\integrations\crm;

use Carbon\Carbon;
use Craft;
use craft\helpers\App;
use craft\helpers\StringHelper;
use GuzzleHttp\Client;
use logisticdesign\formieactioncrm\enums\ContactSourceEnum;
use logisticdesign\formieactioncrm\enums\CustomerTypeEnum;
use logisticdesign\formieactioncrm\enums\DepartmentEnum;
use logisticdesign\formieactioncrm\enums\LeadRequestTypeEnum;
use logisticdesign\formieactioncrm\enums\PrivacyTypeEnum;
use logisticdesign\formieactioncrm\enums\VehicleChannelEnum;
use Throwable;
use verbb\formie\base\Crm;
use verbb\formie\base\Integration;
use verbb\formie\elements\Submission;
use verbb\formie\models\IntegrationField;
use verbb\formie\models\IntegrationFormSettings;

class Action extends Crm
{
    public ?string $username = null;
    public ?string $password = null;
    public ?string $sourceId = null;

    public ?array $leadFieldMapping = null;

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
                'name' => Craft::t('formie-actioncrm', 'Nome'),
                'required' => true,
            ]),
            new IntegrationField([
                'handle' => 'lastName',
                'name' => Craft::t('formie-actioncrm', 'Cognome'),
                'required' => true,
            ]),
            new IntegrationField([
                'handle' => 'phone',
                'name' => Craft::t('formie-actioncrm', 'Telefono'),
            ]),
            new IntegrationField([
                'handle' => 'message',
                'name' => Craft::t('formie-actioncrm', 'Messaggio'),
            ]),
            new IntegrationField([
                'handle' => 'salesDepartment',
                'name' => Craft::t('formie-actioncrm', 'Reparto (default: SALES)'),
            ]),
            new IntegrationField([
                'handle' => 'leadRequestType',
                'name' => Craft::t('formie-actioncrm', 'Tipo richiesta Lead (default: INFO)'),
            ]),
            new IntegrationField([
                'handle' => 'marketing',
                'name' => Craft::t('formie-actioncrm', 'Flag Marketing'),
            ]),
            new IntegrationField([
                'handle' => 'vehicleBrandName',
                'name' => Craft::t('formie-actioncrm', 'Veicolo: Brand'),
            ]),
            new IntegrationField([
                'handle' => 'vehicleModelName',
                'name' => Craft::t('formie-actioncrm', 'Veicolo: Modello'),
            ]),
            new IntegrationField([
                'handle' => 'vehicleVersionName',
                'name' => Craft::t('formie-actioncrm', 'Veicolo: Versione'),
            ]),
            new IntegrationField([
                'handle' => 'vehicleChannel',
                'name' => Craft::t('formie-actioncrm', "Veicolo: Canale d'acquisto"),
            ]),
            new IntegrationField([
                'handle' => 'vehicleKm',
                'name' => Craft::t('formie-actioncrm', 'Veicolo: Km'),
            ]),
            new IntegrationField([
                'handle' => 'vehiclePlate',
                'name' => Craft::t('formie-actioncrm', 'Veicolo: Targa'),
            ]),
        ];

        return new IntegrationFormSettings([
            'lead' => $fields,
        ]);
    }

    public function sendPayload(Submission $submission): bool
    {
        try {
            $formValues = $this->getFieldMappingValues($submission, $this->leadFieldMapping, $this->getFormSettingValue('lead'));
            $formHandle = $submission->getFormHandle();

            $privacyType = ($formValues['marketing'] ?? null)
                ? PrivacyTypeEnum::MARKETING->value
                : PrivacyTypeEnum::REQUEST->value;

            $vehicleChannel = $formValues['vehicleChannel'] ?? null;
            $vehicleChannelEnum = VehicleChannelEnum::tryFrom($vehicleChannel);

            $isUsedVehicle = $vehicleChannelEnum ? $vehicleChannelEnum->isUsed() : null;

            $payload = [
                'ImportSourceID' => App::parseEnv($this->sourceId),
                'ImportSourceLeadID' => StringHelper::UUID(),
                'SourceLeadCreationDateUtc' => Carbon::now()->format('Y-m-d H:i'),
                'SalesDepartmentID' => $formValues['salesDepartment'] ?? DepartmentEnum::SALES->value,
                'LeadRequestTypeID' => $formValues['leadRequestType'] ?? LeadRequestTypeEnum::INFO->value,
                'ContactSourceID' => ContactSourceEnum::EMAIL->value,
                'CustomerTypeID' => CustomerTypeEnum::PRIVATE->value,
                'FirstName' => $formValues['firstName'] ?? null,
                'LastName' => $formValues['lastName'] ?? null,
                'Email1' => $formValues['email'] ?? null,
                'Mobile1' => $formValues['phone'] ?? null,
                'SourceURI' => Craft::$app->getRequest()->getAbsoluteUrl(),
                'OriginCodes' => [
                    ['OriginCode' => 'utm_source', 'OriginValue' => 'website'],
                    ['OriginCode' => 'utm_medium', 'OriginValue' => 'form'],
                    ['OriginCode' => 'utm_campaign', 'OriginValue' => $formHandle],
                ],
                'PrivacyType' => $privacyType,
                'LeadComment' => $formValues['message'] ?? null,
                'BrandName' => $formValues['vehicleBrandName'] ?? null,
                'ModelName' => $formValues['vehicleModelName'] ?? null,
                'VersionName' => $formValues['vehicleVersionName'] ?? null,
                'VehicleChannelID' => $vehicleChannel,
                'IsUsedVehicle' => $isUsedVehicle,
                'OwnedKm' => $formValues['vehicleKm'] ?? null,
                'OwnedNumberPlate' => $formValues['vehiclePlate'] ?? null,
            ];

            $response = $this->deliverPayload($submission, 'api/lead/post', [$payload]);

            if ($response['IsSuccess'] === false) {
                Integration::apiError($this, $response['ErrorMessage']);

                return false;
            }
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

        $lead = $this->getFormSettingValue('lead');

        // Validate when saving form settings
        $rules[] = [
            ['leadFieldMapping'], 'validateFieldMapping', 'params' => $lead, 'when' => function($model) {
                return $model->enabled;
            }, 'on' => [Integration::SCENARIO_FORM],
        ];

        return $rules;
    }
}
