<?php

declare(strict_types=1);

namespace App\Controller\Api\Magazine;

use App\Controller\Api\BaseApi;
use App\DTO\ImageDto;
use App\Entity\Magazine;
use App\Entity\Report;
use App\Factory\ReportFactory;
use App\Kbin\Magazine\DTO\MagazineDto;
use App\Kbin\Magazine\DTO\MagazineRequestDto;
use App\Kbin\Magazine\DTO\MagazineThemeDto;
use App\Kbin\Magazine\DTO\MagazineThemeRequestDto;
use App\Kbin\Magazine\MagazineCreate;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Service\Attribute\Required;

class MagazineBaseApi extends BaseApi
{
    private readonly ReportFactory $reportFactory;
    protected readonly MagazineCreate $magazineCreate;

    #[Required]
    public function setReportFactory(ReportFactory $reportFactory)
    {
        $this->reportFactory = $reportFactory;
    }

    #[Required]
    public function setMagazineCreate(MagazineCreate $magazineCreate)
    {
        $this->magazineCreate = $magazineCreate;
    }

    protected function serializeReport(Report $report)
    {
        $response = $this->reportFactory->createResponseDto($report);

        return $response;
    }

    /**
     * Deserialize a magazine from JSON.
     *
     * @param ?MagazineDto $dto The MagazineDto to modify with new values (default: null to create a new MagazineDto)
     *
     * @return MagazineDto A magazine with only certain fields allowed to be modified by the user
     */
    protected function deserializeMagazine(MagazineDto $dto = null): MagazineDto
    {
        $dto = $dto ? $dto : new MagazineDto();
        $deserialized = $this->serializer->deserialize(
            $this->request->getCurrentRequest()->getContent(),
            MagazineRequestDto::class,
            'json'
        );
        \assert($deserialized instanceof MagazineRequestDto);

        $dto = $deserialized->mergeIntoDto($dto);

        return $dto;
    }

    protected function deserializeThemeFromForm(MagazineThemeDto $dto): MagazineThemeDto
    {
        $deserialized = new MagazineThemeRequestDto();
        $deserialized->customCss = $this->request->getCurrentRequest()->get('customCss');
        $deserialized->backgroundImage = $this->request->getCurrentRequest()->get('backgroundImage');

        $dto = $deserialized->mergeIntoDto($dto);

        return $dto;
    }

    protected function createMagazine(ImageDto $image = null): Magazine
    {
        $dto = $this->deserializeMagazine();

        if ($image) {
            $dto->icon = $image;
        }

        $errors = $this->validator->validate($dto);
        if (\count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        // Rate limit handled elsewhere
        $magazine = ($this->magazineCreate)($dto, $this->getUserOrThrow(), rateLimit: false);

        return $magazine;
    }
}
