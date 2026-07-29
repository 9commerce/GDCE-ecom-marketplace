<?php


namespace Ced\CsGst\Model\TaxClass\Source;
use Magento\Framework\DB\Ddl\Table;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\ClassModel;
class Product extends \Magento\Tax\Model\TaxClass\Source\Product

{
	/**
	 * @var \Magento\Tax\Api\TaxClassRepositoryInterface
	 */
	protected $_taxClassRepository;

	/**
	 * @var \Magento\Framework\Api\SearchCriteriaBuilder
	 */
	protected $_searchCriteriaBuilder;

	/**
	 * @var \Magento\Framework\Api\FilterBuilder
	 */
	protected $_filterBuilder;

	/**
	 * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory
	 */
	protected $_optionFactory;

	protected $_session;

	protected $taxClassCollectionFactory;

    /**
     * Product constructor.
     * @param \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $classesFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $optionFactory
     * @param \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $taxClassCollectionFactory
     */
	public function __construct(
			\Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $classesFactory,
			\Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $optionFactory,
			\Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository,
			\Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
			\Magento\Framework\Api\FilterBuilder $filterBuilder,
			\Magento\Customer\Model\Session $session,
			\Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $taxClassCollectionFactory
	)
    {
		$this->_session = $session;
		$this->taxClassCollectionFactory = $taxClassCollectionFactory;
		parent::__construct($classesFactory, $optionFactory, $taxClassRepository, $searchCriteriaBuilder,$filterBuilder);
	}

    /**
     * Get all options
     *
     * @return array
     */

	public function getAllOptions($withEmpty = true)
	{
		if (!$this->_options) {

				$this->_options = $this->taxClassCollectionFactory->create()
    	 		->addFieldToFilter('class_type', \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT)
    	 		->addFieldToFilter('class_name', ['nlike' => "%GST%"])->load()->toOptionArray();

		}

		if ($withEmpty) {
			if (!$this->_options) {
				return [['value' => '0', 'label' => __('None')]];
			} else {
				return array_merge([['value' => '0', 'label' => __('None')]], $this->_options);
			}
		}

		return $this->_options;
	}
}
