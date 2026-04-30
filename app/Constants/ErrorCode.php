<?php

namespace App\Constants;

class ErrorCode
{
    public const string AUTH_INVALID_CREDENTIALS = 'auth.invalid_credentials';

    public const string AUTH_EMAIL_NOT_VERIFIED = 'auth.email_not_verified';

    public const string AUTH_EMAIL_TAKEN = 'auth.email_taken';

    public const string AUTH_UNAUTHORIZED = 'auth.unauthorized';

    public const string AUTH_FORBIDDEN = 'auth.forbidden';

    public const string VALIDATION_FAILED = 'validation.failed';

    public const string INTERNAL_SERVER_ERROR = 'server.error';

    public const string TENANT_NOT_FOUND = 'tenant.not_found';

    public const string TENANT_NOT_PROVIDED = 'tenant.not_provided';

    public const string TENANT_NOT_MEMBER = 'tenant.not_member';

    public const string TENANT_ALREADY_MEMBER = 'tenant.already_member';

    public const string PERMISSION_DENIED = 'permission.denied';
}
