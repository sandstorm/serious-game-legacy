<?php

namespace MyVendor\AwesomeNeosProject\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\ImageVariant;
use Neos\Media\Domain\Repository\AssetRepository;

class AssetTitleManager
{

    /**
     * @Flow\Inject
     * @var AssetRepository
     */
    protected $assetRepository;

    /**
     * @param AssetInterface $asset
     * @throws IllegalObjectTypeException
     */
    public function addTitleToImportedAsset(AssetInterface $asset): void
    {
        if ($asset instanceof ImageVariant) {
            return;
        }
        $currentTitle = $asset->getTitle();
        if(strlen($currentTitle) === 0) {
            $fileName = $asset->getResource()->getFilename();
            /**
             * @var string $fileName
             */
            $fileName = preg_replace('/.[^.]*$/', '', $fileName);
            $fileName = str_replace(array('-', '_'), ' ', $fileName);
            $asset->setTitle(ucwords($fileName));
            $this->assetRepository->update($asset);
        }
    }
}
