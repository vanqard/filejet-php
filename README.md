# FileJet PHP library

Provides PHP wrapper for communication with FileJet API.

For integration with Symfony project visit [filejet/filejet-bundle](https://github.com/filejet/filejet-bundle) repository.

## Installation

You can install FileJet PHP library easily via [Composer](https://getcomposer.org/):

```bash
composer require filejet/filejet-php ^1.0
```

## Usage

You need Storage ID and API key to be able to communicate with FileJet API. You can find those at [filejet.io](https://filejet.io) after registration.

FileJet PHP library internally calls FileJet API via HttpClient. We have used for our implementation [HTTPlug](http://docs.php-http.org/en/latest/httplug/introduction.html) project so you can use any HTTP client you want. You can easily use it with `php-http/guzzle6-adapter` by installing it via Composer (see below) or you can follow [HTTPlug tutorial](http://docs.php-http.org/en/latest/httplug/tutorial.html) for registering any HTTP client you choose.

```bash
composer require php-http/guzzle6-adapter
```

Setup your service:

```php
$apiKey = 'your api key';
$storageId = 'your storage id';

$fileJet = new FileJet\FileJet(
    new FileJet\HttpClient(),
    new FileJet\Config($apiKey, $storageId)
);
```

The `FileJet\FileJet` class provides 4 public methods which can be used for generating URL for public files (supports on the fly mutations for images), fetching signed URL for private files, generating signed instructions for uploading the file to FileJet and deleting the files from FileJet by providing the file identifier.

### `uploadFile(UploadRequest $request): UploadInstruction`

This method generates the instructions for uploading the file to FileJet. `FileJet\UploadInstructions` contain file identifier for the file which will be uploaded - you should store this information for further usage (eg. for generating the links or deleting the files). The object also contains upload format object with signed URL for uploading the file, HTTP method which should be used and suitable headers which need to be used within the upload request.

You can either upload your file via some PHP HTTP client (which you already have set up because of FileJet API communication) or as recommended you can upload the file asynchronously via JavaScript. You will use URL, headers and HTTP method from UploadFormat object and body will contain file you want to upload.

Within `FileJet\Messages\UploadRequest` you need to specify mime type of file you want to upload, the accessibility of the file (public or private) eg. if you want the file to be publicly accessible via FileJet CDN (suitable for images) or you want to upload private file with sensitive information (suitable for documents) which will be accessible only for users with the correct Storage ID and API key. The last parameter is optional and it tells FIleJet for how long should the upload link be valid. Default value is 60 seconds.

```php
use FileJet\Messages\UploadRequest;

// get the upload instructions
$uploadInstruction = $fileJet->uploadFile(
    new UploadRequest('image/jpeg', UploadRequest::PUBLIC, 60)
);

// you should persist this string for later usage
$fileIdentifier = $uploadInstruction->getFileIdentifier();
$uploadFormat = $uploadInstruction->getUploadFormat();

$httpClient = new FileJet\HttpClient();
$httpClient->sendRequest(
    $uploadFormat->getRequestMethod(),
    $uploadFormat->getUri(),
    $uploadFormat->getHeaders(),
    $fileContent
);
``` 

For better performance we recommend upload files via JavaScript by fetching the upload format from FileJet API via your backend and provide these information to the frontend. Then you can simply use `fetch`.

```html
<form action="#">
    <input type="file" name="file">
    <button type="submit">Upload</button>
</form>
```

```javascript
const form = document.querySelector('form');

form.addEventListener('submit', event => {
    event.preventDefault();

    const uploadFormat = fetch('path/to/your/endpoint');
    fetch(uploadFormat.uri, {
        method: uploadFormat.requestMethod,
        headers: uploadFormat.headers,
        body: new FormData(form)
    })
});
```

### `getUrl(FileInterface $file): string`

When you upload file with public accessibility eg. you will use `FileJet\Messages\UploadRequest::PUBLIC` while fetching upload format you can access your files via FileJet CDN. This method will generate the publicly accessible link for your files based on your configuration.

Method accepts only argument the object which describes your file. You can use this method with `FileJet\File` object which implements `FileJet\FileInterface`. The object contains file identifier provided by `uploadFile()` method. If you are trying to get link for image you can optionally provide mutation string ([see documentation](https://github.com/filejet/filejet-php/mutators.md)). For SEO purposes you can provide the optional third argument which will append the file URL with your custom name.

```php
$reportUrl = $fileJet->getUrl(
    new FileJet\File(
        'fileIdentifierContainingOnlyCharactersAndDigits', 
        null, 
        'report.pdf'
    )
);

// $reportUrl will contain 'https://res.filejet.io/yourStorageId/fileIdentifierContainingOnlyCharactersAndDigits/report.pdf'

$imageUrl = $fileJet->getUrl(
    new FileJet\File(
        'fileIdentifierContainingOnlyCharactersAndDigits',
        'sz_100_100'
    )
);

// $imageUrl will contain 'https://res.filejet.io/yourStorageId/fileIdentifierContainingOnlyCharactersAndDigits/sz_100_100'

```

### `getPrivateUrl(string $fileId, int $expires): DownloadInstruction`

After uploading file to private storage you can retrieve download link by this method. You just need to provide file identifier as first argument. The second argument specifies the number of seconds when the link is valid eg. its expiration time.

```php
$downloadInstruction = $fileJet->getPrivateUrl('fileIdentifierContainingOnlyCharactersAndDigits', 60);

// $url will contain the download link valid for 60 seconds
$url = $downloadInstruction->getUrl();
```

### `deleteFile(string $fileId): void`

This method will delete file from FileJet. The only argument is file identifier obtained from `uploadFile()` method.
