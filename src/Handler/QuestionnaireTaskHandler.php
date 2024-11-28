<?php

namespace Gems\Api\Fhir\Handler;

use Gems\Api\Fhir\Model\Transformer\QuestionnaireTaskInfoTransformer;
use Gems\Api\Handlers\ModelRestHandler;
use Gems\Api\Model\ModelApiHelper;
use Gems\Audit\AuditLog;
use Gems\Db\ResultFetcher;
use Laminas\Db\Adapter\Adapter;
use Mezzio\Helper\UrlHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zalt\Base\RequestUtil;
use Zalt\Loader\ProjectOverloader;
use Zalt\Model\Data\DataReaderInterface;

class QuestionnaireTaskHandler extends ModelRestHandler
{
    protected string|null $currentBaseUrl = null;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        AuditLog $auditLog,
        ProjectOverloader $loader,
        UrlHelper $urlHelper,
        ModelApiHelper $modelApiHelper,
        Adapter $db,
        protected readonly ResultFetcher $resultFetcher,
    ) {
        parent::__construct($eventDispatcher, $auditLog, $loader, $urlHelper, $modelApiHelper, $db);
    }

    public function createModel(): DataReaderInterface
    {
        $model = parent::createModel();

        $model->getMetaModel()->addTransformer(new QuestionnaireTaskInfoTransformer($this->resultFetcher, $this->currentBaseUrl));

        return $model;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->currentBaseUrl = RequestUtil::getCurrentSite($request);
        return parent::handle($request);
    }
}