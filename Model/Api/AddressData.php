<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Model\Api;

use Autopilot\AP3Connector\Logger\Logger;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderAddressInterface;

class AddressData
{
    private Logger $logger;
    private CountryInformationAcquirerInterface $countryRepository;

    public function __construct(Logger $logger, CountryInformationAcquirerInterface $countryRepository)
    {
        $this->logger = $logger;
        $this->countryRepository = $countryRepository;
    }

    /**
     * @param AddressInterface|OrderAddressInterface $address
     * @return array
     */
    public function toArray($address): array
    {
        $data = [
            'city' => (string)$address->getCity(),
            'street_lines' => $address->getStreet(),
            'post_code' => (string)$address->getPostcode(),
            'prefix' => (string)$address->getPrefix(),
            'first_name' => (string)$address->getFirstname(),
            'middle_name' => (string)$address->getMiddlename(),
            'last_name' => (string)$address->getLastname(),
            'suffix' => (string)$address->getSuffix(),
            'company' => (string)$address->getCompany(),
            'vat' => (string)$address->getVatId(),
            'phone' => (string)$address->getTelephone(),
            'fax' => (string)$address->getFax(),
        ];

        $region = $address->getRegion();
        if ($region instanceof RegionInterface) {
            $data['region'] = [
                'code' => (string)$region->getRegionCode(),
                'name' => (string)$region->getRegion(),
            ];
        }

        try {
            $country = $this->countryRepository->getCountryInfo($address->getCountryId());
            if (!empty($country)) {
                $data['country'] = [
                    'name_en' => (string)$country->getFullNameEnglish(),
                    'name_local' => (string)$country->getFullNameLocale(),
                    'abbr2' => (string)$country->getTwoLetterAbbreviation(),
                    'abbr3' => (string)$country->getThreeLetterAbbreviation(),
                ];
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e, 'Failed to fetch country details');
        }

        return $data;
    }
}