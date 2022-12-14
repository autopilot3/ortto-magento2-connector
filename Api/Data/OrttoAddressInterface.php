<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoAddressInterface
{
    const TYPE = 'type';
    const CITY = 'city';
    const COMPANY = 'company';
    const COUNTRY_CODE = 'country_code';
    const COUNTRY_NAME = 'country_name';
    const FIRST_NAME = 'first_name';
    const LAST_NAME = 'last_name';
    const MIDDLE_NAME = 'middle_name';
    const POST_CODE = 'post_code';
    const PREFIX = 'prefix';
    const SUFFIX = 'suffix';
    const REGION = 'region';
    const STREET_LINES = 'street_lines';
    const VAT = 'vat';
    const PHONE = 'phone';
    const FAX = 'fax';

    /**
    * Set type
    *
    * @param string $type
    * @return $this
    */
    public function setType($type);

    /**
    * Get type
    *
    * @return string
    */
    public function getType();

    /**
    * Set city
    *
    * @param string $city
    * @return $this
    */
    public function setCity($city);

    /**
    * Get city
    *
    * @return string
    */
    public function getCity();

    /**
    * Set company
    *
    * @param string $company
    * @return $this
    */
    public function setCompany($company);

    /**
    * Get company
    *
    * @return string
    */
    public function getCompany();

    /**
    * Set country code
    *
    * @param string $countryCode
    * @return $this
    */
    public function setCountryCode($countryCode);

    /**
    * Get country code
    *
    * @return string
    */
    public function getCountryCode();

    /**
    * Set country name
    *
    * @param string $countryName
    * @return $this
    */
    public function setCountryName($countryName);

    /**
    * Get country name
    *
    * @return string
    */
    public function getCountryName();

    /**
    * Set first name
    *
    * @param string $firstName
    * @return $this
    */
    public function setFirstName($firstName);

    /**
    * Get first name
    *
    * @return string
    */
    public function getFirstName();

    /**
    * Set last name
    *
    * @param string $lastName
    * @return $this
    */
    public function setLastName($lastName);

    /**
    * Get last name
    *
    * @return string
    */
    public function getLastName();

    /**
    * Set middle name
    *
    * @param string $middleName
    * @return $this
    */
    public function setMiddleName($middleName);

    /**
    * Get middle name
    *
    * @return string
    */
    public function getMiddleName();

    /**
    * Set post code
    *
    * @param string $postCode
    * @return $this
    */
    public function setPostCode($postCode);

    /**
    * Get post code
    *
    * @return string
    */
    public function getPostCode();

    /**
    * Set prefix
    *
    * @param string $prefix
    * @return $this
    */
    public function setPrefix($prefix);

    /**
    * Get prefix
    *
    * @return string
    */
    public function getPrefix();

    /**
    * Set suffix
    *
    * @param string $suffix
    * @return $this
    */
    public function setSuffix($suffix);

    /**
    * Get suffix
    *
    * @return string
    */
    public function getSuffix();

    /**
    * Set region
    *
    * @param string $region
    * @return $this
    */
    public function setRegion($region);

    /**
    * Get region
    *
    * @return string
    */
    public function getRegion();

    /**
    * Set street lines
    *
    * @param string[] $streetLines
    * @return $this
    */
    public function setStreetLines(array $streetLines);

    /**
    * Get street lines
    *
    * @return string[]
    */
    public function getStreetLines(): array;

    /**
    * Set vat
    *
    * @param string $vat
    * @return $this
    */
    public function setVat($vat);

    /**
    * Get vat
    *
    * @return string
    */
    public function getVat();

    /**
    * Set phone
    *
    * @param string $phone
    * @return $this
    */
    public function setPhone($phone);

    /**
    * Get phone
    *
    * @return string
    */
    public function getPhone();

    /**
    * Set fax
    *
    * @param string $fax
    * @return $this
    */
    public function setFax($fax);

    /**
    * Get fax
    *
    * @return string
    */
    public function getFax();

    /**
    * Convert object data to array
    *
    * @return array
    */
    public function serializeToArray();
}
