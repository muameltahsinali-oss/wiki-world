<?php

declare(strict_types=1);

namespace App\Modules\RolesPermissions\Domain\Enums;

enum Permission: string
{
    case EntityViewPublished = 'entity.view.published';
    case EntityViewDraft = 'entity.view.draft';
    case EntityCreate = 'entity.create';
    case EntityUpdate = 'entity.update';
    case EntitySubmit = 'entity.submit';
    case EntityPublish = 'entity.publish';
    case EntityUnpublish = 'entity.unpublish';
    case ReviewApprove = 'review.approve';
    case MediaUpload = 'media.upload';
    case AiFlagResolve = 'ai.flag.resolve';
    case UserManage = 'user.manage';
    case SettingsManage = 'settings.manage';
    case AnalyticsView = 'analytics.view';
    case AuditView = 'audit.view';
}
