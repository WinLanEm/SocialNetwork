<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
        <meta content="{{csrf_token()}}" name="csrf-token">
        @vite('resources/js/app.js')
        @vite('resources/css/Common/abstract.css')
        @routes
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
