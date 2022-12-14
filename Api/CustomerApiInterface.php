<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface CustomerApiInterface
{
    /**
     * @param string $scopeType
     * @param int $scopeId
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @param bool $anonymous
     * @return \Ortto\Connector\Api\Data\ListCustomerResponseInterface
     */
    public function list(
        string $scopeType,
        int $scopeId,
        int $page = 1,
        string $checkpoint = '',
        int $pageSize = 100,
        bool $anonymous = false
    );
}
