<?php

namespace App\Enums;

enum DeletionRequestStatus: string
{
    case PENDING = 'Pending';
    case APPROVED = 'Approved';
    case REJECTED = 'Rejected';
}
