<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_CsMarketplace
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
declare(strict_types=1);

namespace Ced\CsMarketplace\Setup\Patch\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class CsMarketplacePatch implements DataPatchInterface
{
    const THEME_NAME = 'Magento/luma';
    const THEME_ID = 'design/theme/theme_id';

    /**
     * @var \Ced\CsMarketplace\Model\Vendor\FormFactory
     */
    public $formFactory;

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    protected $csmarketplaceSetupFactory;

    protected $store;

    protected $collectionFactory;

    protected $config;

    protected $scopeInterface;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Ced\CsMarketplace\Setup\CsMarketplaceSetupFactory $csmarketplaceSetupFactory
     * @param \Ced\CsMarketplace\Model\Vendor\FormFactory $formFactory
     * @param Store $store
     * @param \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $collectionFactory
     * @param \Magento\Theme\Model\Config $config
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param EavSetupFactory $eavSetupFactory
     * @param ScopeConfigInterface $scopeInterface
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Ced\CsMarketplace\Setup\CsMarketplaceSetupFactory $csmarketplaceSetupFactory,
        \Ced\CsMarketplace\Model\Vendor\FormFactory $formFactory,
        \Magento\Store\Model\Store $store,
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $collectionFactory,
        \Magento\Theme\Model\Config $config,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        EavSetupFactory $eavSetupFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->csmarketplaceSetupFactory = $csmarketplaceSetupFactory;
        $this->formFactory = $formFactory;
        $this->store = $store;
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
        $this->blockFactory = $blockFactory;
        $this->_scopeConfig = $scopeInterface;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;
        $csmarketplaceSetup = $this->csmarketplaceSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $csmarketplaceSetup->installEntities();
        $csmarketplaceSetup->addAttribute(
            'csmarketplace_vendor', 'is_verify', array(
                'group' => 'General Information',
                'visible' => true,
                'position' => 100,
                'required' => false,
                'type' => 'varchar',
                'input' => 'label',
                'label' => 'Is Verify',
                'source' => '',
                'user_defined' => false
            )
        );
        $csmarketplaceSetup->addAttribute(
            'csmarketplace_vendor', 'verify_key', array(
                'group' => 'General Information',
                'visible' => true,
                'position' => 110,
                'required' => false,
                'type' => 'varchar',
                'input' => 'label',
                'label' => 'Verify Key',
                'source' => '',
                'user_defined' => false
            )
        );
         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'created_at', array(
                 'group' => 'General Information',
                 'visible' => true,
                 'position' => 1,
                 'required' => false,
                 'type' => 'datetime',
                 'input' => 'label',
                 'label' => 'Created At',
                 'source' => '',
                 'user_defined' => false,
                 'frontend_class' => 'validate-no-html-tags'
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'shop_url', array(
                 'group' => 'General Information',
                 'visible' => true,
                 'position' => 2,
                 'type' => 'varchar',
                 'label' => 'Shop Url',
                 'input' => 'text',
                 'required' => true,
                 'frontend_class' => 'validate-shopurl',
                 'validate_rules' => array(
                     'input_validation' => 'identifier'
                 ),
                 'source' => '',
                 'user_defined' => false
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'status', array(
                 'group' => 'General Information',
                 'visible' => true,
                 'position' => 3,
                 'type' => 'varchar',
                 'label' => 'Status',
                 'input' => 'select',
                 'source' => 'Ced\CsMarketplace\Model\System\Config\Source\Status',
                 'default_value' => 'disabled',
                 'required' => true,
                 'user_defined' => false,
                 'note' => '',
                 'frontend_class' => 'validate-no-html-tags'
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'group', array(
                 'group' => 'General Information',
                 'visible' => true,
                 'position' => 4,
                 'type' => 'varchar',
                 'label' => 'Vendor Group',
                 'input' => 'select',
                 'source' => 'Ced\CsMarketplace\Model\System\Config\Source\Group',
                 'default_value' => 'general',
                 'required' => true,
                 'user_defined' => false,
                 'note' => '',
                 'frontend_class' => 'validate-no-html-tags'
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'public_name', array(
                 'group' => 'General Information',
                 'visible' => true,
                 'position' => 4,
                 'type' => 'varchar',
                 'label' => 'Public Name',
                 'input' => 'text',
                 'required' => true,
                 'user_defined' => false,
                 'frontend_class' => 'validate-no-html-tags'
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor',
             'website_id',
             array(
                 'group' => 'General Information',
                 'label' => 'Website ID',
                 'type' => 'static',
                 'user_defined' => false,
                 'required' => false,

             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'name', array(
                 'group' => 'General Information',
                 'visible' => true,
                 'position' => 5,
                 'type' => 'varchar',
                 'label' => 'Name',
                 'input' => 'text',
                 'required' => true,
                 'user_defined' => false,
                 'frontend_class' => 'validate-no-html-tags'
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'gender', array(
                 'group' => 'General Information',
                 'visible' => true,
                 'position' => 6,
                 'required' => false,
                 'type' => 'int',
                 'input' => 'select',
                 'label' => 'Gender',
                 'source' => 'Ced\CsMarketplace\Model\System\Config\Source\Dob',
                 'user_defined' => false,
                 'frontend_class' => 'validate-no-html-tags'
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'profile_picture', array(
                 'group' => 'General Information',
                 'visible' => true,
                 'position' => 7,
                 'required' => false,
                 'type' => 'varchar',
                 'input' => 'image',
                 'label' => 'Profile Picture',
                 'source' => '',
                 'user_defined' => false,
                 'frontend_class' => 'validate-no-html-tags'
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'email', array(
                 'group' => 'General Information',
                 'visible' => true,
                 'position' => 8,
                 'required' => true,
                 'unique' => true,
                 'type' => 'varchar',
                 'input' => 'text',
                 'source' => '',
                 'label' => 'Email',
                 'frontend_class' => 'validate-email',
                 'validate_rules' => array(
                     'input_validation' => 'email'
                 ),
                 'user_defined' => false
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'contact_number', array(
                 'group' => 'General Information',
                 'visible' => true,
                 'position' => 9,
                 'required' => false,
                 'type' => 'varchar',
                 'input' => 'text',
                 'label' => 'Contact Number',
                 'frontend_class' => 'validate-digits',
                 'source' => '',
                 'user_defined' => false
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'company_name', array(
                 'group' => 'Company Information',
                 'visible' => true,
                 'position' => 10,
                 'required' => false,
                 'type' => 'varchar',
                 'input' => 'text',
                 'label' => 'Company Name',
                 'source' => '',
                 'user_defined' => false,
                 'frontend_class' => 'validate-no-html-tags'
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'about', array(
                 'group' => 'Company Information',
                 'visible' => true,
                 'position' => 11,
                 'required' => false,
                 'type' => 'text',
                 'input' => 'textarea',
                 'label' => 'About',
                 'source' => '',
                 'user_defined' => false
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'company_logo', array(
                 'group' => 'Company Information',
                 'required' => false,
                 'visible' => true,
                 'position' => 12,
                 'type' => 'varchar',
                 'input' => 'image',
                 'label' => 'Company Logo',
                 'source' => '',
                 'user_defined' => false
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'company_banner', array(
                 'group' => 'Company Information',
                 'visible' => true,
                 'position' => 13,
                 'required' => false,
                 'type' => 'varchar',
                 'input' => 'image',
                 'label' => 'Company Banner',
                 'source' => '',
                 'user_defined' => false,
                 'frontend_class' => 'validate-no-html-tags'
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'company_address', array(
                 'group' => 'Company Information',
                 'visible' => true,
                 'position' => 14,
                 'required' => false,
                 'type' => 'text',
                 'input' => 'textarea',
                 'label' => 'Company Address',
                 'source' => '',
                 'user_defined' => false,
                 'frontend_class' => 'validate-no-html-tags'
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'support_number', array(
                 'group' => 'Support Information',
                 'visible' => true,
                 'position' => 15,
                 'required' => false,
                 'type' => 'varchar',
                 'input' => 'text',
                 'label' => 'Support Number',
                 'frontend_class' => 'validate-digits',
                 'source' => '',
                 'user_defined' => false,
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'support_email', array(
                 'group' => 'Support Information',
                 'visible' => true,
                 'position' => 16,
                 'required' => false,
                 'type' => 'varchar',
                 'input' => 'text',
                 'label' => 'Support Email',
                 'frontend_class' => 'validate-email',
                 'source' => '',
                 'user_defined' => false
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'meta_keywords', array(
                 'group' => 'SEO Information',
                 'visible' => true,
                 'position' => 19,
                 'required' => false,
                 'type' => 'text',
                 'input' => 'textarea',
                 'label' => 'Meta Keywords',
                 'source' => '',
                 'user_defined' => false
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'meta_description', array(
                 'group' => 'SEO Information',
                 'visible' => true,
                 'position' => 20,
                 'required' => false,
                 'type' => 'text',
                 'input' => 'textarea',
                 'label' => 'Meta Description',
                 'source' => '',
                 'user_defined' => false
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'facebook_id', array(
                 'group' => 'Support Information',
                 'visible' => true,
                 'position' => 21,
                 'required' => false,
                 'type' => 'varchar',
                 'input' => 'text',
                 'label' => 'Facebook ID',
                 'source' => '',
                 'user_defined' => false
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'twitter_id', array(
                 'group' => 'Support Information',
                 'visible' => true,
                 'position' => 22,
                 'required' => false,
                 'type' => 'varchar',
                 'input' => 'text',
                 'label' => 'Twitter ID',
                 'source' => '',
                 'user_defined' => false
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'instagram_id', [
                 'group' => 'Support Information',
                 'visible' => true,
                 'position' => 23,
                 'required' => false,
                 'type' => 'varchar',
                 'input' => 'text',
                 'label' => 'Instagram ID',
                 'source' => '',
                 'user_defined' => false
             ]
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'pinterest_id', [
                 'group' => 'Support Information',
                 'visible' => true,
                 'position' => 24,
                 'required' => false,
                 'type' => 'varchar',
                 'input' => 'text',
                 'label' => 'Pinterest ID',
                 'source' => '',
                 'user_defined' => false
             ]
         );

         $csmarketplaceSetup->removeAttribute('csmarketplace_vendor', 'address');
         $csmarketplaceSetup->removeAttribute('csmarketplace_vendor', 'city');
         $csmarketplaceSetup->removeAttribute('csmarketplace_vendor', 'zip_code');
         $csmarketplaceSetup->removeAttribute('csmarketplace_vendor', 'region_id');
         $csmarketplaceSetup->removeAttribute('csmarketplace_vendor', 'country_id');
         $csmarketplaceSetup->removeAttribute('csmarketplace_vendor', 'region');

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'address', array(
                 'group' => 'Address Information',
                 'visible' => true,
                 'position' => 25,
                 'required' => true,
                 'type' => 'varchar',
                 'input' => 'text',
                 'label' => 'Address',
                 'source' => '',
                 'user_defined' => false
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'city', array(
                 'group' => 'Address Information',
                 'visible' => true,
                 'position' => 26,
                 'required' => true,
                 'type' => 'varchar',
                 'input' => 'text',
                 'label' => 'City',
                 'source' => '',
                 'user_defined' => false
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'zip_code', array(
                 'group' => 'Address Information',
                 'visible' => true,
                 'position' => 27,
                 'required' => true,
                 'type' => 'int',
                 'input' => 'text',
                 'label' => 'Zip/Postal Code',
                 'source' => '',
                 'user_defined' => false
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'region', array(
                 'group' => 'Address Information',
                 'visible' => true,
                 'position' => 29,
                 'type' => 'varchar',
                 'label' => 'State',
                 'input' => 'text',
                 'source' => '',
                 'required' => false,
                 'user_defined' => false,
                 'note' => ''
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'region_id', array(
                 'group' => 'Address Information',
                 'visible' => true,
                 'position' => 28,
                 'type' => 'int',
                 'label' => 'State',
                 'input' => 'select',
                 'source' => 'Ced\CsMarketplace\Model\Vendor\Address\Source\Region',
                 'required' => false,
                 'user_defined' => false,
                 'note' => ''
             )
         );

         $csmarketplaceSetup->addAttribute(
             'csmarketplace_vendor', 'country_id', array(
                 'group' => 'Address Information',
                 'visible' => true,
                 'position' => 30,
                 'type' => 'varchar',
                 'label' => 'Country',
                 'input' => 'select',
                 'source' => 'Magento\Customer\Model\ResourceModel\Address\Attribute\Source\Country',
                 'required' => true,
                 'user_defined' => false,
                 'note' => ''
             )
         );

        $registrationFormAttributes = ['public_name' => 10, 'shop_url' => 20];
        $profileDisplayAttributes = [
            'public_name' => ['sort_order' => 10, 'fontawesome' => 'fa fa-user', 'store_label' => 'Public Name'],
            'support_number' => ['sort_order' => 20, 'fontawesome' => 'fa fa-mobile', 'store_label' => 'Tel'],
            'support_email' => ['sort_order' => 30, 'fontawesome' => 'fa fa-envelope-o',
                'store_label' => 'Support Email'],
            'email' => ['sort_order' => 35, 'fontawesome' => 'fa fa-envelope-o', 'store_label' => 'Email'],
            'company_name' => ['sort_order' => 40, 'fontawesome' => 'fa fa-building', 'store_label' => 'Company'],
            'name' => ['sort_order' => 50, 'fontawesome' => 'fa fa-user', 'store_label' => 'Representative'],
            'company_address' => ['sort_order' => 60, 'fontawesome' => 'fa fa-location-arrow',
                'store_label' => 'Location'],
            'created_at' => ['sort_order' => 70, 'fontawesome' => 'fa fa-calendar',
                'store_label' => 'Vendor Since'],
            'facebook_id' => ['sort_order' => 80, 'fontawesome' => 'fa fa-facebook-square social',
                'store_label' => 'Find us on Facebook'],
            'twitter_id' => ['sort_order' => 90, 'fontawesome' => 'fa fa-twitter social',
                'store_label' => 'Follow us on Twitter'],
            'instagram_id' => ['sort_order' => 100, 'fontawesome' => 'fa fa-instagram social',
                'store_label' => 'Follow us on Instagram'],
            'pinterest_id' => ['sort_order' => 110, 'fontawesome' => 'fa fa-pinterest-square social',
                'store_label' => 'Find us on Pinterest']
        ];

        $vendorAttributes = $this->formFactory->create()->getCollection();
        if (count($vendorAttributes) > 0) {
            $storesViews = $this->store->getCollection();

            foreach ($vendorAttributes as $vendorAttribute) {
                $vendorMainAttribute = $this->formFactory->create()->load($vendorAttribute->getAttributeId());
                $isSaveNeeded = false;

                if (isset($registrationFormAttributes[$vendorAttribute->getAttributeCode()])) {
                    $vendorAttribute->setData('use_in_registration', 1);
                    $vendorAttribute->setData('position_in_registration',
                        $registrationFormAttributes[$vendorAttribute->getAttributeCode()]);
                    $isSaveNeeded = true;
                }

                if (isset($profileDisplayAttributes[$vendorAttribute->getAttributeCode()])) {
                    $frontend_label[0] = $vendorMainAttribute->getFrontendLabel();

                    foreach ($storesViews as $storesView) {
                        $frontend_label[$storesView->getId()] =
                            $profileDisplayAttributes[$vendorAttribute->getAttributeCode()]['store_label'];
                    }

                    $vendorAttribute->setData('use_in_left_profile', 1);
                    $vendorAttribute->setData('position_in_left_profile',
                        $profileDisplayAttributes[$vendorAttribute->getAttributeCode()]['sort_order']);
                    $vendorAttribute->setData('fontawesome_class_for_left_profile',
                        $profileDisplayAttributes[$vendorAttribute->getAttributeCode()]['fontawesome']);
                    $vendorMainAttribute->setData('frontend_label', $frontend_label);
                    $isSaveNeeded = true;
                }

                if ($isSaveNeeded) {
                    $vendorAttribute->save();
                    $vendorMainAttribute->save();
                    $isSaveNeeded = false;
                }
            }
        }

        /*UpgradeData code*/
        $eavSetup = $csmarketplaceSetup;
        // $vendorAttribute = $this->formFactory->create()->getCollection()
        //     ->addFieldToFilter('attribute_code', 'zip_code')
        //     ->getFirstItem();
        // $vendorAttribute->load($vendorAttribute->getAttributeId())->delete();

        // $eavSetup->removeAttribute('csmarketplace_vendor', 'zip_code');

        // $eavSetup->addAttribute(
        //     'csmarketplace_vendor',
        //     'zip_code',
        //     array(
        //         'group' => 'Address Information',
        //         'label' => 'Zip/Postal Code',
        //         'type' => 'static',
        //         'visible' => true,
        //         'position' => 27,
        //         'user_defined' => false,
        //         'required' => true,

        //     )
        // );

        // $attribute = $eavSetup->getAttribute('csmarketplace_vendor',
        //     'zip_code');
        // $vendorAttribute = $this->formFactory->create();

        // $data = [
        //     'attribute_id' => $attribute['attribute_id'],
        //     'attribute_code' => 'zip_code',
        //     'is_visible' => 1,
        //     'sort_order' => 27,
        // ];
        // $vendorAttribute->setData($data)->save();

        // $eavSetup->addAttribute(
        //     'csmarketplace_vendor', 'reason', array(
        //         'group' => 'General Information',
        //         'visible' => true,
        //         'position' => 4,
        //         'type' => 'varchar',
        //         'label' => 'Disapproval Reason',
        //         'input' => 'textarea',
        //         'required' => false,
        //         'user_defined' => false,

        //     )
        // );

        $ourStoryBlock = $this->blockFactory->create();
        $ourStoryBlock->load('ced-csmarketplace-out-story', 'identifier');
        // phpcs:disable Magento2.Files.LineLength.MaxExceeded
        if (!$ourStoryBlock->getId()) {
            $ourStory = [
                'title' => 'Our Story',
                'identifier' => 'ced-csmarketplace-out-story',
                'content' => '<div class="container">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                                            <div class="story_image"><img class="img-fluid" src="{{view url=Ced_CsMarketplace::images/login_landing_page/story_sec.svg}}" alt="CoolBrand"></div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                                            <div class="story_content">
                                                <h3 class="story_heading">Tell Your Story</h3>
                                                <div class="sub_heading"><strong> We are working to your business Goal </strong></div>
                                                <p class="str_para">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nisi ducimus expedita facilis architecto fugiat veniam natus suscipit amet beatae atque, enim recusandae quos, magnam, perferendis accusamus cumque nemo modi unde!</p>
                                                <p class="str_para">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nisi ducimus expedita facilis architecto fugiat veniam natus suscipit amet beatae atque, enim recusandae quos, magnam, perferendis accusamus cumque nemo modi unde!</p>
                                                <div class="button-set"><button class="btn btn-primary">Read more</button></div>
                                            </div>
                                        </div>
                                    </div>
                                  </div>',
                'stores' => 0,
                'is_active' => 1,
            ];
            $this->blockFactory->create()->setData($ourStory)->save();
        }

        $stepsToRegisterBlock = $this->blockFactory->create();
        $stepsToRegisterBlock->load('ced-csmarketplace-steps-to-register', 'identifier');
        if (!$stepsToRegisterBlock->getId()) {
            $stepsToRegister = [
                'title' => 'Steps to Register',
                'identifier' => 'ced-csmarketplace-steps-to-register',
                'content' => '<div class="container">
                                    <div class="how_get_row">
                                        <div class="how_get_main_wrapper">
                                            <div class="how_get">
                                                <h3 class="ger_ready_h">How to get ready for selling?</h3>
                                                <p class="content">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Laboriosam consectetur excepturi consequuntur nemo dolor fuga commodi</p>
                                                <div class="steps_for_get_ready">
                                                    <div id="get_ready" class="carousel slide" data-ride="carousel">
                                                        <div class="carousel-inner">
                                                            <div class="item active">
                                                                <div class="get_ready_steps">
                                                                    <h4>Register</h4>
                                                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                                                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                                                                </div>
                                                            </div>
                                                            <div class="item">
                                                                <div class="get_ready_steps">
                                                                    <h4>List your product</h4>
                                                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                                                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                                                                </div>
                                                            </div>
                                                            <div class="item">
                                                                <div class="get_ready_steps">
                                                                    <h4>Ship your product</h4>
                                                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                                                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                                                                </div>
                                                            </div>
                                                            <div class="item">
                                                                <div class="get_ready_steps">
                                                                    <h4>Earn money</h4>
                                                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                                                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                                                                </div>
                                                            </div>
                                                            <div class="item">
                                                                <div class="get_ready_steps">
                                                                    <h4>Register now</h4>
                                                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. <a class="btn btn-primary" href="{{store direct_url=csmarketplace/account/register}}">Register now</a></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <ol class="carousel-indicators align_carousel_items">
                                                            <li class="active" data-target="#get_ready" data-slide-to="0">Register</li>
                                                            <li data-target="#get_ready" data-slide-to="1">List Product</li>
                                                            <li data-target="#get_ready" data-slide-to="2">Ship product</li>
                                                            <li data-target="#get_ready" data-slide-to="3">Get earning</li>
                                                            <li data-target="#get_ready" data-slide-to="4">Let\'s go</li>
                                                        </ol>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="how_get_image">
                                            <div class="table">
                                                <div class="table-cell"><img class="img-fluid" src="{{view url=Ced_CsMarketplace::images/login_landing_page/get_ready.png}}" alt="CoolBrand"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">&nbsp;</div>
                                </div>',
                'stores' => 0,
                'is_active' => 1,
            ];
            $this->blockFactory->create()->setData($stepsToRegister)->save();
        }


        $featuresBlock = $this->blockFactory->create();
        $featuresBlock->load('ced-csmarketplace-features', 'identifier');
        if (!$featuresBlock->getId()) {
            $features = [
                'title' => 'Features',
                'identifier' => 'ced-csmarketplace-features',
                'content' => '<div class="container">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="section_title">
                                                <h3>Why you sell in our marketplace?</h3>
                                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Libero optio fugiat dignissimos incidunt</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                            <div class="features_box">
                                                <div class="inner">
                                                    <h3 class="h3_hedaing"><img class="img-fluid icon-image-manage" src="{{view url=Ced_CsMarketplace::images/login_landing_page/dashboard_new.svg}}" alt="">Dashboard</h3>
                                                    <p class="para_all">It will have the block of vendor order history, account related statistics details and summary of sales</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                            <div class="features_box">
                                                <div class="inner">
                                                    <h3 class="h3_hedaing"><img class="img-fluid icon-image-manage" src="{{view url=Ced_CsMarketplace::images/login_landing_page/create_new.svg}}" alt=""> Create Product</h3>
                                                    <p class="para_all">Vendor can create Simple products, manage Qty, Price, create Configurable products with variations and images.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                            <div class="features_box">
                                                <div class="inner">
                                                    <h3 class="h3_hedaing"><img class="img-fluid icon-image-manage" src="{{view url=Ced_CsMarketplace::images/login_landing_page/order_new.svg}}" alt="">Order Management</h3>
                                                    <p class="para_all">Vendor can manage order gird, It can print packing slip and create shipment• It can also cancel order as well.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                            <div class="features_box">
                                                <div class="inner">
                                                    <h3 class="h3_hedaing"><img class="img-fluid icon-image-manage" src="{{view url=Ced_CsMarketplace::images/login_landing_page/report_new.svg}}" alt=""> Reports</h3>
                                                    <p class="para_all">Vendor can review different notifications for new orders, can review reports for product sell, commission, total orders.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                            <div class="features_box">
                                                <div class="inner">
                                                    <h3 class="h3_hedaing"><img class="img-fluid icon-image-manage" src="{{view url=Ced_CsMarketplace::images/login_landing_page/customerpanel_new.svg}}" alt=""> Customer Panel</h3>
                                                    <p class="para_all">Customers will be able to view all the products from all the vendors, can post reviews on all products.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                            <div class="features_box">
                                                <div class="inner">
                                                    <h3 class="h3_hedaing"><img class="img-fluid icon-image-manage" src="{{view url=Ced_CsMarketplace::images/login_landing_page/vendor_manage_new.svg}}" alt=""> Vendor Management</h3>
                                                    <p class="para_all">Admin manages all the vendor accounts and also able to review all the statistics, can edit and approve vendors.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>',
                'stores' => 0,
                'is_active' => 1,
            ];
            $this->blockFactory->create()->setData($features)->save();
        }

        /*START : CMS block for TOC sections*/
        $featuresBlock = $this->blockFactory->create();
        $featuresBlock->load('ced-csmarketplace-seller-toc', 'identifier');
        if (!$featuresBlock->getId()) {
            $features = [
                'title' => 'Seller TOC',
                'identifier' => 'ced-csmarketplace-seller-toc',
                'content' => '<p style="text-align: center;"> <strong>THIS AGREEMENT WITNESSES AS UNDER</strong> </p>
                                   <p style="text-align: center;"> Terms and Conditions </p>',
                'stores' => 0,
                'is_active' => 1,
            ];
            $this->blockFactory->create()->setData($features)->save();
        }
        /*END : CMS block for TOC sections*/
        //phpcs:enable

        $salesOrderTable = $setup->getTable('sales_order');
        $vendorOrderTable = $setup->getTable('ced_csmarketplace_vendor_sales_order');
        if ($setup->getConnection()->isTableExists($vendorOrderTable) &&
            $setup->getConnection()->isTableExists($salesOrderTable)) {
            $query = "UPDATE " . $vendorOrderTable . " vo
                            left join " . $salesOrderTable . " so on so.increment_id = vo.order_id
                            set vo.real_order_id = so.entity_id, vo.real_order_status = so.status";

            $setup->getConnection()->query($query);
        }

        /* Change backend_type from int to varchar */
        // $eavSetup->updateAttribute('csmarketplace_vendor', 'zip_code', 'backend_type', 'varchar');
        /* Change backend_type from int to varchar */
        $connection = $setup->getConnection();

        /* table */
        $ced_csmarketplace_vendor = $setup->getTable('ced_csmarketplace_vendor');
        $ced_csmarketplace_vendor_int = $setup->getTable('ced_csmarketplace_vendor_int');
        $ced_csmarketplace_vendor_varchar = $setup->getTable('ced_csmarketplace_vendor_varchar');
        /* table */
        $query1 = sprintf('SELECT `entity_id` FROM `'.$ced_csmarketplace_vendor.'`');
        $vendorCollection = $connection->rawQuery($query1)->fetchAll();

        $attributrId = $eavSetup->getAttributeId('csmarketplace_vendor', 'zip_code');
        foreach ($vendorCollection as $vendor) {
            $zipCodeInt = $connection->fetchCol("SELECT `value` FROM `".$ced_csmarketplace_vendor_int.
                "` WHERE `attribute_id` = '".$attributrId."' AND `entity_id` = '".$vendor['entity_id']."'");
            if (isset($zipCodeInt[0])) {
                $checkQuery = $connection->fetchAll("SELECT * FROM `".$ced_csmarketplace_vendor_varchar.
                    "` WHERE `attribute_id` = '".$attributrId."' AND `entity_id` = '".$vendor['entity_id']."'");
                if (count($checkQuery)) {
                    //update
                    $query = sprintf('UPDATE %s SET `value` = %s WHERE `attribute_id` = %s AND `entity_id` = %s',
                        $setup->getTable($ced_csmarketplace_vendor_varchar), $zipCodeInt[0], $attributrId ,
                        $vendor['entity_id']);
                } else {
                    //insert
                    $query = sprintf('INSERT INTO %s (`value_id`, `attribute_id`, `entity_id`, `value`)
                        VALUES (NULL, %s, %s, %s)', $setup->getTable($ced_csmarketplace_vendor_varchar) , $attributrId ,
                        $vendor['entity_id'] , $zipCodeInt[0]);
                }
                $connection->rawQuery($query);
            }
        }
        $connection->rawQuery("DELETE FROM `".$ced_csmarketplace_vendor_int."` WHERE `attribute_id` = '".$attributrId."'");

        $this->assignTheme();
    }

    /**
     * Assign Theme
     *
     * @return void
     */
    protected function assignTheme()
    {

        $themeValue = $this->_scopeConfig->getValue(self::THEME_ID, ScopeInterface::SCOPE_STORE);

        if ($themeValue == NULL) {

            $themes = $this->collectionFactory->create()->loadRegisteredThemes();
            /**
             * @var \Magento\Theme\Model\Theme $theme
             */
            foreach ($themes as $theme) {
                if ($theme->getCode() == self::THEME_NAME) {
                    $this->config->assignToStore(
                        $theme,
                        [Store::DEFAULT_STORE_ID],
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                    );
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
