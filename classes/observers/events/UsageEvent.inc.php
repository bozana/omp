<?php

namespace APP\observers\events;

use APP\core\Application;
use PKP\observers\events\PKPUsageEvent;

class UsageEvent extends PKPUsageEvent
{
    /** @var int $chapterId Chapter ID */
    public $chapterId;

    /**
     * Create a new usage event instance.
     */
    public function __construct(int $assocType, int $assocId, int $contextId, int $submissionId = null, int $representationId = null, string $mimetype = null, int $chapterId = null)
    {
        parent::__construct($assocType, $assocId, $contextId, $submissionId, $representationId, $mimetype);

        if (isset($chapterId)) {
            $this->chapterId = $chapterId;
        }

        switch ($assocType) {
            case Application::ASSOC_TYPE_CHAPTER:
                $application = Application::get();
                $request = $application->getRequest();
                $canonicalUrlOp = 'book';
                $canonicalUrlParams = [$submissionId];
                $router = $request->getRouter(); /** @var PageRouter $router */
                $op = $router->getRequestedOp($request);
                $args = $router->getRequestedArgs($request);
                if ($op == 'book' && count($args) > 1) {
                    $submissionId = array_shift($args);
                    $subPath = empty($args) ? 0 : array_shift($args);
                    if ($subPath === 'version') {
                        $publicationId = (int) array_shift($args);
                        ;
                        $canonicalUrlParams[] = 'version';
                        $canonicalUrlParams[] = $publicationId;
                        $subPath = empty($args) ? 0 : array_shift($args);
                    }
                    if ($subPath === 'chapter') {
                        $canonicalUrlParams[] = 'chapter';
                        $canonicalUrlParams[] = $chapterId;
                    }
                }
                $canonicalUrl = $this->getCanonicalUrl($request, null, $canonicalUrlOp, $canonicalUrlParams);
                $this->canonicalUrl = $canonicalUrl;
                $this->assocType = $assocType;
                $this->assocId = $assocId;
                break;
            case Application::ASSOC_TYPE_SERIES:
                $application = Application::get();
                $request = $application->getRequest();
                $router = $request->getRouter(); /** @var PageRouter $router */
                $args = $router->getRequestedArgs($request);
                $canonicalUrlOp = 'series';
                $canonicalUrlParams = [$args[0]];
                $canonicalUrl = $this->getCanonicalUrl($request, null, $canonicalUrlOp, $canonicalUrlParams);
                $this->canonicalUrl = $canonicalUrl;
                $this->assocType = $assocType;
                $this->assocId = $assocId;
                break;
            case Application::ASSOC_TYPE_PRESS:
                $application = Application::get();
                $request = $application->getRequest();
                $router = $request->getRouter(); /** @var PageRouter $router */
                $page = $router->getRequestedPage($request);
                if ($page == 'catalog') {
                    $canonicalUrlPage = 'catalog';
                    $canonicalUrlOp = 'index';
                    $canonicalUrl = $this->getCanonicalUrl($request, $canonicalUrlPage, $canonicalUrlOp, []);
                    $this->canonicalUrl = $canonicalUrl;
                }
                break;
        }
        $file = 'debug.txt';
        $current = file_get_contents($file);
        $current .= print_r("++++ Usage Event ++++\n", true);
        file_put_contents($file, $current);
    }
}
