<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsGst
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsGst\Setup;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Api\AddressMetadataInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    const ADDRESS_INDICATOR_TYPE = 'gstin_number';

    protected $regionCollection;

    protected $resourceConnection;

    protected $eavSetupFactory;

    protected $eavConfig;

    public function __construct(
    	\Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollection,
    	\Magento\Framework\App\ResourceConnection $resourceConnection,
    	\Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
    	\Magento\Eav\Model\Config $eavConfig
    ) {
    	$this->regionCollection = $regionCollection;
    	$this->resourceConnection = $resourceConnection;
    	$this->eavSetupFactory = $eavSetupFactory;
    	$this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
       $installer =  $setup->startSetup();
        $connection = $setup->getConnection();
        $regionCollection = $this->regionCollection->create()->addFieldToFilter('country_id','IN');
        if(count($regionCollection->getData())<=0){
        	$country_code = 'IN'; // specify country code for new regions
        	$locale = 'en_IN'; // specify locale
	        $new_regions = array(

	        		'AN'=>"Andaman and Nicobar",
	        		'AP'=>"Andhra Pradesh",
	        		'AR'=>"Arunachal Pradesh",
	        		'AS'=>"Assam",
	        		'BH'=>"Bihar",
	        		'CH'=>"Chandigarh",
	        		'CT'=>"Chhattisgarh",
	        		'DN'=>"Dadra and Nagar Haveli",
	        		'DD'=>"Daman and Diu",
	        		'DL'=>"Delhi",
	        		'GA'=>"Goa",
	        		'GJ'=>"Gujarat",
	        		'HR'=>"Haryana",
	        		'HP'=>"Himachal Pradesh",
	        		'JK'=>"Jammu Kashmir",
	        		'JH'=>"Jharkhand",
	        		'KA'=>"Karnataka",
	        		'KL'=>"Kerala",
	        		'LD'=>"Lakshadweep",
	        		'MP'=>"Madhya Pradesh",
	        		'MH'=>"Maharashtra",
	        		'MN'=>'Manipur',
	        		'ML'=>"Meghalaya",
	        		'MZ'=>"Mizoram",
	        		'NL'=>"Nagaland",
	        		'OR'=>"Odisha",
	        		'PY'=>"Pondicherry",
	        		'PB'=>"Punjab",
	        		'RJ'=>"Rajasthan",
	        		'SK'=>"Sikkim",
	        		'TN'=>"Tamil Nadu",
	        		'TG'=>"Telangana",
	        		'TR'=>"Tripura",
	        		'UP'=>"Uttar Pradesh",
	        		'UT'=>"Uttarakhand",
	        		'WB'=>"West Bengal"
	        );

	        $resource = $this->resourceConnection->getConnection();
	        foreach ($new_regions as $region_code => $region_name) {
		        $sql = "INSERT INTO `{$installer->getTable('directory_country_region')}` (`region_id`,`country_id`,`code`,`default_name`) VALUES (NULL,?,?,?)";

		        $resource->query($sql,array($country_code,$region_code,$region_name));
		        $region_id = $resource->lastInsertId();

		        ;
		        $sql = "INSERT INTO `{$installer->getTable('directory_country_region_name')}` (`locale`,`region_id`,`name`) VALUES (?,?,?)";
		        $resource->query($sql,array($locale,$region_id,$region_name));
	        }
        }


        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            self::ADDRESS_INDICATOR_TYPE,
            [
                'type'         => 'text',
                'label'        => 'GSTIN Number',
                'input'        => 'text',
                'required'     => false,
                'visible'      => true,
                'user_defined' => true,
                'position'     => 999,
                'system'       => 0,
                'default' => ''
            ]
        );

        $attrSetId = $this->eavConfig->getEntityType('customer_address')->getDefaultAttributeSetId();
        $eavSetup->addAttributeToSet('customer_address', $attrSetId, 'General', 'gstin_number');

        $addressAttribute = $this->eavConfig->getAttribute(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            self::ADDRESS_INDICATOR_TYPE
        );
        $addressAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer_address', 'customer_address_edit', 'customer_register_address']
        );
        $addressAttribute->save();
        $setup->endSetup();
    }
}
