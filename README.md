\# Shopify Quote Request App



A Shopify application built with Laravel that helps merchants collect customer quote requests, manage them through a dashboard, and send quotations by email.



\## Features



\* \*\*Get a Quote experience\*\* for Shopify storefronts.

\* \*\*Quote request management\*\* through a merchant dashboard.

\* \*\*Email integration\*\* for sending quotations to customers.

\* \*\*Shopify integration\*\* using app configuration, app proxy, and storefront script functionality.



\## Tech Stack



\* PHP 8.2+

\* Laravel 12

\* JavaScript and CSS

\* Vite

\* Tailwind CSS

\* SendGrid

\* Shopify



\## Requirements



\* PHP 8.2 or later

\* Composer

\* Node.js and npm

\* A Shopify development store and app configuration

\* Database and email credentials



\## Local Setup



1\. Install PHP dependencies:



&#x20;  ```bash

&#x20;  composer install

&#x20;  ```



2\. Copy `.env.example` to `.env`.



3\. Generate the Laravel application key:



&#x20;  ```bash

&#x20;  php artisan key:generate

&#x20;  ```



4\. Configure the database, Shopify settings, and email credentials in `.env`.



5\. Install frontend dependencies:



&#x20;  ```bash

&#x20;  npm install

&#x20;  ```



6\. Build frontend assets:



&#x20;  ```bash

&#x20;  npm run build

&#x20;  ```



7\. Configure the Shopify app URLs and permissions for your development environment before running the app.



\## Security



\* Never commit `.env` files, API keys, access tokens, or customer data.

\* Keep runtime files and session data out of version control.

\* Use your own development credentials and Shopify store when testing.



\## License



Check the applicable project license and distribution permissions before redistributing this application.



