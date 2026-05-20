<?php

namespace App\Common\Constants;

class ErrorCode
{
    public const string AUTH_INVALID_CREDENTIALS = 'auth.invalid_credentials';

    public const string AUTH_EMAIL_NOT_VERIFIED = 'auth.email_not_verified';

    public const string AUTH_CODE_EXPIRED = 'auth.code_expired';

    public const string AUTH_EMAIL_TAKEN = 'auth.email_taken';

    public const string AUTH_UNAUTHORIZED = 'auth.unauthorized';

    public const string AUTH_FORBIDDEN = 'auth.forbidden';

    public const string VALIDATION_FAILED = 'validation.failed';

    public const string INTERNAL_SERVER_ERROR = 'server.error';

    public const string TENANT_NOT_FOUND = 'tenant.not_found';

    public const string TENANT_NOT_PROVIDED = 'tenant.not_provided';

    public const string TENANT_NOT_MEMBER = 'tenant.not_member';

    public const string TENANT_ALREADY_MEMBER = 'tenant.already_member';

    public const string RESOURCE_NOT_FOUND = 'resource.not_found';

    public const string PERMISSION_DENIED = 'permission.denied';

    public const string TASK_LIMIT_EXCEEDED = 'task.limit_exceeded';

    public const string MEMBER_LIMIT_EXCEEDED = 'member.limit_exceeded';

    public const string USER_NOT_FOUND = 'user.not_found';

    public const string BAD_REQUEST = 'bad.request';

    public const string FEATURE_NOT_FOUND = 'feature.not_found';

    public const string ATTACHMENT_NOT_FOUND = 'attachment.not_found';

    public const string ATTACHMENT_UPLOAD_FAILED = 'attachment.upload_failed';

    public const string TOO_MANY_REQUESTS = 'too_many_requests';

    public const string TENANT_LIMIT_EXCEEDED = 'tenant.limit_exceeded';

    public const string ROLE_NOT_FOUND = 'role.not_found';

    public const string ROLE_IN_USE = 'role.in_use';

    public const string ROLE_SYSTEM_PROTECTED = 'role.system_protected';

    public const string ROLE_OWNER_PROTECTED = 'role.owner_protected';

    public const string FEATURE_NOT_AVAILABLE = 'feature.not_available';
}
