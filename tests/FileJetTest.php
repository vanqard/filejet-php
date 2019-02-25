<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FileJetTest extends TestCase
{
    private const API_KEY = 'apiKey';
    private const STORAGE_ID = 'storageId';
    private const AUTO_MODE = true;

    /** @var \FileJet\FileJet */
    private $fileJet;

    /** @var \FileJet\FileInterface */
    private $file;
    /** @var \FileJet\FileInterface */
    private $mutatedFile;

    public function testConversionToMutation(): void
    {
        $mutation = 'newMutation';
        $this->assertEquals('newMutation', $this->fileJet->toMutation($this->file, $mutation));
        $this->assertEquals('mutation,newMutation', $this->fileJet->toMutation($this->mutatedFile, $mutation));
        $this->assertEquals('', $this->fileJet->toMutation($this->file));
        $this->assertEquals('mutation', $this->fileJet->toMutation($this->mutatedFile));
    }

    public function testUrlGeneration()
    {
        $this->assertEquals(
            'https://storageId.5gcdn.net/identifier/auto',
            $this->fileJet->getUrl($this->file)
        );
        $this->assertEquals(
            // FileJet normalizes file identifier internally with lowercase
            'https://storageId.5gcdn.net/mutatedidentifier/mutation,auto',
            $this->fileJet->getUrl($this->mutatedFile)
        );
        $this->assertEquals(
            'https://storageId.5gcdn.net/identifier',
            $this->fileJet->getUrl(
                new \FileJet\File(
                    $this->file->getIdentifier(),
                    $this->fileJet->toMutation($this->file, 'auto=false')
                )
            )
        );
    }

    protected function setUp()
    {
        \Http\Discovery\HttpClientDiscovery::prependStrategy(\Http\Discovery\Strategy\MockClientStrategy::class);

        $this->fileJet = new FileJet\FileJet(
            new \FileJet\HttpClient(),
            new \FileJet\Config(self::API_KEY, self::STORAGE_ID, self::AUTO_MODE)
        );

        $this->file = new \FileJet\File('identifier');
        $this->mutatedFile = new \FileJet\File('mutatedIdentifier', 'mutation');
    }
}
