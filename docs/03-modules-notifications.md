# Notifications module

Notifications owns the email and SMS delivery pipeline, editable event
templates, provider selection, and delivery logs. Gateway implementations are
registered through ProviderRegistry.

The bundled providers are app-default mail, custom SMTP, log SMS, and Twilio
SMS. Additional gateways must be separate add-ons and must register their own
provider, routes, and settings.
