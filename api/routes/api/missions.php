<?php
/*
|--------------------------------------------------------------------------
| Api Mission routes
|--------------------------------------------------------------------------
 */

$router->get(
    '',
    [
        'as' => 'missions',
        'middleware' => ['PaginationMiddleware'],
        'uses' => 'Admin\Mission\MissionController@index'
    ]
);

$router->post(
    '/',
    [
        'as' => 'missions.store',
        'uses' => 'Admin\Mission\MissionController@store'
    ]
);

$router->get(
    '/{missionId}',
    [
        'as' => 'missions.show',
        'uses' => 'Admin\Mission\MissionController@show'
    ]
);

$router->patch(
    '/{missionId}',
    [
        'as' => 'missions.update',
        'uses' => 'Admin\Mission\MissionController@update'
    ]
);

$router->delete(
    '/{missionId}',
    [
        'as' => 'missions.delete',
        'uses' => 'Admin\Mission\MissionController@destroy'
    ]
);

$router->get(
    '/{missionId}/applications',
    [
        'middleware' => ['PaginationMiddleware','TenantHasSettingsMiddleware:volunteering'],
        'uses' => 'Admin\Mission\MissionApplicationController@missionApplications'
    ]
);

$router->get(
    '/{missionId}/applications/{applicationId}',
    [
        'middleware' => ['TenantHasSettingsMiddleware:volunteering'],
        'uses' => 'Admin\Mission\MissionApplicationController@missionApplication'
    ]
);

$router->get(
    '/applications/details',
    [
        'middleware' => ['PaginationMiddleware','TenantHasSettingsMiddleware:volunteering'],
        'uses' => 'Admin\Mission\MissionApplicationController@getMissionApplicationDetails'
    ]
);

$router->patch(
    '/{missionId}/applications/{applicationId}',
    [
        'middleware' => ['TenantHasSettingsMiddleware:volunteering'],
        'uses' => 'Admin\Mission\MissionApplicationController@updateApplication'
    ]
);

$router->get(
    '/{missionId}/comments',
    [
        'middleware' => ['PaginationMiddleware'],
        'as' => 'missions.comments',
        'uses' => 'Admin\Mission\MissionCommentController@index',
    ]
);

$router->get(
    '/{missionId}/comments/{commentId}',
    [
        'middleware' => ['PaginationMiddleware'],
        'as' => 'missions.comments.detail',
        'uses' => 'Admin\Mission\MissionCommentController@show',
    ]
);

$router->patch(
    '/{missionId}/comments/{commentId}',
    [
        'as' => 'missions.comments.update',
        'uses' => 'Admin\Mission\MissionCommentController@update',
    ]
);

$router->delete(
    '/{missionId}/comments/{commentId}',
    [
        'as' => 'missions.comments.delete',
        'uses' => 'Admin\Mission\MissionCommentController@destroy',
    ]
);
