# Piwik Analytics integration for Thelia E-Commerce

[![Project Status: Active - The project has reached a stable, usable state and is being actively developed.](http://www.repostatus.org/badges/latest/active.svg)](http://www.repostatus.org/#active)

This module implements [Piwik](http://piwik.org) user and e-commerce tracking into [Thelia](http://thelia.net).
It reports the following tracking events:

## User Tracking

This module uses the `main.body-bottom` hook to include the piwik javascript bug into the frontend of a Thelia installation. All user activities can therefor be tracked.

## E-commerce Tracking

Additional to the user tracking, these e-commerce events are tracked:

- Tracking Ecommerce Orders & Items Purchased
- Tracking Add to Cart & Items Added to the Cart
- Tracking Product Page Views & Category Page Views

Have a look at the [e-commerce analytics docs](http://piwik.org/docs/ecommerce-analytics) for more information.

Note that the cart and order tracking uses the [PHP Client for Piwik Analytics Tracking API](https://github.com/piwik/piwik-php-tracker) to communicate with
Piwik.

## Installation

Before installing this module, make sure, that [e-commerce tracking is enabled](http://piwik.org/docs/ecommerce-analytics/#enable-ecommerce-tracking) in Piwik for the site you want to use.

### Manual Installation

- Copy the module into <thelia_root>/local/modules/ directory and be sure that the name of the folder is `HookPiwikAnalytics` **or**
- use the `Install or update a module` functionality in the modules section of the Thelia back office

### Composer

`composer require animal/hook-piwik-analytics-module`

After finishing the installation, activate the module in the modules section of the back office and fill in `Piwik URL` and `Website ID`.

## About

„We build it“ — [ANIMAL](http://animal.at)
