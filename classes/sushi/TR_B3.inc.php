<?php

/**
* @file classes/sushi/TR_B3.inc.php
*
* Copyright (c) 2013-2021 Simon Fraser University
* Copyright (c) 2003-2021 John Willinsky
* Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
*
* @class TR_B3
* @ingroup sushi
*
* @brief COUNTER R5 SUSHI Book Usage by Access Type Report (TR_B3).
*
*/

namespace APP\sushi;

class TR_B3 extends TR
{
    /**
     * Get report name defined by COUNTER.
     */
    public function getName(): string
    {
        return 'Book Usage by Access Type';
    }

    /**
     * Get report ID defined by COUNTER.
     */
    public function getID(): string
    {
        return 'TR_B3';
    }

    /**
     * Get report description.
     */
    public function getDescription(): string
    {
        return __('sushi.reports.tr_b3.description');
    }

    /**
     * Get API path defined by COUNTER for this report.
     */
    public function getAPIPath(): string
    {
        return 'reports/tr_b3';
    }

    /**
     * Get request parameters supported by this report.
     */
    public function getSupportedParams(): array
    {
        return ['customer_id', 'begin_date', 'end_date', 'platform'];
    }

    /**
     * Get filters supported by this report.
     */
    public function getSupportedFilters(): array
    {
        return [];
    }

    /**
     * Get attributes supported by this report.
     */
    public function getSupportedAttributes(): array
    {
        return [];
    }

    /**
     * Set filters based on the requested parameters.
     */
    public function setFilters(array $filters): void
    {
        foreach ($filters as $filter) {
            switch ($filter['Name']) {
                case 'Begin_Date':
                    $this->beginDate = $filter['Value'];
                    break;
                case 'End_Date':
                    $this->endDate = $filter['Value'];
                    break;
            }
        }
        // The filters predefined for this report
        $predefinedFilters = [
            ['Name' => 'Metric_Type', 'Value' => 'Total_Item_Investigations|Unique_Item_Investigations|Total_Item_Requests|Unique_Item_Requests|Unique_Title_Investigations|Unique_Title_Requests'],
            ['Name' => 'Access_Method', 'Value' => self::ACCESS_METHOD],
            ['Name' => 'Data_Type', 'Value' => self::DATA_TYPE],
        ];
        $this->filters = array_merge($filters, $predefinedFilters);
    }

    /**
     * Set attributes based on the requested parameters.
     * No attributes are supported by this report.
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = [];
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\sushi\TR_B3', '\TR_B3');
}
