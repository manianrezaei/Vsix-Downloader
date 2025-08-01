import requests
import json

def download_vsix(extension_id, output_file='extension.vsix'):
    publisher, extension_name = extension_id.split('.')

    url = 'https://marketplace.visualstudio.com/_apis/public/gallery/extensionquery'

    payload = {
        "filters": [{
            "criteria": [{
                "filterType": 7,
                "value": extension_id
            }],
            "pageNumber": 1,
            "pageSize": 1,
            "sortBy": 0,
            "sortOrder": 0
        }],
        "assetTypes": [],
        "flags": 0x1 | 0x2 | 0x80
    }

    headers = {
        "Content-Type": "application/json",
        "Accept": "application/json;api-version=3.0-preview.1"
    }

    response = requests.post(url, headers=headers, data=json.dumps(payload))
    data = response.json()

    try:
        files = data['results'][0]['extensions'][0]['versions'][0]['files']
        vsix_url = next(file['source'] for file in files if file['assetType'] == 'Microsoft.VisualStudio.Services.VSIXPackage')
    except (KeyError, StopIteration):
        print("❌ اکستنشن یا لینک دانلود یافت نشد.")
        return

    print(f"📥 در حال دانلود از: {vsix_url}")
    vsix_data = requests.get(vsix_url).content

    with open(output_file, 'wb') as f:
        f.write(vsix_data)

    print(f"✅ دانلود کامل شد: {output_file}")

# مثال: دانلود اکستنشن prettier
download_vsix('esbenp.prettier-vscode', 'prettier.vsix')