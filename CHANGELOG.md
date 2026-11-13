# Release Notes for Action CRM for Formie

## 1.0.0
- Initial release

## 1.0.1
- Fixed sendPayload and added payload events

## 1.0.2
- Fixed SourceURI, SourceLeadCreationDateUtc and event payload

## 2.0.0
> [!IMPORTANT]
> Requires Formie 4. Use version 1.x for Formie 3.

### Changed
- Updated the integration to the Formie 4 custom integration API:
  - The field mapping (`leadFieldMapping`) is now declared with `#[FormIntegrationSetting]`. Without it Formie 4 neither saves nor populates the mapping, so leads were sent with every field empty.
  - Form settings are now defined with `defineFormSettingsSchema()` instead of the `_formSettings` Twig template.
  - `fetchFormSettings()` → `fetchConfig()` / `IntegrationConfig`
  - `sendPayload()` → `executePayload()`, returning an `IntegrationResult`
  - `getClient()` → `defineClient()`

### Fixed
- Fixed `EVENT_AFTER_SEND_PAYLOAD` having the same value as `EVENT_BEFORE_SEND_PAYLOAD` (`onBeforeSendPayload`); it is now `onAfterSendPayload`. Listeners of the "before" event were run a second time after the payload was sent.
- Fixed rejected leads reporting `Undefined array key "ErrorMessage"` instead of the reason returned by Action CRM (`ImportResult[].ImportErrors`, e.g. "LastName is required").
- Fixed a `strtoupper()` deprecation error when the vehicle channel is empty.
