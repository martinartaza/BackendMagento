<?php


namespace Deviget\AddProductForUrl\Helper;


use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ENABLED = 'product_to_cart/general/enabled';

    const ONLY_WINDOW = 'product_to_cart/general/only_window';

    const START_WINDOW = 'product_to_cart/general/start';

    const FINISH_WINDOW = 'product_to_cart/general/finish';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    protected $_timeZone;

    /**
     * Data constructor.
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timeZone
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timeZone,
        Context $context
    )
    {
        $this->_timeZone = $timeZone;
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }

    /**
     * Check if url is valid
     * @return bool
     */
    public function isUrlValid()
    {
        $params = $this->_getRequest();
        $countParamas = count($params->getParams());
        if ($countParamas != 1) {
            return false;
        }
        $id= $params->getParam('id');
        // Only number [0-9] and ','
        return (bool)preg_match('/^[0-9,]+$/', $id);
    }

    /**
     * Get config enable Module
     * @return bool
     */
    public function isEnabledModule()
    {
        return (bool)$this->getConfig(self::ENABLED);
    }

    /**
     * Get config is window time enable and check is in window
     * @return bool
     */
    public function inWindowTime()
    {
        $inWindow = false;
        $onlyWindow = (bool)$this->getConfig(self::ONLY_WINDOW);
        //if config disabled window return true
        if ($onlyWindow === false ) {
            return true;
        }
        $startTime = $this->getConfig(self::START_WINDOW);
        $finishTime = $this->getConfig(self::FINISH_WINDOW);
        return ($this->_timeZone->date($startTime) < $this->_timeZone->date())
            && ($this->_timeZone->date() < $this->_timeZone->date($finishTime));
    }

    /**
     * Get config to path
     * @param $config_path
     * @return mixed
     */
    public function getConfig($config_path)
    {
        return $this->_scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
