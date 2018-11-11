# UPC Maintenance Monitor

## About

UPC Maintenance Monitor is an open source monitoring tool for scheduled maintenances of the hungarian cable provider UPC. It monitors the maintenance schedule webpage and emails you when a maintenance is scheduled for the town/city you provided.

See the example site: https://www.do01.r-us.hu/upc-karb/

## Thanks and credits

- The idea started with an [article of David Walsh](https://davidwalsh.name/php-notifications)
- The website parsing is using [Simple HTML DOM](https://github.com/samacs/simple_html_dom)
- The autocomplete functionality is using [Alexander Farkas's Remote-List jQuery plugin](https://github.com/aFarkas/remote-list)
- The data source for autocomplete is provided by the hungarian postal services as an [Excel workbook](https://www.posta.hu/static/internet/download/Iranyitoszam-Internet_uj.xlsx)
- The autocomplete data generator is using [SimpleXLSX class](https://github.com/shuchkin/simplexlsx) to process the Excel table from hungarian postal services, and generate a JSON array
- The subscription, and email notification code is based on [Remy van Elst's certificate-expiry-monitor](https://github.com/RaymiiOrg/certificate-expiry-monitor)


## Requirements

- PHP 5.6+
- OpenSSL
- PHP must allow remote fopen.

## Installation

Unpack, change some variables, setup a cronjob and go!

First get the code and unpack it to your webroot:

    cd /var/www/html/
    git clone https://github.com/gnanet/upc-maintenance-monitor.git

Create the database folder, outside of your webroot. If you create these inside your webroot, everybody can read them.

    mkdir -p /var/www/upc-mm-db/
    chown -R $wwwuser:$wwwgroup /var/www/upc-mm-db 

These files are used by the tool as database for checks, on first access they will be created.


Change the location of these files in `lib/settings.php`:

    // set this to a location outside of your webroot so that it cannot be accessed via the internets.
    $datastore = '/var/www/upc-mm-db';
    $pre_check_file = $datastore.'/upc_pre_checks.json';
    $check_file = $datastore.'/upc_checks.json';
    $deleted_check_file = $datastore.'/upc_deleted_checks.json';

Also change the `$current_domain` variable, it is used in all the email addresses.

    $current_domain = "www.do01.r-us.hu";

And `$current_link`, which may or may not be the same. It is used in the confirm and unsubscribe links, and depends on your webserver configuration. `example.com/subdir` here means your unsubscribe links will start `https://example.com/subdir/unsubscribe.php`.

    $current_link = "www.do01.r-us.hu/upc-maintenance-monitor";

Visit https://`$current_link`/lib/telepulesnevek.php one time, to generate the autocomplete data.

Set up the cronjob to run once a day:

    # /etc/cron.d/upc-maintenance-monitor
    1 1 * * *    $wwwuser    $(which php) /var/www/html/upc-maintenance-monitor/cron.php >> /var/log/upc-maintenance-monitor.log 2>&1


The default timeout for checks is 2 seconds. If this is too fast for your internal services, this can be raised in the `lib/settings.php` file.

