<?php

declare(strict_types=1);

return [

    'ocr' => [

        /*
        |--------------------------------------------------------------------------
        | Firecrawl API Key
        |--------------------------------------------------------------------------
        |
        | Hosted OCR works without an API key. Set a key for higher limits.
        | These settings apply only when markdown(ocr: true) is called and the
        | local conversion requires OCR. Firecrawl receives the entire PDF.
        |
        */

        'api_key' => env('FIRECRAWL_API_KEY', ''),

        /*
        |--------------------------------------------------------------------------
        | Firecrawl API URL
        |--------------------------------------------------------------------------
        |
        | The base URL of the Firecrawl Parse deployment. The package appends
        | /v2/parse, so do not include that endpoint in this value.
        |
        */

        'api_url' => env('FIRECRAWL_API_URL', 'https://api.firecrawl.dev'),

        /*
        |--------------------------------------------------------------------------
        | Request Timeout
        |--------------------------------------------------------------------------
        |
        | The maximum time in seconds to wait for the hosted OCR request.
        | Conversion is synchronous, so allow enough time for scanned PDFs.
        |
        */

        'timeout' => 300,

    ],

];
