<?php


namespace Ced\CsGst\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory /* For Attribute create  */;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Ced\CsMarketplace\Setup\CsMarketplaceSetupFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    public $_objectManager;
    private $csmarketplaceSetupFactory;

    protected $taxClassModel;

    protected $state;

    protected $taxCalculationRate;

    protected $taxCalculationRule;

    protected $vendorForm;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
    	EavSetupFactory $eavSetupFactory,
        CsMarketplaceSetupFactory $csmarketplaceSetupFactory,
        \Magento\Framework\App\State $state,
        \Magento\Tax\Model\ClassModelFactory $taxClassModel,
        \Magento\Tax\Model\Calculation\RateFactory $taxCalculationRate,
        \Magento\Tax\Model\Calculation\RuleFactory $taxCalculationRule,
        \Ced\CsMarketplace\Model\Vendor\Form $vendorForm
	){
        $this->csmarketplaceSetupFactory = $csmarketplaceSetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->taxClassModel = $taxClassModel;
        $this->taxCalculationRate = $taxCalculationRate;
        $this->taxCalculationRule = $taxCalculationRule;
        $this->vendorForm = $vendorForm;
    }





    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        //die('--here check');
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $setup->startSetup();
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'hsn',/* Custom Attribute Code */
            [
                'group' => 'Product Details',/* Group name in which you want
                                              to display your custom attribute */
                'type' => 'varchar',/* Data type in which formate your value save in database*/
                'backend' => '',
                'frontend' => '',
                'label' => 'HSN Code', /* lablel of your attribute*/
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                                    /*Scope of your attribute */
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'gst_rate',/* Custom Attribute Code */
            [
                'group' => 'Product Details',/* Group name in which you want
                                              to display your custom attribute */
                'type' => 'varchar',/* Data type in which formate your value save in database*/
                'backend' => '',
                'frontend' => '',
                'label' => 'GST Rate', /* lablel of your attribute*/
                'input' => 'select',
                'class' => '',
                'source' => 'Ced\CsGst\Model\Config\Source\Rate',
                                /* Source of your select type custom attribute options*/
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                                    /*Scope of your attribute */
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        );

/*For GST*/
        try {
            $rate_count = 0;
            $class_id = 0;
            $rateId = 0;
            $gstname = '';
            $gst_class = [
                        ['class_name'=>'GST.25','class_type'=>'PRODUCT'],
                        ['class_name'=>'GST3','class_type'=>'PRODUCT'],
                        ['class_name'=>'GST5','class_type'=>'PRODUCT'],
                        ['class_name'=>'GST12','class_type'=>'PRODUCT'],
                        ['class_name'=>'GST18','class_type'=>'PRODUCT'],
                        ['class_name'=>'GST28','class_type'=>'PRODUCT']
                    ];

            $sgst_rate = [
                        '0'=>['code'=>__('SGST.25'),'tax_country_id'=>__('IN'),'zip_is_range'=>0,'tax_postcode'=>__('*'),'rate'=>0.125],
                        '1'=>['code'=>__('SGST3'),'tax_country_id'=>__('IN'),'zip_is_range'=>0,'tax_postcode'=>__('*'),'rate'=>1.5],
                        '2'=>['code'=>__('SGST5'),'tax_country_id'=>__('IN'),'zip_is_range'=>0,'tax_postcode'=>__('*'),'rate'=>2.5],
                        '3'=>['code'=>__('SGST12'),'tax_country_id'=>__('IN'),'zip_is_range'=>0,'tax_postcode'=>__('*'),'rate'=>6],
                        '4'=>['code'=>__('SGST18'),'tax_country_id'=>__('IN'),'zip_is_range'=>0,'tax_postcode'=>__('*'),'rate'=>9],
                        '5'=>['code'=>__('SGST28'),'tax_country_id'=>__('IN'),'zip_is_range'=>0,'tax_postcode'=>__('*'),'rate'=>14]
                    ];

            $cgst_rate = [
                        '0'=>['code'=>__('CGST.25'),'tax_country_id'=>__('IN'),'zip_is_range'=>0,'tax_postcode'=>__('*'),'rate'=>0.125],
                        '1'=>['code'=>__('CGST3'),'tax_country_id'=>__('IN'),'zip_is_range'=>0,'tax_postcode'=>__('*'),'rate'=>1.5],
                        '2'=>['code'=>__('CGST5'),'tax_country_id'=>__('IN'),'zip_is_range'=>0,'tax_postcode'=>__('*'),'rate'=>2.5],
                        '3'=>['code'=>__('CGST12'),'tax_country_id'=>__('IN'),'zip_is_range'=>0,'tax_postcode'=>__('*'),'rate'=>6],
                        '4'=>['code'=>__('CGST18'),'tax_country_id'=>__('IN'),'zip_is_range'=>0,'tax_postcode'=>__('*'),'rate'=>9],
                        '5'=>['code'=>__('CGST28'),'tax_country_id'=>__('IN'),'zip_is_range'=>0,'tax_postcode'=>__('*'),'rate'=>14]
                    ];


            foreach ($gst_class as $gst) {

            	$classModel = $this->taxClassModel->create();
            	$rateModel = $this->taxCalculationRate->create();
            	$ruleModel = $this->taxCalculationRule->create();
                $gstname = $gst['class_name'];

            //tax class
                $classModel->setData($gst)->save();
                $class_id = $classModel->getClassId();

            //tax rate
                $rateModel->setData($sgst_rate[$rate_count])->save();
                $rateId = $rateModel->getTaxCalculationRateId();

                $ruleModel->setData(array('code'=>'S'.$gstname,
                    'customer_tax_class_ids'=>array('0' => 3 ),
                    'product_tax_class_ids'=>array('0' => $class_id ),
                    'tax_rate_ids'=>array('0' => $rateId ),
                    'priority'=>0,
                    'position'=>0
                    ))->save();

                $rateModel->setData($cgst_rate[$rate_count])->save();
                $rateId = $rateModel->getTaxCalculationRateId();

                $ruleModel->setData(array('code'=>'C'.$gstname,
                    'customer_tax_class_ids'=>array('0' => 3 ),
                    'product_tax_class_ids'=>array('0' => $class_id ),
                    'tax_rate_ids'=>array('0' => $rateId ),
                    'priority'=>0,
                    'position'=>0
                    ))->save();
                $rate_count = $rate_count+1;
            }
        }catch (\Exception $e) {
            print_r($e->getMessage());
        }

    /*For IGST*/

        try {
            $irate_count = 0;
            $i_class_id = 0;
            $i_rateId = 0;
            $name = '';
            $igst_class = [
                        ['class_name'=>'IGST.25','class_type'=>'PRODUCT'],
                        ['class_name'=>'IGST3','class_type'=>'PRODUCT'],
                        ['class_name'=>'IGST5','class_type'=>'PRODUCT'],
                        ['class_name'=>'IGST12','class_type'=>'PRODUCT'],
                        ['class_name'=>'IGST18','class_type'=>'PRODUCT'],
                        ['class_name'=>'IGST28','class_type'=>'PRODUCT']
                    ];
            $irate = [
                        '0'=>['code'=>__('IGST.25'),'tax_country_id'=>__('IN'),'zip_is_range'=>0,'tax_postcode'=>__('*'),'rate'=>0.25],
                        '1'=>['code'=>__('IGST3'),'tax_country_id'=>__('IN'),'zip_is_range'=>0,'tax_postcode'=>__('*'),'rate'=>3],
                        '2'=>['code'=>__('IGST5'),'tax_country_id'=>__('IN'),'zip_is_range'=>0,'tax_postcode'=>__('*'),'rate'=>5],
                        '3'=>['code'=>__('IGST12'),'tax_country_id'=>__('IN'),'zip_is_range'=>0,'tax_postcode'=>__('*'),'rate'=>12],
                        '4'=>['code'=>__('IGST18'),'tax_country_id'=>__('IN'),'zip_is_range'=>0,'tax_postcode'=>__('*'),'rate'=>18],
                        '5'=>['code'=>__('IGST28'),'tax_country_id'=>__('IN'),'zip_is_range'=>0,'tax_postcode'=>__('*'),'rate'=>28]
                    ];



            foreach ($igst_class as $value) {
                $i_classModel = $this->taxClassModel->create();
                $i_rateModel = $this->taxCalculationRate->create();
                $i_ruleModel = $this->taxCalculationRule->create();

                $name = $value['class_name'];

            //tax class
                $i_classModel->setData($value)->save();
                $i_class_id = $i_classModel->getClassId();

            //tax rate
                $i_rateModel->setData($irate[$irate_count])->save();
                $i_rateId = $i_rateModel->getTaxCalculationRateId();

            //tax rule
                $i_ruleModel->setData(array('code'=>$name,
                            'customer_tax_class_ids'=>array('0' => 3 ),
                            'product_tax_class_ids'=>array('0' => $i_class_id ),
                            'tax_rate_ids'=>array('0' => $i_rateId),
                            'priority'=>0,
                            'position'=>0
                            ))->save();
                $irate_count = $irate_count+1;
            }
        }catch (\Exception $e) {
            print_r($e->getMessage());
        }


        $csmarketplaceSetup = $this->csmarketplaceSetupFactory->create(['setup' => $setup]);

        $csmarketplaceSetup->installEntities();
        $csmarketplaceSetup->addAttribute('csmarketplace_vendor', 'vendor_gstin', array(
        		'group'			=> 'General Information',
        		'visible'      	=> true,
        		'position'      => 6,
        		'type'          => 'varchar',
        		'default_value'    => 'disabled',
        		'label'         => 'GSTIN No',
        		'input'         => 'text',
        		'required'      => true,
        		'user_defined'  => false,
        ));


        $vendorAttributes = $this->vendorForm->getCollection();

        foreach($vendorAttributes as $vendorAttribute) {
        	$vendorMainAttribute = $this->vendorForm->load($vendorAttribute->getId());

        	if($vendorMainAttribute->getAttributeCode()=='vendor_gstin'){

        		$vendorMainAttribute->setData('use_in_registration',0);
        		$vendorMainAttribute->setData('is_visible',1);
        		$vendorMainAttribute->setData('use_in_left_profile',0);
        		$vendorMainAttribute->save();
        	}
        }

        $setup->endSetup();

    }
}
