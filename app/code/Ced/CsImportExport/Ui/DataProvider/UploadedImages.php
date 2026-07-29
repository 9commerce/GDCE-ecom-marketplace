<?php

namespace Ced\CsImportExport\Ui\DataProvider;

use Ced\CsImportExport\Block\Export\Image;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class UploadedImages
 * @package Ced\CsImportExport\Ui\DataProvider
 */
class UploadedImages extends AbstractDataProvider
{

    /**
     * @var Image
     */
    protected $image;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactoryObj;
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param Image $image
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactoryObj
     * @param Filesystem $filesystem
     * @param array $meta
     * @param array $data
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        Image $image,
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactoryObj,
        Filesystem $filesystem,
        array $meta = [],
        array $data = []
    ) {
        $this->image = $image;
        $this->filesystem = $filesystem;
        $this->collectionFactoryObj = $collectionFactoryObj;
        $item = $this->image->read();
        $this->collection = $this->createData($item);
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @param $data
     * @return \Magento\Framework\Data\Collection
     * @throws \Exception
     */
    public function createData($data)
    {
        $collection = $this->collectionFactoryObj->create();
        $mediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath('import/' . $this->image->VendorId() . '/');
        if ($data) {
            foreach ($data as $key => $item) {
                $varienObject = new DataObject();
                $tmpArray = [];
                $imageName = explode("/", $item);
                $str = substr(end($imageName), 0, 2);
                $imagestring = substr($str, 0, 1) . '/' . substr($str, 1, 2);
                $imagestring = $imagestring . '/' . end($imageName);

                $tmpArray['id'] = $key;
                $tmpArray['image_id'] = $mediaPath . $imagestring;
                $tmpArray['image_name'] = '/'.$imagestring;
                $tmpArray['image_url'] = $item;

                $varienObject->setData($tmpArray);
                $collection->addItem($varienObject);
            }
        }

        return $collection;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $pageSize = $this->collection->getPageSize();
        $currentPage = $this->collection->getCurPage();
        $filteredArray = array_slice(
            $this->collection->toArray()['items'],
            $pageSize*($currentPage-1),
            $pageSize*$currentPage
        );

        return [
            'items' => $filteredArray,
            'totalRecords' => $this->collection->getSize()
        ];
    }
}
