<?php

namespace App\Constants;

class TenancyPermissionConstants
{
    public const TENANCY_ROLE_PREFIX = 'tenancy:';
    public const TENANCY_PERMISSION_PREFIX = self::TENANCY_ROLE_PREFIX;
    public const ROLE_ADMIN = 'tenancy:admin';
    public const ROLE_USER = 'tenancy:user';
    public const TENANT_CREATOR_ROLE = self::ROLE_ADMIN;
    public const PERMISSION_CREATE_SUBSCRIPTIONS = 'tenancy: create subscriptions';
    public const PERMISSION_UPDATE_SUBSCRIPTIONS = 'tenancy: update subscriptions';
    public const PERMISSION_DELETE_SUBSCRIPTIONS = 'tenancy: delete subscriptions';
    public const PERMISSION_VIEW_SUBSCRIPTIONS = 'tenancy: view subscriptions';
    public const PERMISSION_CREATE_ORDERS = 'tenancy: create orders';
    public const PERMISSION_UPDATE_ORDERS = 'tenancy: update orders';
    public const PERMISSION_DELETE_ORDERS = 'tenancy: delete orders';
    public const PERMISSION_VIEW_ORDERS = 'tenancy: view orders';
    public const PERMISSION_VIEW_TRANSACTIONS = 'tenancy: view transactions';
    public const PERMISSION_INVITE_MEMBERS = 'tenancy: invite members';
    public const PERMISSION_MANAGE_TEAM = 'tenancy: manage team';
    public const PERMISSION_UPDATE_TENANT_SETTINGS = 'tenancy: update tenant settings';
}
