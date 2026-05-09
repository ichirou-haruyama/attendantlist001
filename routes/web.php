<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/attendance');
Route::view('/attendance', 'attendance-page')->name('attendance.index');
Route::view('/attendance/admin', 'attendance-admin-page')->name('attendance.admin');
Route::view('/attendance/calendar', 'attendance-calendar-page')->name('attendance.calendar');
