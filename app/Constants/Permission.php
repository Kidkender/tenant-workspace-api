<?php

namespace App\Constants;

class Permission
{
    public const string TASK_VIEW = 'task.view';

    public const string TASK_CREATE = 'task.create';

    public const string TASK_UPDATE = 'task.update';

    public const string TASK_DELETE = 'task.delete';

    public const string TASK_DELETE_ANY = 'task.delete.any';

    public const string COMMENT_CREATE = 'comment.create';

    public const string COMMENT_DELETE = 'comment.delete';

    public const string COMMENT_DELETE_ANY = 'comment.delete.any';

    public const string ATTACHMENT_CREATE = 'attachment.create';

    public const string ATTACHMENT_DELETE = 'attachment.delete';

    public const string ATTACHMENT_DELETE_ANY = 'attachment.delete.any';
}
