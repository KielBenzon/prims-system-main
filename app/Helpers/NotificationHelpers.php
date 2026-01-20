<?php

if (!function_exists('getNotificationLink')) {
    function getNotificationLink($notification)
    {
        switch ($notification->message) {
            case 'A new announcement has been created':
                return route('announcement');
            case 'A new request has been approved':
                return route('request');
            case 'A new donation has been made':
            //     return route('request');
            // case 'A new donation has been deleted':
            //     return route('donation');
            // case 'A new donation has been deleted':
                return route('donation');
            case 'A new baptismal certificate request has been created':
                return route('baptismal_certificate_request');
            case 'A new document has been uploaded':
                return route('document');
            case 'A document has been deleted':
                return route('document');
            case 'A new priest has been added':
                return route('priests');
            case 'A request payment has been made':
                return route('payment');
            default:
                return '#';
        }
    }
}