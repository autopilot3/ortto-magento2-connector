<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface OrttoOrderRepositoryInterface
{
    /**
     * @param ConfigScopeInterface $scope
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @param array $data
     * @return \Ortto\Connector\Api\Data\ListOrderResponseInterface
     */
    public function getList(
        ConfigScopeInterface $scope,
        int $page,
        string $checkpoint,
        int $pageSize,
        array $data = []
    );
}
