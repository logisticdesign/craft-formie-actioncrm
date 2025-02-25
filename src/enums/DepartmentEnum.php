<?php

namespace logisticdesign\formieactioncrm\enums;

enum DepartmentEnum
{
    case SALES = 'SALES';
    case SERVICE = 'SERV';

    public function leadRequestTypes(): array
    {
        return match ($this) {
            self::SALES => [
                LeadRequestTypeEnum::TDR,
                LeadRequestTypeEnum::PREVS,
                LeadRequestTypeEnum::PREVU,
                LeadRequestTypeEnum::FUPTD,
                LeadRequestTypeEnum::PREVR,
                LeadRequestTypeEnum::TCM,
                LeadRequestTypeEnum::RCONT,
                LeadRequestTypeEnum::SERV,
                LeadRequestTypeEnum::CAMP,
                LeadRequestTypeEnum::SRADD,
                LeadRequestTypeEnum::INFO,
                LeadRequestTypeEnum::BROCH,
                LeadRequestTypeEnum::KMEUP,
            ],
            self::SERVICE => [
                LeadRequestTypeEnum::ASS,
                LeadRequestTypeEnum::PARTS,
                LeadRequestTypeEnum::ASSB1,
                LeadRequestTypeEnum::ASSB2,
                LeadRequestTypeEnum::PREVA,
                LeadRequestTypeEnum::RCONT,
                LeadRequestTypeEnum::SERV,
                LeadRequestTypeEnum::ALERT,
                LeadRequestTypeEnum::CAMP,
                LeadRequestTypeEnum::SRADD,
                LeadRequestTypeEnum::INFO,
                LeadRequestTypeEnum::BROCH,
                LeadRequestTypeEnum::KMEUP,
            ],
        };
    }
}
