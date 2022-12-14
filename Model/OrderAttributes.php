<?php

namespace Ortto\Connector\Model;

use Ortto\Connector\Api\Data\OrderAttributesInterface;
use Ortto\Connector\Model\ResourceModel\OrderAttributes as ResourceModel;
use DateTime;
use Magento\Framework\Model\AbstractModel;

class OrderAttributes extends AbstractModel implements OrderAttributesInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'ortto_order_attributes_model';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritdoc
     */
    public function getCanceledAt()
    {
        return $this->getData(OrderAttributesInterface::CANCELED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCanceledAt(DateTime $dateTime)
    {
        $this->setData(OrderAttributesInterface::CANCELED_AT, $dateTime);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCompletedAt()
    {
        return $this->getData(OrderAttributesInterface::COMPLETED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCompletedAt(DateTime $dateTime)
    {
        $this->setData(OrderAttributesInterface::COMPLETED_AT, $dateTime);
        return $this;
    }
}
