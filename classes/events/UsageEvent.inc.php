<?php

namespace APP\events;

use APP\core\Application;

use PKP\events\PKPUsageEvent;

class UsageEvent extends PKPUsageEvent
{
    /**
     * Create a new usage event instance.
     *
     * @param  int  $contextId
     * @param  int  $submissionId
     * @param  int  $representationId
     * @param  int  $fileId
     * @param  int  $seriesId
     */
    public function __construct($contextId, $submissionId = null, $representationId = null, $fileId = null, $seriesId = null)
    {
        parent::__construct($contextId, $submissionId, $representationId, $fileId);

        if (isset($seriesId)) {
            $application = Application::get();
            $request = $application->getRequest();

            $assocType = Application::ASSOC_TYPE_SECTION;
            $assocId = $seriesId;
            $canonicalUrl = $this->getCanonicalUrl($request);

            $this->assocType = $assocType;
            $this->assocId = $assocId;
            $this->canonicalUrl = $canonicalUrl;
        } elseif ($this->assocType == ASSOC_TYPE_PRESS) {
            $application = Application::get();
            $request = $application->getRequest();
            $router = $request->getRouter(); /** @var PageRouter $router */
            $page = $router->getRequestedPage($request);
            if ($page == 'catalog') {
                $canonicalUrlPage = 'catalog';
                $canonicalUrlOp = 'index';
                $canonicalUrl = $this->getCanonicalUrl($request, $canonicalUrlPage, $canonicalUrlOp);
                $this->canonicalUrl = $canonicalUrl;
            }
        }
        $file = 'debug.txt';
        $current = file_get_contents($file);
        $current .= print_r("++++ Usage Event ++++\n", true);
        file_put_contents($file, $current);
    }
}
