<?php
declare(strict_types=1);

namespace Deviget\AddProductForUrl\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Deviget\AddProductForUrl\Helper\Data
     */
    protected $_helperData;

    /**
     * Index constructor.
     * @param \Deviget\AddProductForUrl\Helper\Data $data
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        \Deviget\AddProductForUrl\Helper\Data $data,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->_helperData = $data;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_storeManager = $storeManager;
        $this->_cart = $cart;
        $this->_product = $product;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // If disabled module or wrong url → redirect to home and show message "wrong url"
        if ( (!$this->_helperData->isEnabledModule()) or (!$this->_helperData->isUrlValid()) ) {
            $this->messageManager->addError(__('wrong url'));
            return $this->getResponse()->setRedirect('/');
        }
        // If enabled window time and not in window time → redirect to home and show out window time
        if (!$this->_helperData->inWindowTime()) {
            $this->messageManager->addError(__('out window time'));
            return $this->getResponse()->setRedirect('/');
        }

        try {
            $params = [];
            $params['qty'] = '1';//product quantity
            $ids = $this->getRequest()->getParam('id');
            $pIds = explode(',',$ids);
            foreach($pIds as $id) {
                $_product = $this->_product->load($id);
                if ($_product) {
                    $this->_cart->addProduct($_product, $params);
                    $this->_cart->save();
                }
            }
            $this->messageManager->addSuccess(__('Add to cart successfully.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addException($e, __('%1', $e->getMessage()));
            return $this->getResponse()->setRedirect('/');
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('error.'));
            return $this->getResponse()->setRedirect('/');
        }
        /*Redirect to cart page*/
        $store = $this->_storeManager->getStore();
        $this->getResponse()->setRedirect('/checkout/cart/');
    }
}

