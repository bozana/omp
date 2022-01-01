<?php

/**
 * @file api/v1/stats/sushi/StatsSushiHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class StatsSushiHandler
 * @ingroup api_v1_stats
 *
 * @brief Handle API requests for COUNTER R5 SUSHI statistics.
 *
 */


import('lib.pkp.api.v1.stats.sushi.PKPStatsSushiHandler');

class StatsSushiHandler extends PKPStatsSushiHandler
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function getGETDefinitions(): array
    {
        $roles = [];
        return array_merge(
            parent::getGETDefinitions(),
            [
                [
                    'pattern' => $this->getEndpointPattern() . '/reports/tr',
                    'handler' => [$this, 'getReportsTR'],
                    'roles' => $roles
                ],
                [
                    'pattern' => $this->getEndpointPattern() . '/reports/tr_b3',
                    'handler' => [$this, 'getReportsTRB3'],
                    'roles' => $roles
                ],
            ]
        );
    }

    public function getReportsTR(\Slim\Http\Request $slimRequest, \PKP\core\APIResponse $response, array $args): \PKP\core\APIResponse
    {
        $args['report'] = new \APP\sushi\TR();
        return $this->getReport($slimRequest, $response, $args);
    }

    public function getReportsTRB3(\Slim\Http\Request $slimRequest, \PKP\core\APIResponse $response, array $args): \PKP\core\APIResponse
    {
        $args['report'] = new \APP\sushi\TR_B3();
        return $this->getReport($slimRequest, $response, $args);
    }

    protected function getReportList(): array
    {
        return array_merge(parent::getReportList(), [
            [
                'Report_Name' => 'Title Master Report',
                'Report_ID' => 'TR',
                'Release' => '5',
                'Report_Description' => __('sushi.reports.tr.description'),
                'Path' => 'reports/tr'
            ],
            [
                'Report_Name' => 'Book Usage by Access Type',
                'Report_ID' => 'TR_B3',
                'Release' => '5',
                'Report_Description' => __('sushi.reports.tr_b3.description'),
                'Path' => 'reports/tr_b3'
            ],
        ]);
    }
}
