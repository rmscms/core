<?php

namespace RMS\Core\Controllers\Admin;

if (class_exists(\App\Http\Controllers\Admin\AdminController::class)) {
    abstract class ProjectAdminController extends \App\Http\Controllers\Admin\AdminController
    {
    }
} else {
    abstract class ProjectAdminController extends \RMS\Core\Controllers\Admin\AdminController
    {
    }
}
