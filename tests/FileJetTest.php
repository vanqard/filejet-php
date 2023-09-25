<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FileJet\FileJet;
use FileJet\File;
use FileJet\HttpClient;
use FileJet\Config;
use FileJet\Mutation;
use FileJet\FileInterface;

final class FileJetTest extends TestCase
{
    private const API_KEY = 'apiKey';
    private const STORAGE_ID = 'storageId';
    private const AUTO_MODE = true;

    /** @var FileJet */
    private FileJet $fileJet;

    /** @var FileInterface */
    private FileInterface $file;

    /** @var FileInterface */
    private FileInterface $mutatedFile;

    /** @var Mutation */
    private Mutation $mutationInstance;

    public function testConversionToMutation(): void
    {
        $mutation = 'newMutation';
        $this->assertEquals('newMutation', $this->mutationInstance->toMutation($this->file, $mutation));
        $this->assertEquals('mutation,newMutation', $this->mutationInstance->toMutation($this->mutatedFile, $mutation));
        $this->assertEquals('', $this->mutationInstance->toMutation($this->file));
        $this->assertEquals('mutation', $this->mutationInstance->toMutation($this->mutatedFile));
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
                    $this->mutationInstance->toMutation($this->file, 'auto=false')
                )
            )
        );
    }

    protected function setUp(): void
    {
        $this->mutationInstance = new Mutation();
        $this->fileJet = new FileJet(
            new HttpClient(),
            new Config(self::API_KEY, self::STORAGE_ID, null, self::AUTO_MODE),
            $this->mutationInstance
        );

        $this->file = new File('identifier');
        $this->mutatedFile = new File('mutatedIdentifier', 'mutation');
    }
}
