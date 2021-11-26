# FileJet PHP library

Provides PHP wrapper for communication with FileJet API.

For integration with Symfony project visit [filejet/filejet-bundle](https://github.com/filejet/filejet-bundle) repository.

## Installation

You can install FileJet PHP library easily via [Composer](https://getcomposer.org/):

```bash
composer require filejet/filejet-php ^2.3
```

## Usage

You need Storage ID and API key to be able to communicate with FileJet API. You can find those at [filejet.io](https://filejet.io) after registration.

FileJet PHP library internally calls FileJet API via HttpClient. We have used for our implementation [HTTPlug](http://docs.php-http.org/en/latest/httplug/introduction.html) project so you can use any HTTP client you want. You can easily use it with `php-http/guzzle6-adapter` by installing it via Composer (see below) or you can follow [HTTPlug tutorial](http://docs.php-http.org/en/latest/httplug/tutorial.html) for registering any HTTP client you choose.

```bash
composer require php-http/guzzle6-adapter ^1.1
composer require php-http/message ^1.6
```

Setup your service:

```php
$apiKey = 'your api key';
$storageId = 'your storage id';
$signatureSecret = 'your signature secret';
$autoMode = true;

$fileJet = new FileJet\FileJet(
    new FileJet\HttpClient(),
    new FileJet\Config($apiKey, $storageId, $signatureSecret, $autoMode),
    new FileJet\Mutation()
);
```

The `FileJet\FileJet` class provides few public methods which can be used for generating URL for public files (supports on the fly mutations for images), fetching signed URL for private files, generating signed instructions for uploading the file to FileJet and deleting the files from FileJet by providing the file identifier.

### `uploadFile(UploadRequest $request): UploadInstruction`

This method generates the instructions for uploading the file to FileJet. `FileJet\UploadInstructions` contain file identifier for the file which will be uploaded - you should store this information for further usage (eg. for generating the links or deleting the files). The object also contains upload format object with signed URL for uploading the file, HTTP method which should be used and suitable headers which need to be used within the upload request.

You can either upload your file via some PHP HTTP client (which you already have set up because of FileJet API communication) or as recommended you can upload the file asynchronously via JavaScript. You will use URL, headers and HTTP method from UploadFormat object and body will contain file you want to upload.

Within `FileJet\Messages\UploadRequest` you need to specify mime type of file you want to upload, the accessibility of the file (public or private) eg. if you want the file to be publicly accessible via FileJet CDN (suitable for images) or you want to upload private file with sensitive information (suitable for documents) which will be accessible only for users with the correct Storage ID and API key. The last parameter is optional and it tells FIleJet for how long should the upload link be valid. Default value is 60 seconds.

```php
use FileJet\Messages\UploadRequest;

// get the upload instructions
$uploadInstruction = $fileJet->uploadFile(
    new UploadRequest('image/jpeg', UploadRequest::PUBLIC_ACCESS, 60)
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
<form action="#" id="form">
    <input type="file" name="file" id="file">
    <button type="submit">Upload</button>
</form>
```

```javascript
const form = document.getElementById('form');
const input = document.getElementById('file');

form.addEventListener('submit', event => {
    event.preventDefault();

    const uploadFormat = fetch('path/to/your/endpoint');
    fetch(uploadFormat.uri, {
        method: uploadFormat.requestMethod,
        headers: uploadFormat.headers,
        body: input.files[0]
    })
});
```

### `bulkUploadFiles(UploadRequest[] $requests): UploadInstruction[]`

This method is useful when you want tu upload multiple files. It works exact the same like `uploadFile()` but is more efficient than calling multiple times `uploadFile()`.

The result `UploadInstruction[]` is ordered in the same order as the input `UploadRequest[]`. Default php array keys are used.

```php
use FileJet\Messages\UploadRequest;

// get the upload instructions
$uploadInstructions = $fileJet->bulkUploadFiles(
    [
        new UploadRequest('image/jpeg', UploadRequest::PUBLIC_ACCESS, 60),
        new UploadRequest('image/jpeg', UploadRequest::PUBLIC_ACCESS, 60),
        new UploadRequest('image/jpeg', UploadRequest::PUBLIC_ACCESS, 60),
    ]
);

foreach ($uploadInstructions as $uploadInstruction) {
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
}
``` 

### `getUrl(FileInterface $file): string`

When you upload file with public accessibility eg. you will use `FileJet\Messages\UploadRequest::PUBLIC_ACCESS` while fetching upload format you can access your files via FileJet CDN. This method will generate the publicly accessible link for your files based on your configuration.

Method accepts only argument the object which describes your file. You can use this method with `FileJet\File` object which implements `FileJet\FileInterface`. The object contains file identifier provided by `uploadFile()` method. If you are trying to get link for image you can optionally provide mutation string ([see documentation](https://github.com/filejet/filejet-php/blob/master/mutators.md)). For SEO purposes you can provide the optional third argument which will append the file URL with your custom name.

```php
$reportUrl = $fileJet->getUrl(
    new FileJet\File(
        'fileIdentifierContainingOnlyCharactersAndDigits', 
        null, 
        'report.pdf'
    )
);

// $reportUrl will contain 'https://yourStorageId.5gcdn.net/fileIdentifierContainingOnlyCharactersAndDigits/report.pdf'

$imageUrl = $fileJet->getUrl(
    new FileJet\File(
        'fileIdentifierContainingOnlyCharactersAndDigits',
        'sz_100_100'
    )
);

// $imageUrl will contain 'https://yourStorageId.5gcdn.net/fileIdentifierContainingOnlyCharactersAndDigits/sz_100_100'

```

### `getPrivateUrl(string $fileId, int $expires): DownloadInstruction`

After uploading file to private storage you can retrieve download link by this method. You just need to provide file identifier as first argument. The second argument specifies the number of seconds when the link is valid eg. its expiration time.

```php
$downloadInstruction = $fileJet->getPrivateUrl('fileIdentifierContainingOnlyCharactersAndDigits', 60);

// $url will contain the download link valid for 60 seconds
$url = $downloadInstruction->getUrl();
```

### `getExternalUrl(string $url, string $mutation)`

You don't need to upload files through FileJet service in order to use all of its functionality, You can use all mutations with your own images.

Simply use this method for your publicly accessible images with use of FileJet mutations. In order for this method to work you will need either add the domain from which you are serving your images to the whitelist or you can provide `signatureSecret` to your configuration.

You can manage the whitelist and your `signatureSecret` at https://app.filejet.io

### `deleteFile(string $fileId): void`

This method will delete file from FileJet. The only argument is file identifier obtained from `uploadFile()` method.


## Auto optimization mode

You can use our intelligent auto optimization mode by simply activate it globally by setting a third argument of  `FileJet\Config` to `true`. This will append every public URL with `auto` mutation which is responsible for providing most optimized version of your image to your clients.

If you don't want to use auto mode globally you can append at any time to your images by simply providing `auto` mutation string.

If you are using auto optimization mode globally and you for any reason want to disable it for specific images, you can disable it per image by providing `auto=false` mutation.
