<!doctype html>
<html>

<head>
    <title>{{ $config->get('ui.title', config('app.name') . ' - API Docs') }}</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>

<body>
    <!-- Add your own OpenAPI/Swagger specification URL here: -->
    <script id="api-reference" type="application/json">
        @json($spec)
    </script>

    <!-- Optional: You can set a full configuration object like this: -->
    <script>
        var configuration = {
        theme: 'saturn',
        hideDownloadButton: true,
    }

    document.getElementById('api-reference').dataset.configuration =
        JSON.stringify(configuration)
    </script>
    <script src="/standalone.js"></script>
</body>

</html>
