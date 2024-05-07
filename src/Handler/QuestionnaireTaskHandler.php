<?php

namespace Gems\Api\Fhir\Handler;

use Gems\Api\Fhir\Model\Transformer\QuestionnaireTaskInfoTransformer;
use Gems\Api\Handlers\ModelRestHandler;
use Gems\Api\Model\ModelApiHelper;
use Gems\Audit\AuditLog;
use Gems\Db\ResultFetcher;
use Gems\Site\SiteUrl;
use Gems\Site\SiteUtil;
use Laminas\Db\Adapter\Adapter;
use Mezzio\Helper\UrlHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zalt\Loader\ProjectOverloader;
use Zalt\Model\Data\DataReaderInterface;

class QuestionnaireTaskHandler extends ModelRestHandler
{
    protected SiteUrl|null $currentSite = null;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        AuditLog $auditLog,
        ProjectOverloader $loader,
        UrlHelper $urlHelper,
        ModelApiHelper $modelApiHelper,
        Adapter $db,
        protected readonly SiteUtil $siteUtil,
        protected readonly ResultFetcher $resultFetcher,
    ) {
        parent::__construct($eventDispatcher, $auditLog, $loader, $urlHelper, $modelApiHelper, $db);
    }

    public function createModel(): DataReaderInterface
    {
        $model = parent::createModel();
        $currentUrl = null;
        if ($this->currentSite) {
            $currentUrl = $this->currentSite->getUrl();
        }
        $model->getMetaModel()->addTransformer(new QuestionnaireTaskInfoTransformer($this->resultFetcher, $currentUrl));

        return $model;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->currentSite = $this->siteUtil->getCurrentSite($request);
        return parent::handle($request);
    }
}