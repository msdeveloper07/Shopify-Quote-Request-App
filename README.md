# ShowQuote — Shopify Quote Request App

ShowQuote is a Shopify application designed to help merchants manage product quote requests instead of relying exclusively on the standard purchase flow.

## Features

* **Get a Quote flow:** Provides a quote-request experience on supported Shopify storefront product pages.
* **Quote management:** Supports a merchant-side dashboard for viewing quote requests.
* **Email quotations:** Integrates with SendGrid for email-related functionality.
* **Shopify integration:** Uses Shopify app configuration, app proxy, and script-tag functionality.

## Tech Stack

* PHP 8.2+
* Laravel 12
* JavaScript, CSS
* Vite and Tailwind CSS
* SendGrid
* Shopify

## Local Development

### Prerequisites

* PHP 8.2 or compatible version
* Composer
* Node.js and npm
* A configured Shopify development environment
* Required database and email settings

### Setup

1. Install PHP dependencies:

   ```bash
   composer install
   ```

2. Create your local environment configuration:

   ```bash
   cp .env.example .env
   ```

   On Windows, copy `.env.example` to `.env` manually if needed.

3. Generate the Laravel application key:

   ```bash
   php artisan key:generate
   ```

4. Configure the database, Shopify credentials, and email settings in `.env`.

5. Install frontend dependencies:

   ```bash
   npm install
   ```

6. Build frontend assets:

   ```bash
   npm run build
   ```

7. Configure the Shopify app URLs and required scopes for your own development environment before running the app.

## Configuration and Security

* Never commit `.env`, API keys, access tokens, customer information, or session files.
* Use development-store credentials and URLs appropriate to your environment.
* Do not reuse production or client credentials in personal development environments.

## Project Status

Setup and feature documentation should be verified against the current implementation before deployment.

## License

Confirm the applicable project license and distribution permissions before publishing or redistributing this code.
