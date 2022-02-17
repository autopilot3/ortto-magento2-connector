<?php

namespace Autopilot\AP3Connector\Block\Adminhtml\System\Config;

use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Helper\Config;
use Autopilot\AP3Connector\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class SyncCustomers extends FieldBase
{
    public function __construct(
        Context $context,
        Data $helper,
        ScopeManagerInterface $scopeManager,
        array $data = []
    ) {

        parent::__construct($context, $scopeManager, $helper, $data);
        $this->setTemplate('system/config/sync_products.phtml');
    }

    public function getAjaxURL(): string
    {
        return $this->_escaper->escapeHtmlAttr($this->getUrl(Config::SYNC_CUSTOMERS_ROUTE));
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        $originalData = $element->getOriginalData();
        $label = $originalData['label'];
        $this->addData([
            'button_label' => $label,
        ]);
        return parent::_toHtml();
    }
}