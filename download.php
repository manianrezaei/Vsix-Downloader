<?php

function downloadVsix($extensionId, $outputFile = 'aider.vsix') {
    [$publisher, $extensionName] = explode('.', $extensionId);

    $url = 'https://marketplace.visualstudio.com/_apis/public/gallery/extensionquery';

    $payload = [
        'filters' => [[
            'criteria' => [[
                'filterType' => 7,
                'value' => $extensionId
            ]],
            'pageNumber' => 1,
            'pageSize' => 1,
            'sortBy' => 0,
            'sortOrder' => 0
        ]],
        'assetTypes' => [],
        'flags' => 0x1 | 0x2 | 0x80
    ];

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json;api-version=3.0-preview.1'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (empty($data['results'][0]['extensions'][0]['versions'][0]['files'])) {
        die("Extension not found or no downloadable VSIX.\n");
    }

    $files = $data['results'][0]['extensions'][0]['versions'][0]['files'];
    $vsixUrl = null;

    foreach ($files as $file) {
        if ($file['assetType'] === 'Microsoft.VisualStudio.Services.VSIXPackage') {
            $vsixUrl = $file['source'];
            break;
        }
    }

    if (!$vsixUrl) {
        die("VSIX URL not found.\n");
    }

    echo "Downloading from: $vsixUrl\n";
    $vsixData = file_get_contents($vsixUrl);
    file_put_contents($outputFile, $vsixData);

    echo "Downloaded to $outputFile\n";
}

 
downloadVsix('esbenp.prettier-vscode', 'prettier.vsix');